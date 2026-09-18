<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ExpressDeliveryTest extends TestCase
{
    use DatabaseMigrations;

    public function test_admin_can_save_express_delivery_pricing_in_website_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->assertSame('199.00', WebsiteSetting::current()->express_delivery_charge);

        $this->actingAs($admin)->get(route('admin.settings.edit'))
            ->assertOk()->assertSee('Express Delivery Price (INR)');

        foreach (['275.50', '0'] as $charge) {
            $this->from(route('admin.settings.edit'))
                ->put(route('admin.settings.update'), ['express_delivery_charge' => $charge, 'cod_order_limit' => '100000'])
                ->assertRedirect(route('admin.settings.edit'))
                ->assertSessionHasNoErrors();

            $this->assertDatabaseHas('website_settings', ['id' => 1, 'express_delivery_charge' => $charge]);
        }
    }

    public function test_invalid_delivery_pricing_does_not_overwrite_the_saved_charge(): void
    {
        WebsiteSetting::current()->update(['express_delivery_charge' => '275.50']);
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        foreach ([null, -1, 'invalid', '1.234', '100000000'] as $charge) {
            $this->put(route('admin.settings.update'), ['express_delivery_charge' => $charge, 'cod_order_limit' => '100000'])
                ->assertSessionHasErrors('express_delivery_charge');
        }

        $this->assertSame('275.50', WebsiteSetting::current()->express_delivery_charge);
    }

    public function test_checkout_uses_the_saved_price_and_binds_the_order_summary_to_delivery_selection(): void
    {
        WebsiteSetting::current()->update(['express_delivery_charge' => '275.50']);

        $this->get(route('checkout'))->assertOk()
            ->assertViewHas('expressDeliveryCharge', 275.50)
            ->assertSee('₹275.50')
            ->assertSee('x-text="deliveryDescription"', false)
            ->assertSee('x-text="formatMoney(orderTotal)"', false)
            ->assertSee('x-text="shippingCost > 0 ? formatMoney(shippingCost) : \'Free\'"', false)
            ->assertDontSee('₹199');
    }

    public function test_customer_orders_save_the_current_charge_and_keep_it_after_settings_change(): void
    {
        WebsiteSetting::current()->update(['express_delivery_charge' => '275.50']);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $address = $customer->addresses()->create([...$this->address(), 'is_default' => true]);
        $product = $this->product();

        $this->actingAs($customer)->postJson(route('cart.store'), ['product_id' => $product->id, 'qty' => 2])->assertOk();
        $this->postJson(route('checkout.order.store'), [
            'address_id' => $address->id, 'delivery' => 'express', 'payment' => 'cod', 'shipping_charge' => 0,
        ])->assertOk();

        $order = $customer->orders()->firstOrFail();
        $this->assertSame(275.50, (float) $order->shipping_charge);
        $this->assertSame(2335.50, (float) $order->grand_total);

        WebsiteSetting::current()->update(['express_delivery_charge' => '499.99']);

        $this->get(route('account.orders.show', $order->order_number))->assertOk()
            ->assertViewHas('order', fn ($data) => $data['shipping'] === 275.50 && $data['total'] === 2335.50);
    }

    public function test_guest_orders_use_the_saved_charge_and_allow_standard_and_zero_cost_express_delivery(): void
    {
        $product = $this->product();
        $addressId = $this->postJson(route('checkout.addresses.store'), $this->address())
            ->assertOk()->json('address.id');
        $firstOrderId = null;

        foreach ([['express', 95.75, 95.75], ['standard', 95.75, 0], ['express', 0, 0]] as [$delivery, $setting, $shipping]) {
            WebsiteSetting::current()->update(['express_delivery_charge' => $setting]);
            $this->postJson(route('cart.store'), ['product_id' => $product->id, 'qty' => 2])->assertOk();

            $orderId = $this->postJson(route('checkout.order.store'), [
                'address_id' => $addressId, 'delivery' => $delivery, 'payment' => 'cod', 'shipping_charge' => 0,
            ])->assertOk()->json('orderId');
            $firstOrderId ??= $orderId;

            $this->get(route('order.success', $orderId))->assertOk()
                ->assertViewHas('order', fn ($order) => (float) $order['shipping'] === (float) $shipping && (float) $order['total'] === (float) (2060 + $shipping));
        }

        $this->get(route('order.success', $firstOrderId))->assertOk()
            ->assertViewHas('order', fn ($order) => $order['shipping'] === 95.75 && $order['total'] === 2155.75);
    }

    private function product(): Product
    {
        $category = Category::create(['slug' => 'express-delivery-test', 'name' => 'Express Delivery Test', 'is_active' => true]);

        return Product::create([
            'name' => 'Delivery Test Product', 'slug' => 'delivery-test-product', 'sku' => 'DELIVERY-001',
            'category_id' => $category->id, 'jewellery_type' => 'Ring', 'metal_type' => 'Gold',
            'selling_price' => 1000, 'mrp' => 1200, 'gst_percentage' => 0, 'stock_quantity' => 10, 'is_active' => true,
        ]);
    }

    private function address(): array
    {
        return [
            'type' => 'Home', 'name' => 'Delivery Customer', 'email' => 'delivery@example.com', 'phone' => '9876543210',
            'line1' => 'Delivery Street', 'city' => 'Kolkata', 'district' => 'Kolkata', 'state' => 'West Bengal',
            'pincode' => '700001', 'country' => 'India',
        ];
    }
}

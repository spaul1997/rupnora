<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CashOnDeliveryTest extends TestCase
{
    use DatabaseMigrations;

    public function test_admin_can_save_the_cod_limit_and_disable_cod_with_zero(): void
    {
        $this->assertSame('100000.00', WebsiteSetting::current()->cod_order_limit);
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]))
            ->get(route('admin.settings.edit'))->assertOk()->assertSee('Cash on Delivery Order Limit (INR)');

        foreach (['12500.50', '0'] as $limit) {
            $this->from(route('admin.settings.edit'))->put(route('admin.settings.update'), [
                'express_delivery_charge' => '199', 'cod_order_limit' => $limit,
            ])->assertRedirect(route('admin.settings.edit'))->assertSessionHasNoErrors();

            $this->assertDatabaseHas('website_settings', ['id' => 1, 'cod_order_limit' => $limit, 'express_delivery_charge' => '199']);
        }
    }

    public function test_invalid_limits_do_not_overwrite_the_saved_limit(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        foreach ([null, -1, 'invalid', '1.234', '100000000'] as $limit) {
            $this->put(route('admin.settings.update'), [
                'express_delivery_charge' => '199', 'cod_order_limit' => $limit,
            ])->assertSessionHasErrors('cod_order_limit');
        }

        $this->assertSame('100000.00', WebsiteSetting::current()->cod_order_limit);
    }

    public function test_checkout_displays_the_configured_limit_and_binds_cod_availability(): void
    {
        WebsiteSetting::current()->update(['cod_order_limit' => '2500.50']);

        $this->get(route('checkout'))->assertOk()
            ->assertViewHas('codOrderLimit', 2500.50)
            ->assertSee('Available on orders below ₹2,500.50')
            ->assertSee('x-text="codDescription"', false)
            ->assertSee(':disabled="!codAvailable"', false);
    }

    public function test_customer_cod_requires_a_total_strictly_below_the_limit_and_online_remains_available(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $address = $customer->addresses()->create([...$this->address(), 'is_default' => true]);
        $product = $this->product();
        $this->actingAs($customer)->postJson(route('cart.store'), ['product_id' => $product->id, 'qty' => 2])->assertOk();

        foreach ([2060, 2059.99, 0] as $limit) {
            WebsiteSetting::current()->update(['cod_order_limit' => $limit]);
            $this->postJson(route('checkout.order.store'), [
                'address_id' => $address->id, 'delivery' => 'standard', 'payment' => 'cod', 'cod_order_limit' => 99999999,
            ])->assertUnprocessable()->assertJsonValidationErrors('payment');
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, $product->fresh()->stock_quantity);

        WebsiteSetting::current()->update(['cod_order_limit' => '2060.01']);
        $this->postJson(route('checkout.order.store'), [
            'address_id' => $address->id, 'delivery' => 'standard', 'payment' => 'cod',
        ])->assertOk();
        $order = $customer->orders()->firstOrFail();
        $this->assertSame('Cash on Delivery', $order->payment_method);
        $this->assertSame(2060.0, (float) $order->grand_total);

        WebsiteSetting::current()->update(['cod_order_limit' => 0]);
        $this->postJson(route('cart.store'), ['product_id' => $product->id, 'qty' => 2])->assertOk();
        $this->postJson(route('checkout.order.store'), [
            'address_id' => $address->id, 'delivery' => 'standard', 'payment' => 'online',
        ])->assertOk();
        $this->assertSame('Online Payment', $customer->orders()->latest('id')->firstOrFail()->payment_method);
    }

    public function test_guest_cod_limit_includes_the_configured_express_delivery_charge(): void
    {
        WebsiteSetting::current()->update(['cod_order_limit' => '2099.50', 'express_delivery_charge' => '39.50']);
        $product = $this->product();
        $addressId = $this->postJson(route('checkout.addresses.store'), $this->address())->assertOk()->json('address.id');
        $this->postJson(route('cart.store'), ['product_id' => $product->id, 'qty' => 2])->assertOk();

        $this->postJson(route('checkout.order.store'), [
            'address_id' => $addressId, 'delivery' => 'express', 'payment' => 'cod',
        ])->assertUnprocessable()->assertJsonValidationErrors('payment');
        $this->assertEmpty(session('checkout.orders', []));

        WebsiteSetting::current()->update(['cod_order_limit' => '2099.51']);
        $orderId = $this->postJson(route('checkout.order.store'), [
            'address_id' => $addressId, 'delivery' => 'express', 'payment' => 'cod',
        ])->assertOk()->json('orderId');

        $this->get(route('order.success', $orderId))->assertOk()
            ->assertViewHas('order', fn ($order) => $order['payment_method'] === 'Cash on Delivery'
                && $order['shipping'] === 39.50 && $order['total'] === 2099.50);
    }

    private function product(): Product
    {
        $category = Category::create(['slug' => 'cod-test', 'name' => 'COD Test', 'is_active' => true]);

        return Product::create([
            'name' => 'COD Test Product', 'slug' => 'cod-test-product', 'sku' => 'COD-001',
            'category_id' => $category->id, 'jewellery_type' => 'Ring', 'metal_type' => 'Gold',
            'selling_price' => 1000, 'mrp' => 1200, 'gst_percentage' => 0, 'stock_quantity' => 10, 'is_active' => true,
        ]);
    }

    private function address(): array
    {
        return [
            'type' => 'Home', 'name' => 'COD Customer', 'email' => 'cod@example.com', 'phone' => '9876543210',
            'line1' => 'COD Street', 'city' => 'Kolkata', 'district' => 'Kolkata', 'state' => 'West Bengal',
            'pincode' => '700001', 'country' => 'India',
        ];
    }
}

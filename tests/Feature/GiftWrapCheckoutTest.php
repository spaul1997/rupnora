<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GiftWrapCheckoutTest extends TestCase
{
    use DatabaseMigrations;

    public function test_checkout_displays_the_gift_wrap_editor_and_dynamic_summary(): void
    {
        $this->get(route('checkout'))->assertOk()
            ->assertViewHas('giftWrapCharge', 50.0)
            ->assertViewHas('giftMessageLimit', 248)
            ->assertSee('Add a gift wrap (+₹50)')
            ->assertSee('x-model="giftMessage"', false)
            ->assertSee('x-text="formatMoney(giftWrapCost)"', false)
            ->assertSee('Romantic');
    }

    public function test_gift_wrap_details_and_server_calculated_charge_are_saved_with_the_order(): void
    {
        Mail::fake();
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $address = $customer->addresses()->create([...$this->address(), 'is_default' => true]);
        $product = $this->product();

        $this->actingAs($customer)->postJson(route('cart.store'), [
            'product_id' => $product->id,
            'qty' => 2,
        ])->assertOk();

        $orderNumber = $this->postJson(route('checkout.order.store'), [
            'address_id' => $address->id,
            'delivery' => 'standard',
            'payment' => 'online',
            'gift_wrap' => true,
            'gift_wrap_charge' => 0,
            'gift_message_category' => 'Birthday',
            'gift_message' => '  Have a wonderful birthday!  ',
            'gift_to' => '  Mira  ',
            'gift_from' => '  Arjun  ',
        ])->assertOk()->json('orderId');

        $order = $customer->orders()->where('order_number', $orderNumber)->firstOrFail();

        $this->assertTrue($order->gift_wrap);
        $this->assertSame(50.0, (float) $order->gift_wrap_charge);
        $this->assertSame('Birthday', $order->gift_message_category);
        $this->assertSame('Have a wonderful birthday!', $order->gift_message);
        $this->assertSame('Mira', $order->gift_to);
        $this->assertSame('Arjun', $order->gift_from);
        $this->assertSame(2110.0, (float) $order->grand_total);

        $this->get(route('account.orders.show', $orderNumber))->assertOk()
            ->assertSee('Gift Wrap')
            ->assertSee('Have a wonderful birthday!')
            ->assertViewHas('order', fn (array $data) => $data['gift_wrap'] === [
                'enabled' => true,
                'charge' => 50.0,
                'category' => 'Birthday',
                'message' => 'Have a wonderful birthday!',
                'to' => 'Mira',
                'from' => 'Arjun',
            ]);
    }

    public function test_gift_fields_are_validated_and_do_not_affect_orders_when_gift_wrap_is_off(): void
    {
        Mail::fake();
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $address = $customer->addresses()->create([...$this->address(), 'is_default' => true]);
        $product = $this->product();
        $this->actingAs($customer)->postJson(route('cart.store'), ['product_id' => $product->id, 'qty' => 1])->assertOk();

        $this->postJson(route('checkout.order.store'), [
            'address_id' => $address->id,
            'delivery' => 'standard',
            'payment' => 'online',
            'gift_wrap' => true,
            'gift_message_category' => 'Unknown',
            'gift_message' => str_repeat('a', 249),
        ])->assertUnprocessable()->assertJsonValidationErrors(['gift_message_category', 'gift_message']);

        $this->postJson(route('checkout.order.store'), [
            'address_id' => $address->id,
            'delivery' => 'standard',
            'payment' => 'online',
            'gift_wrap' => false,
            'gift_message_category' => 'Unknown',
            'gift_message' => str_repeat('a', 249),
            'gift_to' => str_repeat('b', 101),
        ])->assertOk();

        $order = $customer->orders()->firstOrFail();
        $this->assertFalse($order->gift_wrap);
        $this->assertSame(0.0, (float) $order->gift_wrap_charge);
        $this->assertNull($order->gift_message);
        $this->assertSame(1030.0, (float) $order->grand_total);
    }

    private function product(): Product
    {
        $category = Category::create([
            'slug' => 'gift-wrap-test',
            'name' => 'Gift Wrap Test',
            'is_active' => true,
        ]);

        return Product::create([
            'name' => 'Gift Wrap Test Product',
            'slug' => 'gift-wrap-test-product',
            'sku' => 'GIFT-001',
            'category_id' => $category->id,
            'jewellery_type' => 'Ring',
            'metal_type' => 'Gold',
            'selling_price' => 1000,
            'mrp' => 1200,
            'gst_percentage' => 0,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
    }

    private function address(): array
    {
        return [
            'type' => 'Home',
            'name' => 'Gift Customer',
            'email' => 'gift@example.com',
            'phone' => '9876543210',
            'line1' => 'Gift Street',
            'city' => 'Kolkata',
            'district' => 'Kolkata',
            'state' => 'West Bengal',
            'pincode' => '700001',
            'country' => 'India',
        ];
    }
}

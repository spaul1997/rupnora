<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutShippingChargeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.shiprocket', [
            'base_url' => 'https://apiv2.shiprocket.in/v1/external',
            'email' => 'api@example.com',
            'password' => 'secret-password',
            'pickup_location' => 'Home',
            'pickup_postcode' => '700056',
            'minimum_weight' => 0.5,
            'timeout' => 20,
            'token_ttl_minutes' => 12960,
        ]);

        Cache::flush();
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Mail::fake();
    }

    public function test_checkout_uses_the_lowest_active_serviceable_courier_rate_below_the_free_shipping_threshold(): void
    {
        WebsiteSetting::current()->update([
            'free_shipping_threshold' => 3000,
            'express_delivery_charge' => 100,
        ]);
        [$customer, $address, $product] = $this->checkoutData(1000, 750);
        $this->actingAs($customer)
            ->postJson(route('cart.store'), ['product_id' => $product->id, 'qty' => 1])
            ->assertOk();
        $this->fakeShiprocketRates();

        $this->postJson(route('checkout.shipping-quote'), [
            'address_id' => $address->id,
            'payment' => 'online',
        ])
            ->assertOk()
            ->assertJsonPath('available', true)
            ->assertJsonPath('shipping_charge', 85)
            ->assertJsonPath('minimum_freight_charge', 85)
            ->assertJsonPath('free_shipping', false)
            ->assertJsonPath('amount_to_free_shipping', 2000)
            ->assertJsonPath('courier_count', 2)
            ->assertJsonPath('courier_name', 'Economy Courier')
            ->assertJsonPath('estimated_date', '24 Oct 2026')
            ->assertJsonPath('weight', 0.75);

        $orderNumber = $this->postJson(route('checkout.order.store'), [
            'address_id' => $address->id,
            'delivery' => 'standard',
            'payment' => 'online',
        ])->assertOk()->json('orderId');

        $order = Order::query()->where('order_number', $orderNumber)->firstOrFail();
        $this->assertSame(85.0, (float) $order->shipping_charge);
        $this->assertSame(1085.0, (float) $order->grand_total);
        $this->assertSame('2026-10-24', $order->estimated_delivery->format('Y-m-d'));

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Expected Delivery')
            ->assertSee('24 Oct 2026');
        Http::assertSentCount(3);
    }

    public function test_free_shipping_threshold_waives_freight_but_keeps_the_express_surcharge(): void
    {
        WebsiteSetting::current()->update([
            'free_shipping_threshold' => 3000,
            'express_delivery_charge' => 100,
        ]);
        [$customer, $address, $product] = $this->checkoutData(2000, 250);
        $this->actingAs($customer)
            ->postJson(route('cart.store'), ['product_id' => $product->id, 'qty' => 2])
            ->assertOk();
        $this->fakeShiprocketRates();

        $this->postJson(route('checkout.shipping-quote'), [
            'address_id' => $address->id,
            'payment' => 'online',
        ])
            ->assertOk()
            ->assertJsonPath('shipping_charge', 0)
            ->assertJsonPath('minimum_freight_charge', 85)
            ->assertJsonPath('free_shipping', true)
            ->assertJsonPath('amount_to_free_shipping', 0);

        $orderNumber = $this->postJson(route('checkout.order.store'), [
            'address_id' => $address->id,
            'delivery' => 'express',
            'payment' => 'online',
        ])->assertOk()->json('orderId');

        $order = Order::query()->where('order_number', $orderNumber)->firstOrFail();
        $this->assertSame(100.0, (float) $order->shipping_charge);
        $this->assertSame(4100.0, (float) $order->grand_total);
        Http::assertSentCount(3);
    }

    public function test_checkout_page_exposes_dynamic_shipping_quote_controls(): void
    {
        WebsiteSetting::current()->update(['free_shipping_threshold' => 4321]);

        $this->get(route('checkout'))
            ->assertOk()
            ->assertViewHas('freeShippingThreshold', 4321.0)
            ->assertSee('shippingQuoteUrl')
            ->assertSee('Checking Shiprocket courier serviceability')
            ->assertSee('x-show="step >= 2"', false)
            ->assertSee('shippingError.length > 0', false)
            ->assertSee('more for free shipping')
            ->assertSee('Expected delivery by')
            ->assertDontSee('Standard Delivery')
            ->assertDontSee('Express Delivery')
            ->assertSee('Insured Shipping');
    }

    private function fakeShiprocketRates(): void
    {
        Http::fake(function (ClientRequest $request) {
            if (str_ends_with($request->url(), '/auth/login')) {
                return Http::response(['token' => 'checkout-token']);
            }

            if (str_contains($request->url(), '/courier/courierListWithCounts')) {
                return Http::response(['courier_data' => [
                    ['id' => 10, 'name' => 'Economy Courier'],
                    ['id' => 20, 'name' => 'Premium Courier'],
                ]]);
            }

            $this->assertStringContainsString('/courier/serviceability/', $request->url());
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            $this->assertSame('700056', $query['pickup_postcode'] ?? null);
            $this->assertSame('110001', $query['delivery_postcode'] ?? null);
            $this->assertSame('0', $query['cod'] ?? null);

            return Http::response(['data' => [
                'recommended_courier_company_id' => 10,
                'available_courier_companies' => [
                    [
                        'courier_company_id' => 10,
                        'courier_name' => 'Economy Courier',
                        'freight_charge' => 85,
                        'estimated_delivery_days' => '5',
                        'etd' => '2026-10-22 18:00:00',
                    ],
                    [
                        'courier_company_id' => 20,
                        'courier_name' => 'Premium Courier',
                        'freight_charge' => 135,
                        'estimated_delivery_days' => '3',
                        'etd' => '2026-10-20 18:00:00',
                    ],
                    [
                        'courier_company_id' => 999,
                        'courier_name' => 'Inactive Courier',
                        'freight_charge' => 500,
                    ],
                ],
            ]]);
        });
    }

    /**
     * @return array{User, CustomerAddress, Product}
     */
    private function checkoutData(float $price, float $grossWeight): array
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $address = $customer->addresses()->create([
            'type' => 'Home',
            'name' => 'Shipping Customer',
            'email' => 'shipping@example.com',
            'phone' => '9876543210',
            'line1' => 'Shipping Street',
            'city' => 'New Delhi',
            'district' => 'New Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'country' => 'India',
            'is_default' => true,
        ]);
        $category = Category::create([
            'slug' => 'shipping-charge-test',
            'name' => 'Shipping Charge Test',
            'is_active' => true,
        ]);
        $product = Product::create([
            'name' => 'Shipping Test Product',
            'slug' => 'shipping-test-product',
            'sku' => 'SHIPPING-001',
            'category_id' => $category->id,
            'jewellery_type' => 'Ring',
            'metal_type' => 'Gold',
            'gross_weight' => $grossWeight,
            'selling_price' => $price,
            'mrp' => $price,
            'gst_percentage' => 0,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        return [$customer, $address, $product];
    }
}

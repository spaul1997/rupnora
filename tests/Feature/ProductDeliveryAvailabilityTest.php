<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductDeliveryAvailabilityTest extends TestCase
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
        Http::preventStrayRequests();
    }

    public function test_product_page_checks_dynamic_shiprocket_delivery_availability(): void
    {
        $product = $this->product();

        Http::fake(function (ClientRequest $request) {
            if (str_ends_with($request->url(), '/auth/login')) {
                return Http::response(['token' => 'delivery-token']);
            }

            $this->assertStringContainsString('/courier/serviceability/', $request->url());
            $this->assertSame(['Bearer delivery-token'], $request->header('Authorization'));
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            $this->assertSame('700056', $query['pickup_postcode'] ?? null);
            $this->assertSame('110001', $query['delivery_postcode'] ?? null);
            $this->assertSame('0.75', $query['weight'] ?? null);
            $this->assertSame('0', $query['cod'] ?? null);

            return Http::response([
                'data' => [
                    'recommended_courier_company_id' => 10,
                    'available_courier_companies' => [
                        [
                            'courier_company_id' => 20,
                            'courier_name' => 'Standard Courier',
                            'freight_charge' => 80,
                            'estimated_delivery_days' => '5',
                            'etd' => '2026-10-22 18:00:00',
                        ],
                        [
                            'courier_company_id' => 10,
                            'courier_name' => 'Recommended Express',
                            'freight_charge' => 120,
                            'estimated_delivery_days' => '3',
                            'etd' => '2026-10-20 18:00:00',
                        ],
                    ],
                ],
            ]);
        });

        $this->getJson(route('product.delivery-availability', [
            'product' => $product,
            'delivery_postcode' => '110001',
            'quantity' => 3,
        ]))
            ->assertOk()
            ->assertJsonPath('available', true)
            ->assertJsonPath('delivery_postcode', '110001')
            ->assertJsonPath('weight', 0.75)
            ->assertJsonPath('courier_count', 2)
            ->assertJsonPath('courier_name', 'Recommended Express')
            ->assertJsonPath('estimated_date', '20 Oct 2026');

        Http::assertSentCount(2);

        $this->get(route('product.show', $product->slug))
            ->assertOk()
            ->assertSee('Delivery Availability')
            ->assertSee('deliveryCheckUrl')
            ->assertSee('delivery_postcode')
            ->assertDontSee('setTimeout(() => { this.pinLoading', false);
    }

    public function test_product_delivery_check_handles_unserviceable_and_invalid_pincodes(): void
    {
        $product = $this->product();

        Http::fake([
            '*/auth/login' => Http::response(['token' => 'delivery-token']),
            '*/courier/serviceability/*' => Http::response([
                'data' => ['available_courier_companies' => []],
            ]),
        ]);

        $this->getJson(route('product.delivery-availability', [
            'product' => $product,
            'delivery_postcode' => '110001',
            'quantity' => 1,
        ]))
            ->assertOk()
            ->assertJsonPath('available', false)
            ->assertJsonPath('courier_count', 0)
            ->assertJsonPath('message', 'Delivery is not currently available for this pincode.');

        $this->getJson(route('product.delivery-availability', [
            'product' => $product,
            'delivery_postcode' => 'invalid',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('delivery_postcode');

        Http::assertSentCount(2);
    }

    private function product(): Product
    {
        $category = Category::create([
            'name' => 'Delivery Test Jewellery',
            'slug' => 'delivery-test-jewellery',
            'is_active' => true,
        ]);

        return Product::create([
            'name' => 'Delivery Test Ring',
            'slug' => 'delivery-test-ring',
            'sku' => 'DELIVERY-001',
            'category_id' => $category->id,
            'jewellery_type' => 'Ring',
            'metal_type' => 'Gold',
            'gross_weight' => 250,
            'mrp' => 2500,
            'selling_price' => 2200,
            'stock_quantity' => 6,
            'is_active' => true,
        ]);
    }
}

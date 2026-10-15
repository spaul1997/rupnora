<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.shiprocket', [
            ...config('services.shiprocket', []),
            'base_url' => 'https://apiv2.shiprocket.in/v1/external',
            'email' => 'shiprocket-test@example.com',
            'password' => 'test-password',
            'pickup_location' => 'Home',
            'pickup_postcode' => '700056',
            'minimum_weight' => 0.5,
        ]);

        Http::fake([
            '*/auth/login' => Http::response(['token' => 'default-test-token']),
            '*/courier/courierListWithCounts*' => Http::response([
                'courier_data' => [['id' => 1, 'name' => 'Test Courier']],
            ]),
            '*/courier/serviceability/*' => Http::response([
                'data' => [
                    'recommended_courier_company_id' => 1,
                    'available_courier_companies' => [[
                        'courier_company_id' => 1,
                        'courier_name' => 'Test Courier',
                        'freight_charge' => 0,
                        'cod_charges' => 0,
                        'estimated_delivery_days' => '7',
                    ]],
                ],
            ]),
        ]);
    }
}

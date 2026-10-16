<?php

namespace Tests\Feature\Admin;

use App\Mail\TrackingGeneratedMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ShiprocketService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ShiprocketOrderTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.shiprocket', [
            'base_url' => 'https://apiv2.shiprocket.in/v1/external',
            'email' => 'api@example.com',
            'password' => 'secret-password',
            'pickup_location' => 'Home',
            'pickup_postcode' => '700001',
            'tracking_url' => 'https://shiprocket.co/tracking/{awb}',
            'length' => 10,
            'breadth' => 15,
            'height' => 20,
            'minimum_weight' => 0.5,
            'timeout' => 20,
            'token_ttl_minutes' => 12960,
        ]);

        Cache::flush();
        Http::swap(new Factory);
        Http::preventStrayRequests();
    }

    public function test_admin_can_send_a_dynamic_order_to_shiprocket(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $order = $this->order();

        Http::fake(function (ClientRequest $request) use ($order) {
            if (str_ends_with($request->url(), '/auth/login')) {
                $this->assertSame('api@example.com', $request['email']);
                $this->assertSame('secret-password', $request['password']);

                return Http::response(['token' => 'test-token']);
            }

            $this->assertStringEndsWith('/orders/create/adhoc', $request->url());
            $this->assertSame(['Bearer test-token'], $request->header('Authorization'));
            $this->assertSame($order->order_number, $request['order_id']);
            $this->assertSame('Home', $request['pickup_location']);
            $this->assertSame('COD', $request['payment_method']);
            $this->assertTrue($request['shipping_is_billing']);
            $this->assertSame('Asha', $request['billing_customer_name']);
            $this->assertSame('Devi', $request['billing_last_name']);
            $this->assertSame('9876543210', $request['billing_phone']);
            $this->assertSame('JEWEL-001', $request['order_items'][0]['sku']);
            $this->assertSame(2, $request['order_items'][0]['units']);
            $this->assertSame(1.5, $request['weight']);

            return Http::response([
                'order_id' => 123456,
                'shipment_id' => 654321,
                'status' => 'NEW',
                'awb_code' => '',
                'courier_name' => '',
            ]);
        });

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertDontSee('Send to Shiprocket');

        $order->update(['status' => 'packed']);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Send to Shiprocket')
            ->assertSee('Create Shiprocket order?')
            ->assertDontSee('Courier Name')
            ->assertDontSee('Tracking Number')
            ->assertDontSee('Tracking URL')
            ->assertDontSee('Estimated Delivery')
            ->assertSeeInOrder(['Update Order Status', 'Shipping', 'Order Timeline']);

        $this->actingAs($admin)
            ->post(route('admin.orders.shiprocket.store', $order))
            ->assertRedirect()
            ->assertSessionHas('success', 'Order sent to Shiprocket successfully.');

        $order->refresh();
        $this->assertSame('123456', $order->shiprocket_order_id);
        $this->assertSame('654321', $order->shiprocket_shipment_id);
        $this->assertSame('NEW', $order->shiprocket_status);
        $this->assertNotNull($order->shiprocket_synced_at);
        Http::assertSentCount(2);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Change Delivery Address')
            ->assertSee('Update delivery address?')
            ->assertSee('Cancel Order')
            ->assertSee('Cancel Shiprocket order?');

        $this->actingAs($admin)
            ->post(route('admin.orders.shiprocket.store', $order))
            ->assertSessionHas('success', 'This order is already available in Shiprocket.');

        Http::assertSentCount(2);
    }

    public function test_common_token_method_caches_the_token(): void
    {
        Http::fake([
            '*/auth/login' => Http::response(['token' => 'cached-token']),
        ]);

        $shiprocket = app(ShiprocketService::class);

        $this->assertSame('cached-token', $shiprocket->token());
        $this->assertSame('cached-token', $shiprocket->token());
        Http::assertSentCount(1);
    }

    public function test_admin_can_update_the_shiprocket_delivery_address(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $order = $this->order();
        $originalBillingAddress = $order->billing_address;
        $order->update([
            'status' => 'packed',
            'shiprocket_order_id' => '16178831',
            'shiprocket_shipment_id' => '654321',
        ]);

        Http::fake(function (ClientRequest $request) {
            if (str_ends_with($request->url(), '/auth/login')) {
                return Http::response(['token' => 'address-token']);
            }

            $this->assertStringEndsWith('/orders/address/update', $request->url());
            $this->assertSame(['Bearer address-token'], $request->header('Authorization'));
            $this->assertSame(16178831, $request['order_id']);
            $this->assertSame('Majin Bu', $request['shipping_customer_name']);
            $this->assertSame('9988998899', $request['shipping_phone']);
            $this->assertSame('Earth', $request['shipping_address']);
            $this->assertSame('Near Capsule Corp', $request['shipping_address_2']);
            $this->assertSame('Pune', $request['shipping_city']);
            $this->assertSame('Maharashtra', $request['shipping_state']);
            $this->assertSame('India', $request['shipping_country']);
            $this->assertSame(110077, $request['shipping_pincode']);

            return Http::response('', 202);
        });

        $this->actingAs($admin)
            ->patch(route('admin.orders.shiprocket.address.update', $order), [
                'shipping_customer_name' => 'Majin Bu',
                'shipping_phone' => '+91 99889 98899',
                'shipping_address' => 'Earth',
                'shipping_address_2' => 'Near Capsule Corp',
                'shipping_city' => 'Pune',
                'shipping_state' => 'Maharashtra',
                'shipping_country' => 'India',
                'shipping_pincode' => '110077',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Delivery address updated in Shiprocket successfully.');

        $order->refresh();
        $this->assertSame('Majin Bu', $order->shipping_address['name']);
        $this->assertSame('9988998899', $order->shipping_address['phone']);
        $this->assertSame('Earth', $order->shipping_address['line1']);
        $this->assertSame('Near Capsule Corp', $order->shipping_address['line2']);
        $this->assertSame('Pune', $order->shipping_address['city']);
        $this->assertSame('Maharashtra', $order->shipping_address['state']);
        $this->assertSame('110077', $order->shipping_address['pincode']);
        $this->assertSame($originalBillingAddress, $order->billing_address);
        Http::assertSentCount(2);
    }

    public function test_admin_can_check_serviceability_generate_awb_and_email_tracking_to_customer(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $order = $this->order();
        $customer = $order->user;
        $order->update([
            'status' => 'packed',
            'shiprocket_order_id' => '16178831',
            'shiprocket_shipment_id' => '16090281',
            'shiprocket_status' => 'NEW',
        ]);

        Http::fake(function (ClientRequest $request) {
            $url = $request->url();

            if (str_ends_with($url, '/auth/login')) {
                return Http::response(['token' => 'awb-token']);
            }

            if (str_contains($url, '/courier/courierListWithCounts')) {
                parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
                $this->assertSame('active', $query['type'] ?? null);
                $this->assertSame(['Bearer awb-token'], $request->header('Authorization'));

                return Http::response([
                    'courier_data' => [
                        ['id' => 10, 'name' => 'Delhivery Surface'],
                        ['id' => 20, 'name' => 'Blue Dart'],
                    ],
                ]);
            }

            if (str_contains($url, '/courier/serviceability/')) {
                parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
                $this->assertSame('700001', $query['pickup_postcode'] ?? null);
                $this->assertSame('700016', $query['delivery_postcode'] ?? null);
                $this->assertSame('16178831', $query['order_id'] ?? null);
                $this->assertSame(['Bearer awb-token'], $request->header('Authorization'));

                return Http::response([
                    'data' => [
                        'recommended_courier_company_id' => 10,
                        'available_courier_companies' => [[
                            'courier_company_id' => 10,
                            'courier_name' => 'Delhivery Surface',
                            'freight_charge' => 112.50,
                            'cod_charges' => 35,
                            'estimated_delivery_days' => '3',
                            'etd' => 'Oct 19, 2026',
                            'delivery_performance' => 4.7,
                        ]],
                    ],
                ]);
            }

            if (str_ends_with($url, '/courier/assign/awb')) {
                $this->assertSame(['Bearer awb-token'], $request->header('Authorization'));
                $this->assertSame(16090281, $request['shipment_id']);
                $this->assertSame(10, $request['courier_id']);

                return Http::response([
                    'awb_assign_status' => 1,
                    'response' => [
                        'data' => [
                            'courier_company_id' => 10,
                            'awb_code' => 'AWB123456789',
                            'courier_name' => 'Delhivery Surface',
                            'shipment_id' => 16090281,
                        ],
                    ],
                ]);
            }

            if (str_ends_with($url, '/courier/track/awb/AWB123456789')) {
                $this->assertSame('GET', $request->method());
                $this->assertSame(['Bearer awb-token'], $request->header('Authorization'));

                return Http::response([
                    'tracking_data' => [
                        'track_status' => 1,
                        'shipment_status' => 18,
                        'shipment_track' => [[
                            'awb_code' => 'AWB123456789',
                            'current_status' => 'In Transit',
                            'courier_name' => 'Delhivery Surface',
                            'edd' => '2026-10-19 18:00:00',
                        ]],
                        'shipment_track_activities' => [
                            [
                                'date' => '2026-10-17 10:30:00',
                                'status' => 'IT',
                                'activity' => 'Shipment reached Kolkata hub',
                                'location' => 'Kolkata, West Bengal',
                                'sr-status-label' => 'IN TRANSIT',
                            ],
                            [
                                'date' => '2026-10-16 15:10:00',
                                'status' => 'PICKED',
                                'activity' => 'Shipment picked up',
                                'location' => 'Kolkata, West Bengal',
                                'sr-status-label' => 'PICKED UP',
                            ],
                        ],
                        'track_url' => 'https://shiprocket.co/tracking/AWB123456789',
                    ],
                ]);
            }

            return Http::response(['message' => 'Unexpected Shiprocket request'], 500);
        });

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertDontSee('Choose Courier &amp; Generate AWB', false);

        $this->post(route('admin.orders.shiprocket.couriers', $order))
            ->assertRedirect()
            ->assertSessionHasErrors('shiprocket_awb');

        $this->post(route('admin.orders.shiprocket.awb', $order), ['courier_id' => 10])
            ->assertRedirect()
            ->assertSessionHasErrors('shiprocket_awb');

        $order->update(['status' => 'shipped']);

        $this->post(route('admin.orders.shiprocket.couriers', $order))
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('shiprocket_couriers', fn (array $couriers): bool => count($couriers) === 1
                && $couriers[0]['id'] === 10
                && $couriers[0]['recommended'] === true
            );

        $this->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Delhivery Surface')
            ->assertSee('Generate AWB &amp; Send Tracking Email', false)
            ->assertSee('Generate AWB?');

        $this->post(route('admin.orders.shiprocket.awb', $order), ['courier_id' => 10])
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'AWB generated and the tracking email was queued successfully.');

        $order->refresh();
        $this->assertSame('AWB ASSIGNED', $order->shiprocket_status);
        $this->assertSame('Delhivery Surface', $order->courier_name);
        $this->assertSame('AWB123456789', $order->tracking_number);
        $this->assertSame('https://shiprocket.co/tracking/AWB123456789', $order->tracking_url);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => 'awb_assigned',
            'updated_by' => $admin->id,
        ]);
        Mail::assertQueued(TrackingGeneratedMail::class, fn (TrackingGeneratedMail $mail): bool => $mail->hasTo('asha@example.com')
            && $mail->order->tracking_number === 'AWB123456789'
        );
        $mailHtml = (new TrackingGeneratedMail($order))->render();
        $this->assertStringContainsString('AWB123456789', $mailHtml);
        $this->assertStringContainsString('https://shiprocket.co/tracking/AWB123456789', $mailHtml);

        $this->actingAs($admin)
            ->post(route('admin.orders.shiprocket.tracking.refresh', $order))
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Tracking refreshed successfully: IN TRANSIT.');

        $order->refresh();
        $this->assertSame('IN TRANSIT', $order->shiprocket_status);
        $this->assertSame('IN TRANSIT', $order->shiprocket_tracking['status']);
        $this->assertSame(18, $order->shiprocket_tracking['status_id']);
        $this->assertSame('Shipment reached Kolkata hub', $order->shiprocket_tracking['activities'][0]['activity']);
        $this->assertSame('2026-10-19', $order->estimated_delivery->format('Y-m-d'));
        $this->assertNotNull($order->shiprocket_tracked_at);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => 'shiprocket_tracking_updated',
            'remark' => 'Shiprocket shipment status updated to IN TRANSIT.',
            'updated_by' => $admin->id,
        ]);

        $this->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Tracking ID / AWB')
            ->assertSee('AWB123456789')
            ->assertSee('View Tracking')
            ->assertSee('Open Courier Tracking Page')
            ->assertDontSee('Choose Courier &amp; Generate AWB', false)
            ->assertSee('Shipment reached Kolkata hub')
            ->assertSee('View live tracking?');

        $this->actingAs($customer)
            ->get(route('account.orders.show', $order->order_number))
            ->assertOk()
            ->assertSee('Refresh Tracking')
            ->assertSee('Open Tracking Page')
            ->assertSee('Tracking ID / AWB')
            ->assertSee('AWB123456789')
            ->assertSee('Shipment reached Kolkata hub')
            ->assertSee('https://shiprocket.co/tracking/AWB123456789', false);

        $this->post(route('account.orders.tracking.refresh', $order->order_number))
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Tracking refreshed successfully: IN TRANSIT.');

        $this->assertSame(1, $order->statusHistories()->where('status', 'shiprocket_tracking_updated')->count());

        $otherCustomer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $this->actingAs($otherCustomer)
            ->post(route('account.orders.tracking.refresh', $order->order_number))
            ->assertNotFound();

        $this->actingAs($admin)
            ->post(route('admin.orders.shiprocket.awb', $order), ['courier_id' => 10])
            ->assertSessionHas('success', 'Tracking has already been generated for this order.');

        Mail::assertQueued(TrackingGeneratedMail::class, 1);
        Http::assertSentCount(6);
    }

    public function test_rejected_shiprocket_address_does_not_change_the_local_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $order = $this->order();
        $originalAddress = $order->shipping_address;
        $order->update([
            'status' => 'packed',
            'shiprocket_order_id' => '16178831',
            'shiprocket_shipment_id' => '654321',
        ]);

        Http::fake([
            '*/auth/login' => Http::response(['token' => 'address-token']),
            '*/orders/address/update' => Http::response(['message' => 'Address cannot be updated'], 422),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.shiprocket.address.update', $order), [
                'shipping_customer_name' => 'Majin Bu',
                'shipping_phone' => '9988998899',
                'shipping_address' => 'Earth',
                'shipping_address_2' => '',
                'shipping_city' => 'Pune',
                'shipping_state' => 'Maharashtra',
                'shipping_country' => 'India',
                'shipping_pincode' => '110077',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('shiprocket_address');

        $this->assertSame($originalAddress, $order->fresh()->shipping_address);
    }

    public function test_admin_can_cancel_the_shiprocket_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $order = $this->order();
        $product = $order->items()->firstOrFail()->product()->firstOrFail();
        $product->update(['stock_quantity' => 8]);
        $order->update([
            'status' => 'packed',
            'stock_reserved' => true,
            'shiprocket_order_id' => '16168898',
            'shiprocket_shipment_id' => '654321',
            'shiprocket_status' => 'NEW',
        ]);

        Http::fake(function (ClientRequest $request) {
            if (str_ends_with($request->url(), '/auth/login')) {
                return Http::response(['token' => 'cancel-token']);
            }

            $this->assertStringEndsWith('/orders/cancel', $request->url());
            $this->assertSame(['Bearer cancel-token'], $request->header('Authorization'));
            $this->assertSame([16168898], $request['ids']);

            return Http::response('', 204);
        });

        $this->actingAs($admin)
            ->post(route('admin.orders.shiprocket.cancel', $order))
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Order cancelled in Shiprocket successfully.');

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('CANCELLED', $order->shiprocket_status);
        $this->assertFalse($order->stock_reserved);
        $this->assertSame(10, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => 'cancelled',
            'remark' => 'Order cancelled in Shiprocket by admin.',
            'updated_by' => $admin->id,
        ]);
        Http::assertSentCount(2);
    }

    public function test_rejected_shiprocket_cancellation_does_not_cancel_the_local_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $order = $this->order();
        $order->update([
            'status' => 'packed',
            'stock_reserved' => true,
            'shiprocket_order_id' => '16168898',
            'shiprocket_shipment_id' => '654321',
        ]);

        Http::fake([
            '*/auth/login' => Http::response(['token' => 'cancel-token']),
            '*/orders/cancel' => Http::response(['message' => 'Order cannot be cancelled'], 422),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.shiprocket.cancel', $order))
            ->assertRedirect()
            ->assertSessionHasErrors('shiprocket_cancel');

        $order->refresh();
        $this->assertSame('packed', $order->status);
        $this->assertTrue($order->stock_reserved);
        $this->assertDatabaseMissing('order_status_histories', [
            'order_id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    private function order(): Order
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
            'name' => 'Asha Devi',
            'email' => 'asha@example.com',
            'phone' => '+91 98765 43210',
        ]);
        $category = Category::create([
            'name' => 'Shiprocket Test',
            'slug' => 'shiprocket-test',
            'is_active' => true,
        ]);
        $product = Product::create([
            'name' => 'Gold Jewellery',
            'slug' => 'gold-jewellery',
            'sku' => 'JEWEL-001',
            'category_id' => $category->id,
            'jewellery_type' => 'Ring',
            'metal_type' => 'Gold',
            'gross_weight' => 750,
            'selling_price' => 900,
            'mrp' => 1000,
            'gst_percentage' => 3,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
        $address = [
            'name' => 'Asha Devi',
            'email' => 'asha@example.com',
            'phone' => '+91 98765 43210',
            'line1' => '22 Park Street',
            'line2' => 'Second Floor',
            'landmark' => 'Near Metro',
            'city' => 'Kolkata',
            'state' => 'West Bengal',
            'pincode' => '700016',
            'country' => 'India',
        ];
        $order = $customer->orders()->create([
            'order_number' => 'RN-2026-1000',
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'status' => 'confirmed',
            'payment_status' => 'cod',
            'payment_method' => 'Cash on Delivery',
            'subtotal' => 2000,
            'discount_amount' => 200,
            'coupon_discount' => 100,
            'shipping_charge' => 50,
            'gst_amount' => 52.43,
            'grand_total' => 1750,
            'shipping_address' => $address,
            'billing_address' => $address,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 2,
            'price' => 900,
            'total' => 1800,
        ]);

        return $order;
    }
}

<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ShiprocketService
{
    private const TOKEN_CACHE_PREFIX = 'shiprocket.auth_token.';

    public function token(bool $refresh = false): string
    {
        $email = trim((string) config('services.shiprocket.email'));
        $password = (string) config('services.shiprocket.password');

        if ($email === '' || $password === '') {
            throw new RuntimeException('Shiprocket API credentials are not configured.');
        }

        $cacheKey = self::TOKEN_CACHE_PREFIX.sha1($email);

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember(
            $cacheKey,
            now()->addMinutes((int) config('services.shiprocket.token_ttl_minutes', 12960)),
            function () use ($email, $password): string {
                $response = $this->client()->post('auth/login', [
                    'email' => $email,
                    'password' => $password,
                ]);

                if (! $response->successful()) {
                    throw new RuntimeException('Shiprocket authentication failed: '.$this->errorMessage($response));
                }

                $token = $response->json('token');

                if (! is_string($token) || trim($token) === '') {
                    throw new RuntimeException('Shiprocket authentication did not return a token.');
                }

                return $token;
            },
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function createOrder(Order $order): array
    {
        $order->loadMissing('items.product');
        $payload = $this->orderPayload($order);
        $response = $this->sendCreateOrder($payload, $this->token());

        // A cached token can be revoked before its normal expiry. Refresh it once.
        if ($response->status() === 401) {
            $response = $this->sendCreateOrder($payload, $this->token(refresh: true));
        }

        if (! $response->successful()) {
            throw new RuntimeException('Shiprocket order creation failed: '.$this->errorMessage($response));
        }

        $data = $response->json();

        if (! is_array($data) || empty($data['order_id']) || empty($data['shipment_id'])) {
            throw new RuntimeException('Shiprocket order creation returned an incomplete response.');
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $address
     */
    public function updateDeliveryAddress(Order $order, array $address): void
    {
        if (! $order->shiprocket_order_id) {
            throw new RuntimeException('Create the Shiprocket order before changing its delivery address.');
        }

        $payload = [
            'order_id' => (int) $order->shiprocket_order_id,
            'shipping_customer_name' => $address['shipping_customer_name'],
            'shipping_phone' => $address['shipping_phone'],
            'shipping_address' => $address['shipping_address'],
            'shipping_address_2' => $address['shipping_address_2'] ?? '',
            'shipping_city' => $address['shipping_city'],
            'shipping_state' => $address['shipping_state'],
            'shipping_country' => $address['shipping_country'],
            'shipping_pincode' => (int) $address['shipping_pincode'],
        ];
        $response = $this->sendDeliveryAddressUpdate($payload, $this->token());

        if ($response->status() === 401) {
            $response = $this->sendDeliveryAddressUpdate($payload, $this->token(refresh: true));
        }

        if (! $response->successful()) {
            throw new RuntimeException('Shiprocket delivery address update failed: '.$this->errorMessage($response));
        }
    }

    public function cancelOrder(Order $order): void
    {
        if (! $order->shiprocket_order_id) {
            throw new RuntimeException('Create the Shiprocket order before cancelling it.');
        }

        $payload = ['ids' => [(int) $order->shiprocket_order_id]];
        $response = $this->sendCancellation($payload, $this->token());

        if ($response->status() === 401) {
            $response = $this->sendCancellation($payload, $this->token(refresh: true));
        }

        if (! $response->successful()) {
            throw new RuntimeException('Shiprocket order cancellation failed: '.$this->errorMessage($response));
        }
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function listCouriers(): array
    {
        $response = $this->sendCourierList($this->token());

        if ($response->status() === 401) {
            $response = $this->sendCourierList($this->token(refresh: true));
        }

        if (! $response->successful()) {
            throw new RuntimeException('Shiprocket courier list failed: '.$this->errorMessage($response));
        }

        $couriers = $response->json('courier_data');

        if (! is_array($couriers)) {
            throw new RuntimeException('Shiprocket courier list returned an incomplete response.');
        }

        return collect($couriers)
            ->map(function ($courier): array {
                $courier = is_array($courier) ? $courier : [];

                return [
                    'id' => (int) ($courier['id'] ?? $courier['courier_company_id'] ?? 0),
                    'name' => trim((string) ($courier['name'] ?? $courier['courier_name'] ?? '')),
                ];
            })
            ->filter(fn (array $courier): bool => $courier['id'] > 0 && $courier['name'] !== '')
            ->unique('id')
            ->values()
            ->all();
    }

    /**
     * Return active couriers that can serve a package before an order is created.
     *
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     freight_charge: float,
     *     cod_charges: float,
     *     estimated_delivery_days: string,
     *     etd: string,
     *     recommended: bool
     * }>
     */
    public function packageServiceableCouriers(
        string $deliveryPostcode,
        float $weight,
        bool $cashOnDelivery = false,
    ): array {
        $pickupPostcode = trim((string) config('services.shiprocket.pickup_postcode'));
        $deliveryPostcode = trim($deliveryPostcode);

        if (! preg_match('/^[1-9][0-9]{5}$/', $pickupPostcode)) {
            throw new RuntimeException('Set a valid SHIPROCKET_PICKUP_POSTCODE before checking courier serviceability.');
        }

        if (! preg_match('/^[1-9][0-9]{5}$/', $deliveryPostcode)) {
            throw new RuntimeException('The delivery address does not contain a valid pincode.');
        }

        $directory = collect($this->listCouriers())->keyBy('id');
        $query = [
            'pickup_postcode' => $pickupPostcode,
            'delivery_postcode' => $deliveryPostcode,
            'weight' => round(max((float) config('services.shiprocket.minimum_weight', 0.5), $weight), 3),
            'cod' => $cashOnDelivery ? 1 : 0,
        ];
        $response = $this->sendServiceabilityCheck($query, $this->token());

        if ($response->status() === 401) {
            $response = $this->sendServiceabilityCheck($query, $this->token(refresh: true));
        }

        if (! $response->successful()) {
            throw new RuntimeException('Shiprocket serviceability check failed: '.$this->errorMessage($response));
        }

        $couriers = $response->json('data.available_courier_companies');

        if (! is_array($couriers)) {
            throw new RuntimeException('Shiprocket serviceability check returned an incomplete response.');
        }

        $recommendedId = (int) $response->json('data.recommended_courier_company_id', 0);

        return collect($couriers)
            ->filter(fn ($courier): bool => is_array($courier))
            ->map(function (array $courier) use ($directory, $recommendedId): array {
                $id = (int) ($courier['courier_company_id'] ?? $courier['id'] ?? 0);
                $listedCourier = $directory->get($id);

                return [
                    'id' => $id,
                    'name' => trim((string) ($courier['courier_name'] ?? data_get($listedCourier, 'name', ''))),
                    'freight_charge' => round((float) ($courier['freight_charge'] ?? $courier['rate'] ?? 0), 2),
                    'cod_charges' => round((float) ($courier['cod_charges'] ?? 0), 2),
                    'estimated_delivery_days' => trim((string) ($courier['estimated_delivery_days'] ?? '')),
                    'etd' => trim((string) ($courier['etd'] ?? '')),
                    'recommended' => $id === $recommendedId,
                    'listed' => $listedCourier !== null,
                ];
            })
            ->filter(fn (array $courier): bool => $courier['listed'] && $courier['id'] > 0 && $courier['name'] !== '')
            ->map(fn (array $courier): array => collect($courier)->except('listed')->all())
            ->unique('id')
            ->values()
            ->all();
    }

    /**
     * Return only couriers Shiprocket reports as serviceable for this order.
     *
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     freight_charge: float,
     *     cod_charges: float,
     *     estimated_delivery_days: string,
     *     etd: string,
     *     rating: float|null,
     *     recommended: bool
     * }>
     */
    public function serviceableCouriers(Order $order): array
    {
        if (! $order->shiprocket_order_id) {
            throw new RuntimeException('Create the Shiprocket order before checking courier serviceability.');
        }

        $pickupPostcode = trim((string) config('services.shiprocket.pickup_postcode'));
        $deliveryPostcode = trim((string) data_get(
            $order->shipping_address ?: $order->billing_address ?: [],
            'pincode',
        ));

        if (! preg_match('/^[1-9][0-9]{5}$/', $pickupPostcode)) {
            throw new RuntimeException('Set a valid SHIPROCKET_PICKUP_POSTCODE before checking courier serviceability.');
        }

        if (! preg_match('/^[1-9][0-9]{5}$/', $deliveryPostcode)) {
            throw new RuntimeException('The order delivery address does not contain a valid pincode.');
        }

        $directory = collect($this->listCouriers())->keyBy('id');
        $query = [
            'pickup_postcode' => $pickupPostcode,
            'delivery_postcode' => $deliveryPostcode,
            'order_id' => (int) $order->shiprocket_order_id,
        ];
        $response = $this->sendServiceabilityCheck($query, $this->token());

        if ($response->status() === 401) {
            $response = $this->sendServiceabilityCheck($query, $this->token(refresh: true));
        }

        if (! $response->successful()) {
            throw new RuntimeException('Shiprocket serviceability check failed: '.$this->errorMessage($response));
        }

        $couriers = $response->json('data.available_courier_companies');

        if (! is_array($couriers)) {
            throw new RuntimeException('Shiprocket serviceability check returned an incomplete response.');
        }

        $recommendedId = (int) $response->json('data.recommended_courier_company_id', 0);

        return collect($couriers)
            ->map(function ($courier) use ($directory, $recommendedId): array {
                $courier = is_array($courier) ? $courier : [];
                $id = (int) ($courier['courier_company_id'] ?? $courier['id'] ?? 0);
                $listedCourier = $directory->get($id, []);

                return [
                    'id' => $id,
                    'name' => trim((string) ($courier['courier_name'] ?? $listedCourier['name'] ?? '')),
                    'freight_charge' => round((float) ($courier['freight_charge'] ?? $courier['rate'] ?? 0), 2),
                    'cod_charges' => round((float) ($courier['cod_charges'] ?? 0), 2),
                    'estimated_delivery_days' => trim((string) ($courier['estimated_delivery_days'] ?? '')),
                    'etd' => trim((string) ($courier['etd'] ?? '')),
                    'rating' => isset($courier['rating'])
                        ? (float) $courier['rating']
                        : (isset($courier['delivery_performance']) ? (float) $courier['delivery_performance'] : null),
                    'recommended' => $id === $recommendedId,
                ];
            })
            ->filter(fn (array $courier): bool => $courier['id'] > 0 && $courier['name'] !== '')
            ->unique('id')
            ->sortBy(fn (array $courier): array => [
                $courier['recommended'] ? 0 : 1,
                $courier['freight_charge'] > 0 ? $courier['freight_charge'] : PHP_FLOAT_MAX,
                $courier['name'],
            ])
            ->values()
            ->all();
    }

    /**
     * Check prepaid delivery availability for a product and selected quantity.
     *
     * @return array{
     *     available: bool,
     *     message: string,
     *     delivery_postcode: string,
     *     weight: float,
     *     courier_count: int,
     *     courier_name: string|null,
     *     estimated_delivery_days: string|null,
     *     estimated_date: string|null,
     *     freight_charge: float|null
     * }
     */
    public function checkProductServiceability(Product $product, string $deliveryPostcode, int $quantity = 1): array
    {
        $pickupPostcode = trim((string) config('services.shiprocket.pickup_postcode'));
        $deliveryPostcode = trim($deliveryPostcode);

        if (! preg_match('/^[1-9][0-9]{5}$/', $pickupPostcode)) {
            throw new RuntimeException('Set a valid SHIPROCKET_PICKUP_POSTCODE before checking delivery availability.');
        }

        if (! preg_match('/^[1-9][0-9]{5}$/', $deliveryPostcode)) {
            throw new RuntimeException('Enter a valid 6-digit delivery pincode.');
        }

        $quantity = max(1, min(5, $quantity));
        $weight = round(max(
            (float) config('services.shiprocket.minimum_weight', 0.5),
            ((float) $product->gross_weight * $quantity) / 1000,
        ), 3);
        $query = [
            'pickup_postcode' => $pickupPostcode,
            'delivery_postcode' => $deliveryPostcode,
            'weight' => $weight,
            'cod' => 0,
        ];
        $response = $this->sendServiceabilityCheck($query, $this->token());

        if ($response->status() === 401) {
            $response = $this->sendServiceabilityCheck($query, $this->token(refresh: true));
        }

        if (! $response->successful()) {
            throw new RuntimeException('Shiprocket delivery availability check failed: '.$this->errorMessage($response));
        }

        $couriers = $response->json('data.available_courier_companies', []);

        if (! is_array($couriers)) {
            throw new RuntimeException('Shiprocket delivery availability returned an incomplete response.');
        }

        $recommendedId = (int) $response->json('data.recommended_courier_company_id', 0);
        $availableCouriers = collect($couriers)
            ->filter(fn ($courier): bool => is_array($courier))
            ->map(fn (array $courier): array => [
                'id' => (int) ($courier['courier_company_id'] ?? $courier['id'] ?? 0),
                'name' => trim((string) ($courier['courier_name'] ?? $courier['name'] ?? '')),
                'freight_charge' => round((float) ($courier['freight_charge'] ?? $courier['rate'] ?? 0), 2),
                'estimated_delivery_days' => trim((string) ($courier['estimated_delivery_days'] ?? '')),
                'etd' => trim((string) ($courier['etd'] ?? '')),
            ])
            ->filter(fn (array $courier): bool => $courier['id'] > 0 && $courier['name'] !== '')
            ->sortBy(fn (array $courier): array => [
                $courier['id'] === $recommendedId ? 0 : 1,
                (int) $courier['estimated_delivery_days'] ?: PHP_INT_MAX,
                $courier['freight_charge'] > 0 ? $courier['freight_charge'] : PHP_FLOAT_MAX,
            ])
            ->values();
        $courier = $availableCouriers->first();

        if (! $courier) {
            return [
                'available' => false,
                'message' => 'Delivery is not currently available for this pincode.',
                'delivery_postcode' => $deliveryPostcode,
                'weight' => $weight,
                'courier_count' => 0,
                'courier_name' => null,
                'estimated_delivery_days' => null,
                'estimated_date' => null,
                'freight_charge' => null,
            ];
        }

        $estimatedDate = null;

        try {
            $estimatedDate = $courier['etd'] !== ''
                ? Carbon::parse($courier['etd'])->format('d M Y')
                : null;
        } catch (\Throwable) {
            $estimatedDate = null;
        }

        if (! $estimatedDate && (int) $courier['estimated_delivery_days'] > 0) {
            $estimatedDate = now()->addDays((int) $courier['estimated_delivery_days'])->format('d M Y');
        }

        return [
            'available' => true,
            'message' => 'Delivery is available for this pincode.',
            'delivery_postcode' => $deliveryPostcode,
            'weight' => $weight,
            'courier_count' => $availableCouriers->count(),
            'courier_name' => $courier['name'],
            'estimated_delivery_days' => $courier['estimated_delivery_days'] ?: null,
            'estimated_date' => $estimatedDate,
            'freight_charge' => $courier['freight_charge'],
        ];
    }

    /**
     * @return array{awb_code: string, courier_name: string, courier_company_id: int, shipment_id: string}
     */
    public function generateAwb(Order $order, int $courierId): array
    {
        if (! $order->shiprocket_shipment_id) {
            throw new RuntimeException('Create the Shiprocket shipment before generating an AWB.');
        }

        $payload = [
            'shipment_id' => (int) $order->shiprocket_shipment_id,
            'courier_id' => $courierId,
        ];
        $response = $this->sendAwbAssignment($payload, $this->token());

        if ($response->status() === 401) {
            $response = $this->sendAwbAssignment($payload, $this->token(refresh: true));
        }

        if (! $response->successful()) {
            throw new RuntimeException('Shiprocket AWB generation failed: '.$this->errorMessage($response));
        }

        $data = $response->json('response.data');
        $awbCode = is_array($data) ? trim((string) ($data['awb_code'] ?? '')) : '';

        if ((int) $response->json('awb_assign_status', 0) !== 1 || $awbCode === '') {
            $message = $response->json('response.message')
                ?? (is_string($data) ? $data : null)
                ?? $response->json('message')
                ?? 'Shiprocket did not assign an AWB.';

            throw new RuntimeException(str((string) $message)->squish()->limit(300)->toString());
        }

        return [
            'awb_code' => $awbCode,
            'courier_name' => trim((string) ($data['courier_name'] ?? 'Shiprocket Courier')),
            'courier_company_id' => (int) ($data['courier_company_id'] ?? $courierId),
            'shipment_id' => (string) ($data['shipment_id'] ?? $order->shiprocket_shipment_id),
        ];
    }

    public function trackingUrl(string $awbCode): string
    {
        $template = trim((string) config('services.shiprocket.tracking_url'));
        $url = str_contains($template, '{awb}')
            ? str_replace('{awb}', rawurlencode($awbCode), $template)
            : rtrim($template, '/').'/'.rawurlencode($awbCode);
        $scheme = parse_url($url, PHP_URL_SCHEME);

        return filter_var($url, FILTER_VALIDATE_URL) && in_array($scheme, ['http', 'https'], true)
            ? $url
            : 'https://www.shiprocket.in/shipment-tracking/';
    }

    /**
     * @return array{
     *     status: string,
     *     status_id: int|null,
     *     courier_name: string|null,
     *     estimated_delivery: string|null,
     *     tracking_url: string,
     *     activities: array<int, array{date: string, status: string, activity: string, location: string}>
     * }
     */
    public function trackAwb(Order $order): array
    {
        $awbCode = trim((string) $order->tracking_number);

        if ($awbCode === '') {
            throw new RuntimeException('Generate an AWB before checking Shiprocket tracking.');
        }

        $response = $this->sendAwbTracking($awbCode, $this->token());

        if ($response->status() === 401) {
            $response = $this->sendAwbTracking($awbCode, $this->token(refresh: true));
        }

        if (! $response->successful()) {
            throw new RuntimeException('Shiprocket tracking failed: '.$this->errorMessage($response));
        }

        $body = $response->json();

        if (! is_array($body)) {
            throw new RuntimeException('Shiprocket tracking returned an incomplete response.');
        }

        if (array_key_exists('tracking_data', $body) && ! is_array($body['tracking_data'])) {
            $message = $body['message'] ?? 'Tracking details are not available for this AWB yet.';

            throw new RuntimeException(str((string) $message)->squish()->limit(300)->toString());
        }

        $tracking = is_array($body['tracking_data'] ?? null) ? $body['tracking_data'] : $body;
        $shipmentTrack = data_get($tracking, 'shipment_track.0', []);
        $shipmentTrack = is_array($shipmentTrack) ? $shipmentTrack : [];
        $rawActivities = $tracking['shipment_track_activities'] ?? $tracking['scans'] ?? $body['scans'] ?? [];
        $rawActivities = is_array($rawActivities) ? $rawActivities : [];

        if (isset($tracking['track_status']) && (int) $tracking['track_status'] === 0 && $shipmentTrack === [] && $rawActivities === []) {
            $message = $tracking['error'] ?? $tracking['message'] ?? $body['message'] ?? 'Tracking details are not available for this AWB yet.';

            throw new RuntimeException(str((string) $message)->squish()->limit(300)->toString());
        }

        $activities = collect($rawActivities)
            ->filter(fn ($activity): bool => is_array($activity))
            ->map(fn (array $activity): array => [
                'date' => trim((string) ($activity['date'] ?? $activity['timestamp'] ?? '')),
                'status' => trim((string) ($activity['sr-status-label'] ?? $activity['status'] ?? '')),
                'activity' => trim((string) ($activity['activity'] ?? $activity['message'] ?? '')),
                'location' => trim((string) ($activity['location'] ?? '')),
            ])
            ->filter(fn (array $activity): bool => collect($activity)->contains(fn (string $value): bool => $value !== ''))
            ->sortByDesc(fn (array $activity): int => strtotime($activity['date']) ?: 0)
            ->take(25)
            ->values()
            ->all();

        $statusId = collect([
            $shipmentTrack['current_status_id'] ?? null,
            is_numeric($tracking['shipment_status'] ?? null) ? $tracking['shipment_status'] : null,
            $body['current_status_id'] ?? null,
            $body['shipment_status_id'] ?? null,
        ])->first(fn ($value): bool => is_numeric($value));
        $statusId = $statusId !== null ? (int) $statusId : null;
        $latestActivity = $activities[0] ?? [];
        $status = collect([
            $shipmentTrack['current_status'] ?? null,
            $tracking['current_status'] ?? null,
            ! is_numeric($tracking['shipment_status'] ?? null) ? ($tracking['shipment_status'] ?? null) : null,
            $body['current_status'] ?? null,
            ! is_numeric($body['shipment_status'] ?? null) ? ($body['shipment_status'] ?? null) : null,
            $latestActivity['status'] ?? null,
            $latestActivity['activity'] ?? null,
            $statusId ? $this->trackingStatusLabel($statusId) : null,
            $order->shiprocket_status,
            'AWB ASSIGNED',
        ])->first(fn ($value): bool => is_scalar($value) && trim((string) $value) !== '');
        $estimatedDelivery = collect([
            $shipmentTrack['edd'] ?? null,
            $tracking['etd'] ?? null,
            $body['etd'] ?? null,
        ])->first(fn ($value): bool => is_scalar($value) && trim((string) $value) !== '');

        try {
            $estimatedDelivery = $estimatedDelivery
                ? Carbon::parse((string) $estimatedDelivery)->toDateString()
                : null;
        } catch (\Throwable) {
            $estimatedDelivery = null;
        }

        $courierName = collect([
            $shipmentTrack['courier_name'] ?? null,
            $tracking['courier_name'] ?? null,
            $body['courier_name'] ?? null,
            $order->courier_name,
        ])->first(fn ($value): bool => is_scalar($value) && trim((string) $value) !== '');
        $trackingUrl = collect([
            $tracking['track_url'] ?? null,
            $body['track_url'] ?? null,
            $order->tracking_url,
            $this->trackingUrl($awbCode),
        ])->first(function ($value): bool {
            if (! is_string($value) || ! filter_var($value, FILTER_VALIDATE_URL)) {
                return false;
            }

            return in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true);
        });

        return [
            'status' => str((string) $status)->replace('_', ' ')->squish()->upper()->toString(),
            'status_id' => $statusId,
            'courier_name' => $courierName ? trim((string) $courierName) : null,
            'estimated_delivery' => $estimatedDelivery,
            'tracking_url' => (string) $trackingUrl,
            'activities' => $activities,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function orderPayload(Order $order): array
    {
        $order->loadMissing('items.product');

        if ($order->items->isEmpty()) {
            throw new RuntimeException('The order has no items to send to Shiprocket.');
        }

        $billingAddress = $order->billing_address ?: $order->shipping_address ?: [];
        $shippingAddress = $order->shipping_address ?: $billingAddress;
        [$billingFirstName, $billingLastName] = $this->splitName(
            (string) ($billingAddress['name'] ?? $order->customer_name),
        );
        [$shippingFirstName, $shippingLastName] = $this->splitName(
            (string) ($shippingAddress['name'] ?? $order->customer_name),
        );
        $shippingIsBilling = $this->addressesMatch($billingAddress, $shippingAddress);
        $itemsTotal = round((float) $order->items->sum(fn ($item) => (float) $item->total), 2);

        $payload = [
            'order_id' => $order->order_number,
            'order_date' => $order->created_at->format('Y-m-d H:i'),
            'pickup_location' => (string) config('services.shiprocket.pickup_location', 'Home'),
            'comment' => 'Website order '.$order->order_number,
            'billing_customer_name' => $billingFirstName,
            'billing_last_name' => $billingLastName,
            'billing_address' => (string) ($billingAddress['line1'] ?? ''),
            'billing_address_2' => $this->addressLineTwo($billingAddress),
            'billing_city' => (string) ($billingAddress['city'] ?? ''),
            'billing_pincode' => (string) ($billingAddress['pincode'] ?? ''),
            'billing_state' => (string) ($billingAddress['state'] ?? ''),
            'billing_country' => (string) ($billingAddress['country'] ?? 'India'),
            'billing_email' => (string) ($billingAddress['email'] ?? $order->customer_email),
            'billing_phone' => $this->phone((string) ($billingAddress['phone'] ?? $order->customer_phone)),
            'shipping_is_billing' => $shippingIsBilling,
            'order_items' => $order->items->map(fn ($item): array => [
                'name' => $item->product_name,
                'sku' => $item->sku ?: 'ITEM-'.$item->id,
                'units' => (int) $item->quantity,
                'selling_price' => round((float) $item->price, 2),
                'discount' => 0,
                'tax' => $item->product ? round((float) $item->product->gst_percentage, 2) : 0,
                'hsn' => '',
            ])->values()->all(),
            'payment_method' => $this->paymentMethod($order),
            'shipping_charges' => round((float) $order->shipping_charge, 2),
            'giftwrap_charges' => round((float) $order->gift_wrap_charge, 2),
            'transaction_charges' => 0,
            'total_discount' => round((float) $order->coupon_discount, 2),
            'sub_total' => max(0, round($itemsTotal - (float) $order->coupon_discount, 2)),
            'length' => (float) config('services.shiprocket.length', 10),
            'breadth' => (float) config('services.shiprocket.breadth', 15),
            'height' => (float) config('services.shiprocket.height', 20),
            'weight' => $this->weight($order),
        ];

        if (! $shippingIsBilling) {
            $payload += [
                'shipping_customer_name' => $shippingFirstName,
                'shipping_last_name' => $shippingLastName,
                'shipping_address' => (string) ($shippingAddress['line1'] ?? ''),
                'shipping_address_2' => $this->addressLineTwo($shippingAddress),
                'shipping_city' => (string) ($shippingAddress['city'] ?? ''),
                'shipping_pincode' => (string) ($shippingAddress['pincode'] ?? ''),
                'shipping_country' => (string) ($shippingAddress['country'] ?? 'India'),
                'shipping_state' => (string) ($shippingAddress['state'] ?? ''),
                'shipping_email' => (string) ($shippingAddress['email'] ?? $order->customer_email),
                'shipping_phone' => $this->phone((string) ($shippingAddress['phone'] ?? $order->customer_phone)),
            ];
        }

        return $payload;
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.shiprocket.base_url'), '/').'/')
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.shiprocket.timeout', 20));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sendCreateOrder(array $payload, string $token): Response
    {
        return $this->client()
            ->withToken($token)
            ->post('orders/create/adhoc', $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sendDeliveryAddressUpdate(array $payload, string $token): Response
    {
        return $this->client()
            ->withToken($token)
            ->post('orders/address/update', $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sendCancellation(array $payload, string $token): Response
    {
        return $this->client()
            ->withToken($token)
            ->post('orders/cancel', $payload);
    }

    private function sendCourierList(string $token): Response
    {
        return $this->client()
            ->withToken($token)
            ->get('courier/courierListWithCounts', ['type' => 'active']);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private function sendServiceabilityCheck(array $query, string $token): Response
    {
        return $this->client()
            ->withToken($token)
            ->get('courier/serviceability/', $query);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sendAwbAssignment(array $payload, string $token): Response
    {
        return $this->client()
            ->withToken($token)
            ->post('courier/assign/awb', $payload);
    }

    private function sendAwbTracking(string $awbCode, string $token): Response
    {
        return $this->client()
            ->withToken($token)
            ->get('courier/track/awb/'.rawurlencode($awbCode));
    }

    private function trackingStatusLabel(int $statusId): ?string
    {
        return [
            1 => 'AWB ASSIGNED',
            2 => 'LABEL GENERATED',
            3 => 'PICKUP SCHEDULED',
            4 => 'PICKUP QUEUED',
            5 => 'MANIFEST GENERATED',
            6 => 'SHIPPED',
            7 => 'DELIVERED',
            8 => 'CANCELLED',
            9 => 'RTO INITIATED',
            10 => 'RTO DELIVERED',
            11 => 'PENDING',
            12 => 'LOST',
            13 => 'PICKUP ERROR',
            14 => 'RTO ACKNOWLEDGED',
            15 => 'PICKUP RESCHEDULED',
            16 => 'CANCELLATION REQUESTED',
            17 => 'OUT FOR DELIVERY',
            18 => 'IN TRANSIT',
            19 => 'OUT FOR PICKUP',
            20 => 'PICKUP EXCEPTION',
            21 => 'UNDELIVERED',
            22 => 'DELAYED',
            23 => 'PARTIAL DELIVERED',
            24 => 'DESTROYED',
            25 => 'DAMAGED',
            26 => 'FULFILLED',
            38 => 'REACHED AT DESTINATION',
            42 => 'PICKED UP',
        ][$statusId] ?? null;
    }

    private function errorMessage(Response $response): string
    {
        $message = $response->json('message')
            ?? $response->json('response.message')
            ?? $response->json('errors.0')
            ?? $response->reason();

        return str((string) $message)->squish()->limit(300)->toString();
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function addressLineTwo(array $address): string
    {
        return collect([$address['line2'] ?? null, $address['landmark'] ?? null])
            ->filter(fn ($value) => filled($value))
            ->join(', ');
    }

    /**
     * @param  array<string, mixed>  $billing
     * @param  array<string, mixed>  $shipping
     */
    private function addressesMatch(array $billing, array $shipping): bool
    {
        $keys = ['name', 'line1', 'line2', 'landmark', 'city', 'state', 'pincode', 'country', 'email', 'phone'];

        return collect($keys)->every(
            fn (string $key): bool => trim((string) ($billing[$key] ?? '')) === trim((string) ($shipping[$key] ?? '')),
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [];

        return [$parts[0] ?? 'Customer', $parts[1] ?? ''];
    }

    private function phone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }

        return $digits;
    }

    private function paymentMethod(Order $order): string
    {
        return $order->payment_status === 'cod' || str_contains(strtolower((string) $order->payment_method), 'cash')
            ? 'COD'
            : 'Prepaid';
    }

    private function weight(Order $order): float
    {
        $kilograms = (float) $order->items->sum(
            fn ($item): float => ((float) ($item->product?->gross_weight ?? 0) * (int) $item->quantity) / 1000,
        );

        return round(max((float) config('services.shiprocket.minimum_weight', 0.5), $kilograms), 3);
    }
}

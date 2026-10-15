<?php

namespace App\Services;

use App\Models\Product;
use App\Models\WebsiteSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class CheckoutShippingService
{
    public function __construct(private readonly ShiprocketService $shiprocket) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, mixed>  $address
     * @return array{
     *     available: bool,
     *     message: string,
     *     shipping_charge: float,
     *     maximum_freight_charge: float,
     *     free_shipping: bool,
     *     free_shipping_threshold: float,
     *     amount_to_free_shipping: float,
     *     courier_count: int,
     *     courier_name: string|null,
     *     estimated_delivery_days: string|null,
     *     estimated_date: string|null,
     *     weight: float
     * }
     */
    public function quote(array $items, array $address, float $cartTotal, string $payment = 'online'): array
    {
        $deliveryPostcode = trim((string) ($address['pincode'] ?? ''));

        if (! preg_match('/^[1-9][0-9]{5}$/', $deliveryPostcode)) {
            throw new RuntimeException('The selected delivery address does not contain a valid pincode.');
        }

        if ($items === []) {
            throw new RuntimeException('Your cart is empty.');
        }

        $weight = $this->cartWeight($items);
        $cashOnDelivery = $payment === 'cod';
        $cacheKey = 'shiprocket.checkout_rates.'.sha1(implode('|', [
            (string) config('services.shiprocket.email'),
            (string) config('services.shiprocket.pickup_postcode'),
            $deliveryPostcode,
            number_format($weight, 3, '.', ''),
            $cashOnDelivery ? '1' : '0',
        ]));
        $couriers = Cache::remember(
            $cacheKey,
            now()->addMinutes(10),
            fn (): array => $this->shiprocket->packageServiceableCouriers(
                $deliveryPostcode,
                $weight,
                $cashOnDelivery,
            ),
        );

        $settings = WebsiteSetting::current();
        $threshold = max(0, (float) $settings->free_shipping_threshold);
        $freeShipping = $threshold <= 0 || $cartTotal >= $threshold;
        $amountToFreeShipping = $freeShipping ? 0 : round($threshold - $cartTotal, 2);
        $maximumRateCourier = collect($couriers)
            ->sortByDesc(fn (array $courier): float => (float) $courier['freight_charge'])
            ->first();

        if (! is_array($maximumRateCourier)) {
            return [
                'available' => false,
                'message' => 'Delivery is not currently available for the selected address.',
                'shipping_charge' => 0,
                'maximum_freight_charge' => 0,
                'free_shipping' => $freeShipping,
                'free_shipping_threshold' => $threshold,
                'amount_to_free_shipping' => $amountToFreeShipping,
                'courier_count' => 0,
                'courier_name' => null,
                'estimated_delivery_days' => null,
                'estimated_date' => null,
                'weight' => $weight,
            ];
        }

        $maximumFreightCharge = round(max(0, (float) $maximumRateCourier['freight_charge']), 2);
        $estimatedDate = $this->estimatedDate($maximumRateCourier);

        return [
            'available' => true,
            'message' => $freeShipping
                ? 'Free shipping applies to this order.'
                : 'Shipping calculated from available couriers.',
            'shipping_charge' => $freeShipping ? 0 : $maximumFreightCharge,
            'maximum_freight_charge' => $maximumFreightCharge,
            'free_shipping' => $freeShipping,
            'free_shipping_threshold' => $threshold,
            'amount_to_free_shipping' => $amountToFreeShipping,
            'courier_count' => count($couriers),
            'courier_name' => (string) $maximumRateCourier['name'],
            'estimated_delivery_days' => $maximumRateCourier['estimated_delivery_days'] ?: null,
            'estimated_date' => $estimatedDate,
            'weight' => $weight,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function cartWeight(array $items): float
    {
        $quantities = collect($items)
            ->groupBy(fn (array $item): int => (int) data_get($item, 'product.id'))
            ->map(fn ($rows): int => (int) $rows->sum('qty'));
        $weights = Product::query()
            ->whereIn('id', $quantities->keys())
            ->pluck('gross_weight', 'id');
        $kilograms = $quantities
            ->map(fn (int $quantity, int|string $productId): float => ((float) ($weights[$productId] ?? 0) * $quantity) / 1000)
            ->sum();

        return round(max((float) config('services.shiprocket.minimum_weight', 0.5), $kilograms), 3);
    }

    /**
     * @param  array<string, mixed>  $courier
     */
    private function estimatedDate(array $courier): ?string
    {
        try {
            if (filled($courier['etd'] ?? null)) {
                return Carbon::parse($courier['etd'])->format('d M Y');
            }
        } catch (\Throwable) {
            // Fall back to the courier's estimated day count.
        }

        $days = (int) ($courier['estimated_delivery_days'] ?? 0);

        return $days > 0 ? now()->addDays($days)->format('d M Y') : null;
    }
}

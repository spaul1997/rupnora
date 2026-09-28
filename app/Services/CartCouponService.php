<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartCouponService
{
    public const SESSION_KEY = 'shopping_cart.coupon_code';

    public function apply(string $code, array $items, ?User $customer = null): array
    {
        $coupon = Coupon::query()->with(['products:id', 'categories:id'])
            ->whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])
            ->lockForUpdate()
            ->first();

        if (! $coupon || ! $coupon->is_active || $coupon->start_date->isFuture() || $coupon->end_date->isPast()) {
            throw ValidationException::withMessages(['coupon' => 'This coupon is invalid or has expired.']);
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw ValidationException::withMessages(['coupon' => 'This coupon has reached its usage limit.']);
        }

        if ($customer && $coupon->usage_per_customer !== null) {
            $used = Order::query()->where('user_id', $customer->id)->where('coupon_code', $coupon->code)->count();
            if ($used >= $coupon->usage_per_customer) {
                throw ValidationException::withMessages(['coupon' => 'You have reached the usage limit for this coupon.']);
            }
        }

        $lines = collect($items)->map(function (array $item) {
            $product = $item['model'] ?? null;
            $data = $item['product'] ?? [];

            return [
                'key' => $item['key'] ?? (string) ($product?->id ?? $data['id'] ?? ''),
                'product_id' => (int) ($product?->id ?? $data['id'] ?? 0),
                'category_id' => (int) ($product?->category_id ?? $data['category_id'] ?? 0),
                'amount' => round((float) ($item['unit_price'] ?? $data['price'] ?? 0) * (int) ($item['qty'] ?? 1), 2),
            ];
        });
        $sellingTotal = round((float) $lines->sum('amount'), 2);

        if ($coupon->minimum_order !== null && $sellingTotal < (float) $coupon->minimum_order) {
            throw ValidationException::withMessages(['coupon' => 'This coupon requires a minimum order of ₹'.number_format((float) $coupon->minimum_order, 2).'.']);
        }

        $productIds = $coupon->products->pluck('id');
        $categoryIds = $coupon->categories->pluck('id');
        $eligible = $lines->filter(fn (array $line) =>
            ($productIds->isEmpty() && $categoryIds->isEmpty()) ||
            $productIds->contains($line['product_id']) || $categoryIds->contains($line['category_id'])
        );
        $eligibleTotal = round((float) $eligible->sum('amount'), 2);

        if ($eligibleTotal <= 0) {
            throw ValidationException::withMessages(['coupon' => 'This coupon does not apply to the products in your cart.']);
        }

        $discount = $coupon->discount_type === 'percentage'
            ? $eligibleTotal * ((float) $coupon->discount_value / 100)
            : (float) $coupon->discount_value;
        if ($coupon->maximum_discount !== null) {
            $discount = min($discount, (float) $coupon->maximum_discount);
        }
        $discount = round(min($discount, $eligibleTotal), 2);

        return [
            'coupon' => $coupon,
            'discount' => $discount,
            'allocations' => $this->allocate($eligible, $eligibleTotal, $discount),
        ];
    }

    /** @return array<string, float> */
    private function allocate(Collection $eligible, float $eligibleTotal, float $discount): array
    {
        $allocations = [];
        $remaining = $discount;
        $lastKey = $eligible->keys()->last();

        foreach ($eligible as $index => $line) {
            $amount = $index === $lastKey
                ? $remaining
                : round($discount * ($line['amount'] / $eligibleTotal), 2);
            $amount = min($amount, $line['amount']);
            $allocations[(string) $line['key']] = $amount;
            $remaining = round($remaining - $amount, 2);
        }

        return $allocations;
    }
}

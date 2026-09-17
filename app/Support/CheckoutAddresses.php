<?php

namespace App\Support;

use App\Models\CustomerAddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutAddresses
{
    private const ADDRESSES_KEY = 'checkout.guest_addresses';

    public static function all(): array
    {
        if (auth()->user()?->role === 'customer') {
            return auth()->user()->addresses()
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->get()
                ->map(fn (CustomerAddress $address) => $address->toStorefront())
                ->all();
        }

        return session(self::ADDRESSES_KEY, []);
    }

    public static function add(array $data): array
    {
        if (auth()->user()?->role === 'customer') {
            return DB::transaction(function () use ($data) {
                $customer = auth()->user();
                $customer->newQuery()->whereKey($customer->id)->lockForUpdate()->firstOrFail();
                $default = ($data['default'] ?? false) || ! $customer->addresses()->exists();

                if ($default) {
                    $customer->addresses()->update(['is_default' => false]);
                }

                $address = $customer->addresses()->create([
                    ...collect($data)->except('default')->all(),
                    'is_default' => $default,
                ]);

                return $address->toStorefront();
            });
        }

        $addresses = self::all();

        $address = [
            'id' => 'new-'.Str::uuid()->toString(),
            'type' => $data['type'] ?? 'Home',
            'default' => count($addresses) === 0,
            'name' => trim($data['name']),
            'phone' => trim($data['phone']),
            'line1' => trim($data['line1']),
            'line2' => trim($data['line2'] ?? ''),
            'landmark' => trim($data['landmark'] ?? ''),
            'city' => trim($data['city']),
            'state' => trim($data['state']),
            'pincode' => trim($data['pincode']),
            'country' => trim($data['country'] ?? 'India') ?: 'India',
        ];

        $addresses[] = $address;
        session()->put(self::ADDRESSES_KEY, array_values($addresses));

        return $address;
    }
}

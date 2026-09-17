<?php

namespace App\Support;

use Illuminate\Support\Str;

class CheckoutAddresses
{
    private const ADDRESSES_KEY = 'checkout.addresses';

    private const INITIALIZED_KEY = 'checkout.addresses_initialized';

    public static function all(): array
    {
        $isCustomer = auth()->check() && auth()->user()->role === 'customer';

        if (! session()->has(self::INITIALIZED_KEY) || session('checkout.addresses_customer') !== $isCustomer) {
            self::put($isCustomer ? Catalog::addresses() : [], $isCustomer);
        }

        return session(self::ADDRESSES_KEY, []);
    }

    public static function add(array $data): array
    {
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
        self::put($addresses, auth()->check() && auth()->user()->role === 'customer');

        return $address;
    }

    private static function put(array $addresses, bool $isCustomer): void
    {
        session()->put(self::ADDRESSES_KEY, array_values($addresses));
        session()->put(self::INITIALIZED_KEY, true);
        session()->put('checkout.addresses_customer', $isCustomer);
    }
}

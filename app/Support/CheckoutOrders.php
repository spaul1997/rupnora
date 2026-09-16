<?php

namespace App\Support;

class CheckoutOrders
{
    private const ORDERS_KEY = 'checkout.orders';
    private const LATEST_ORDER_KEY = 'checkout.latest_order_id';

    public static function create(array $data): array
    {
        $items = ShoppingCart::items();
        $sellingTotal = collect($items)->sum(fn (array $item) => $item['product']['price'] * $item['qty']);
        $mrpTotal = collect($items)->sum(fn (array $item) => max($item['product']['mrp'], $item['product']['price']) * $item['qty']);
        $discount = max(0, $mrpTotal - $sellingTotal);
        $delivery = $data['delivery'] ?? 'standard';
        $shipping = $delivery === 'express' ? 199 : 0;
        $tax = round($sellingTotal * 0.03);
        $address = self::address((string) $data['address_id']);
        $payment = self::paymentMethod($data['payment'] ?? 'upi');
        $orderId = self::nextOrderId();

        $order = [
            'id' => $orderId,
            'date' => now()->format('d M Y'),
            'status' => 'Processing',
            'payment_status' => ($data['payment'] ?? 'upi') === 'cod' ? 'Pending' : 'Paid',
            'payment_method' => $payment,
            'delivery_option' => $delivery === 'express' ? 'Express Delivery' : 'Standard Delivery',
            'expected_delivery' => now()->addDays($delivery === 'express' ? 3 : 7)->format('d M Y'),
            'items' => $items,
            'subtotal' => $mrpTotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $mrpTotal - $discount + $shipping + $tax,
            'address' => $address,
            'timeline' => [
                'Order Placed' => now()->format('d M Y'),
                'Confirmed' => ($data['payment'] ?? 'upi') === 'cod' ? null : now()->format('d M Y'),
                'Packed' => null,
                'Shipped' => null,
                'Out for Delivery' => null,
                'Delivered' => null,
            ],
        ];

        $orders = self::sessionOrders();
        $orders[$orderId] = $order;

        session()->put(self::ORDERS_KEY, array_slice($orders, -10, 10, true));
        session()->put(self::LATEST_ORDER_KEY, $orderId);

        return $order;
    }

    public static function find(string $id): ?array
    {
        return self::sessionOrders()[$id] ?? Catalog::order($id);
    }

    public static function latest(): ?array
    {
        $latestOrderId = session(self::LATEST_ORDER_KEY);

        return $latestOrderId ? self::find($latestOrderId) : null;
    }

    public static function all(): array
    {
        return collect(array_values(self::sessionOrders()))
            ->reverse()
            ->merge(Catalog::orders())
            ->unique('id')
            ->values()
            ->all();
    }

    private static function sessionOrders(): array
    {
        return session(self::ORDERS_KEY, []);
    }

    private static function address(string $addressId): array
    {
        return collect(CheckoutAddresses::all())
            ->first(fn (array $address) => (string) $address['id'] === $addressId)
            ?? Catalog::addresses()[0];
    }

    private static function paymentMethod(string $payment): string
    {
        return [
            'upi' => 'UPI',
            'card' => 'Credit / Debit Card',
            'netbanking' => 'Net Banking',
            'wallet' => 'Wallet',
            'cod' => 'Cash On Delivery',
        ][$payment] ?? 'UPI';
    }

    private static function nextOrderId(): string
    {
        do {
            $orderId = 'ORD-'.now()->format('Y').'-'.random_int(10000, 99999);
        } while (self::find($orderId));

        return $orderId;
    }
}

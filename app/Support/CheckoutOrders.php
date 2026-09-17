<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutOrders
{
    private const ORDERS_KEY = 'checkout.orders';

    private const LATEST_ORDER_KEY = 'checkout.latest_order_id';

    public static function create(array $data): array
    {
        if (auth()->user()?->role === 'customer') {
            return self::createCustomerOrder($data);
        }

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
            'customer_id' => null,
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
        if (auth()->user()?->role === 'customer') {
            $order = self::customerQuery()->where('order_number', $id)->first();

            return $order ? self::mapOrder($order) : null;
        }

        $order = self::sessionOrders()[$id] ?? null;

        return $order && array_key_exists('customer_id', $order) && $order['customer_id'] === null ? $order : null;
    }

    public static function latest(): ?array
    {
        $latestOrderId = session(self::LATEST_ORDER_KEY);

        return $latestOrderId ? self::find($latestOrderId) : null;
    }

    public static function all(): array
    {
        if (auth()->user()?->role === 'customer') {
            return self::customerQuery()->latest()->get()->map(fn (Order $order) => self::mapOrder($order))->all();
        }

        return collect(array_values(self::sessionOrders()))
            ->filter(fn (array $order) => array_key_exists('customer_id', $order) && $order['customer_id'] === null)
            ->reverse()
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
            ?? throw ValidationException::withMessages(['address_id' => 'Please select a saved delivery address.']);
    }

    private static function customerQuery(): Builder
    {
        return Order::query()->where('user_id', auth()->id())
            ->with(['items.product.images', 'statusHistories']);
    }

    private static function createCustomerOrder(array $data): array
    {
        $items = ShoppingCart::items();
        $address = self::address((string) $data['address_id']);
        $shipping = $data['delivery'] === 'express' ? 199 : 0;

        $order = DB::transaction(function () use ($data, $items, $address, $shipping) {
            $products = Product::query()->whereIn('id', array_column(array_column($items, 'product'), 'id'))
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $quantities = collect($items)->groupBy('product.id')->map(fn ($rows) => $rows->sum('qty'));

            foreach ($quantities as $id => $quantity) {
                $product = $products->get($id);

                if (! $product || ! $product->is_active || $product->stock_quantity < $quantity) {
                    throw ValidationException::withMessages(['cart' => 'One of your items is no longer available in the requested quantity.']);
                }
            }

            foreach ($items as &$item) {
                $product = $products->get($item['product']['id']);
                $item['product']['price'] = $product->computeFinalPrice();
                $item['product']['mrp'] = (float) $product->mrp;
            }
            unset($item);

            $sellingTotal = collect($items)->sum(fn ($item) => $item['product']['price'] * $item['qty']);
            $mrpTotal = collect($items)->sum(fn ($item) => max($item['product']['mrp'], $item['product']['price']) * $item['qty']);
            $tax = round($sellingTotal * 0.03);
            $customer = auth()->user();
            $order = $customer->orders()->create([
                'order_number' => 'ORD-'.now()->format('Y').'-'.Str::upper(Str::random(12)),
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'status' => 'processing',
                'payment_status' => $data['payment'] === 'cod' ? 'cod' : 'pending',
                'payment_method' => self::paymentMethod($data['payment']),
                'subtotal' => $mrpTotal,
                'discount_amount' => $mrpTotal - $sellingTotal,
                'shipping_charge' => $shipping,
                'gst_amount' => $tax,
                'grand_total' => $sellingTotal + $shipping + $tax,
                'shipping_address' => $address,
                'billing_address' => $address,
                'estimated_delivery' => now()->addDays($data['delivery'] === 'express' ? 3 : 7),
                'stock_reserved' => true,
            ]);

            foreach ($items as $item) {
                $product = $item['product'];
                $order->items()->create([
                    'product_id' => $product['id'], 'product_name' => $product['name'], 'sku' => $product['sku'],
                    'metal' => $product['metal'], 'purity' => $product['purity'], 'size' => $item['size'],
                    'quantity' => $item['qty'], 'price' => $product['price'], 'total' => $product['price'] * $item['qty'],
                ]);
            }

            foreach ($quantities as $id => $quantity) {
                $product = $products->get($id);
                $product->stock_quantity -= $quantity;
                $product->save();
            }

            $order->statusHistories()->create(['status' => 'processing', 'remark' => 'Order placed by customer.', 'updated_by' => $customer->id]);

            return $order;
        });

        session()->put(self::LATEST_ORDER_KEY, $order->order_number);

        return self::mapOrder($order->load(['items.product.images', 'statusHistories']));
    }

    private static function mapOrder(Order $order): array
    {
        $addressDefaults = ['id' => 0, 'type' => 'Home', 'default' => false, 'name' => $order->customer_name,
            'phone' => $order->customer_phone, 'line1' => '', 'line2' => '', 'landmark' => '',
            'city' => '', 'state' => '', 'pincode' => '', 'country' => 'India'];
        $timeline = ['Order Placed' => $order->created_at->format('d M Y'), 'Confirmed' => null,
            'Packed' => null, 'Shipped' => null, 'Out for Delivery' => null, 'Delivered' => $order->delivered_at?->format('d M Y')];

        foreach ($order->statusHistories->sortBy('created_at') as $history) {
            $step = match ($history->status) {
                'confirmed', 'processing' => 'Confirmed', 'packed' => 'Packed', 'shipped' => 'Shipped',
                'out_for_delivery' => 'Out for Delivery', 'delivered' => 'Delivered', default => null,
            };
            if ($step) {
                $timeline[$step] = $history->created_at->format('d M Y');
            }
        }

        return [
            'id' => $order->order_number, 'date' => $order->created_at->format('d M Y'),
            'status' => $order->status === 'out_for_delivery' ? 'Out for Delivery' : Str::headline($order->status),
            'payment_status' => $order->payment_status === 'cod' ? 'Cash on Delivery' : Str::headline($order->payment_status),
            'payment_method' => $order->payment_method ?? 'Pending',
            'subtotal' => (float) $order->subtotal, 'discount' => (float) $order->discount_amount,
            'coupon' => $order->coupon_code, 'coupon_discount' => (float) $order->coupon_discount,
            'shipping' => (float) $order->shipping_charge, 'tax' => (float) $order->gst_amount, 'total' => (float) $order->grand_total,
            'expected_delivery' => $order->estimated_delivery?->format('d M Y'),
            'address' => [...$addressDefaults, ...($order->shipping_address ?? [])],
            'billing_address' => [...$addressDefaults, ...($order->billing_address ?? $order->shipping_address ?? [])],
            'timeline' => $timeline,
            'tracking_url' => filter_var($order->tracking_url, FILTER_VALIDATE_URL) && in_array(parse_url($order->tracking_url, PHP_URL_SCHEME), ['http', 'https']) ? $order->tracking_url : null,
            'tracking_number' => $order->tracking_number,
            'courier_name' => $order->courier_name,
            'items' => $order->items->map(function (OrderItem $item) {
                $product = $item->product;
                $image = $product?->images->firstWhere('is_primary', true) ?? $product?->images->first();

                return ['qty' => $item->quantity, 'size' => $item->size, 'product' => [
                    'id' => $item->product_id ?? 0, 'slug' => $product?->slug,
                    'available' => $product && $product->is_active,
                    'name' => $item->product_name, 'sku' => $item->sku, 'price' => (float) $item->price,
                    'art' => 'ring', 'image' => $image ? asset('storage/'.$image->image_path) : null,
                ]];
            })->all(),
        ];
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

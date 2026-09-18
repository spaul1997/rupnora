<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutOrders
{
    private const ORDERS_KEY = 'checkout.orders';

    private const GUEST_ORDER_IDS_KEY = 'checkout.guest_order_ids';

    private const LATEST_ORDER_KEY = 'checkout.latest_order_id';

    public static function create(array $data): array
    {
        return self::createCustomerOrder($data);
    }

    public static function find(string $id): ?array
    {
        if (auth()->user()?->role === 'customer') {
            $order = self::customerQuery()->where('order_number', $id)->first();

            return $order ? self::mapOrder($order) : null;
        }

        if (in_array($id, session(self::GUEST_ORDER_IDS_KEY, []), true)) {
            $order = Order::query()->where('order_number', $id)
                ->with(['items.product.images', 'statusHistories'])->first();

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

        $orders = Order::query()->whereIn('order_number', session(self::GUEST_ORDER_IDS_KEY, []))
            ->with(['items.product.images', 'statusHistories'])->latest('id')->get()
            ->map(fn (Order $order) => self::mapOrder($order))->all();

        $legacyOrders = collect(array_values(self::sessionOrders()))
            ->filter(fn (array $order) => array_key_exists('customer_id', $order) && $order['customer_id'] === null)
            ->reverse()
            ->unique('id')
            ->values()
            ->all();

        return [...$orders, ...$legacyOrders];
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
        $shipping = $data['delivery'] === 'express' ? (float) WebsiteSetting::current()->express_delivery_charge : 0;
        $guestCheckout = auth()->user()?->role !== 'customer';

        if ($guestCheckout) {
            $address['email'] = mb_strtolower(trim($address['email'] ?? ''));
            $address['phone'] = trim($address['phone'] ?? '');
        }

        $order = DB::transaction(function () use ($data, $items, $address, $shipping, $guestCheckout) {
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
            $customer = $guestCheckout ? self::resolveCustomer($address) : auth()->user();

            if (! $customer->is_active) {
                throw ValidationException::withMessages(['email' => 'Please use an active customer account.']);
            }

            if ($guestCheckout) {
                $savedAddress = $customer->addresses()->firstOrCreate(
                    collect($address)->only([
                        'type', 'name', 'email', 'phone', 'line1', 'line2', 'landmark',
                        'city', 'district', 'state', 'pincode', 'country',
                    ])->all(),
                    ['is_default' => ! $customer->addresses()->exists()],
                );
                $address = $savedAddress->toStorefront();
            }

            $order = $customer->orders()->create([
                'order_number' => 'ORD-'.now()->format('Y').'-'.Str::upper(Str::random(12)),
                'customer_name' => $guestCheckout ? $address['name'] : $customer->name,
                'customer_email' => $guestCheckout ? mb_strtolower(trim($address['email'])) : $customer->email,
                'customer_phone' => $guestCheckout ? $address['phone'] : $customer->phone,
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
        }, 3);

        if ($guestCheckout) {
            $orderIds = session(self::GUEST_ORDER_IDS_KEY, []);
            $orderIds[] = $order->order_number;
            session()->put(self::GUEST_ORDER_IDS_KEY, array_slice(array_unique($orderIds), -10));
        }

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
            'online' => 'Online Payment',
            'cod' => 'Cash on Delivery',
        ][$payment] ?? 'Online Payment';
    }

    private static function resolveCustomer(array $address): User
    {
        $email = mb_strtolower(trim($address['email'] ?? ''));
        $phone = trim($address['phone'] ?? '');
        Validator::make(['email' => $email, 'phone' => $phone], [
            'email' => ['required', 'email', 'max:254'],
            'phone' => ['required', 'string', 'max:30'],
        ])->validate();

        $phoneDigits = preg_replace('/\D+/', '', $phone);
        $phoneVariants = [$phoneDigits];

        if (strlen($phoneDigits) === 10) {
            $phoneVariants[] = '91'.$phoneDigits;
        } elseif (strlen($phoneDigits) === 12 && str_starts_with($phoneDigits, '91')) {
            $phoneVariants[] = substr($phoneDigits, 2);
        }

        $customers = User::query()->where(function (Builder $query) use ($email, $phone, $phoneDigits, $phoneVariants) {
            $query->whereRaw('LOWER(email) = ?', [$email])->orWhere('phone', $phone);

            if ($phoneDigits !== '') {
                $normalizedPhone = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '+', ''), '-', ''), '(', ''), ')', ''), '.', '')";
                $query->orWhereIn(DB::raw($normalizedPhone), $phoneVariants);
            }
        })->orderBy('id')->lockForUpdate()->get();

        if ($customers->count() > 1) {
            throw ValidationException::withMessages([
                'email' => 'This email and mobile number match different accounts. Please use contact details for one account.',
            ]);
        }

        $customer = $customers->first() ?? User::firstOrCreate(['email' => $email], [
            'name' => trim($address['name']),
            'phone' => $phone,
            'password' => $phone,
            'role' => 'customer',
            'is_active' => true,
        ]);

        if ($customer->role !== 'customer' || ! $customer->is_active) {
            throw ValidationException::withMessages(['email' => 'Please use contact details for an active customer account.']);
        }

        return $customer;
    }
}

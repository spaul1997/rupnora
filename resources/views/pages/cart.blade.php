@php
    $mrpTotal = collect($items)->sum(fn ($i) => max($i['product']['mrp'], $i['product']['price']) * $i['qty']);
    $sellingTotal = collect($items)->sum(fn ($i) => $i['product']['price'] * $i['qty']);
    $discount = max(0, $mrpTotal - $sellingTotal);
    $tax = round($sellingTotal * 0.03);
    $cartStateItems = collect($items)->map(function ($item) {
        $product = $item['product'];

        return [
            'key' => $item['key'],
            'id' => (string) $product['id'],
            'wishlistId' => (string) ($product['slug'] ?? $product['id']),
            'name' => $product['name'],
            'price' => (float) $product['price'],
            'mrp' => (float) max($product['mrp'], $product['price']),
            'qty' => (int) $item['qty'],
            'max' => (int) ($item['max_qty'] ?? 5),
            'updateUrl' => route('cart.update', $item['key']),
            'removeUrl' => route('cart.destroy', $item['key']),
            'wishlistUrl' => route('cart.move-to-wishlist', $item['key']),
        ];
    })->values()->all();
@endphp

<x-layouts.app title="Shopping Cart">
    <div class="container-luxe pt-6">
        <x-ui.breadcrumb :trail="[['label' => 'Shopping Cart']]" />
    </div>

    <div class="container-luxe py-8" x-data="cartPage({{ Illuminate\Support\Js::from($cartStateItems) }})">
        <h1 class="font-display text-3xl text-charcoal sm:text-4xl">Shopping Cart</h1>

        <div x-show="!hasItems" @if(count($items) > 0) x-cloak @endif class="mt-10">
            <x-ui.empty-state icon="bag" title="Your cart is empty" description="Looks like you haven't added anything yet. Explore our collections to find something you'll love." actionLabel="Start Shopping" :actionUrl="route('home')" />
        </div>

        <div x-show="hasItems" @if(count($items) === 0) x-cloak @endif class="mt-8 grid grid-cols-1 gap-10 lg:grid-cols-3 lg:gap-14">
            <div class="lg:col-span-2">
                <div class="divide-y divide-line rounded-2xl border border-line px-5 sm:px-6">
                    @foreach ($items as $item)
                        <x-ui.cart-item :item="$item" :index="$loop->index" />
                    @endforeach
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                    <a href="{{ route('collections.index') }}" class="flex items-center gap-1.5 text-sm font-medium text-charcoal hover:text-champagne-dark">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        Continue Shopping
                    </a>
                </div>
            </div>

            <div>
                <div class="card-luxe p-6">
                    <h3 class="font-display text-lg text-charcoal">Order Summary</h3>

                    <div class="mt-4 flex gap-2" x-data="{ code: '' }">
                        <input type="text" x-model="code" placeholder="Enter Coupon Code" class="input-luxe flex-1 !py-2.5 text-sm">
                        <button type="button" class="btn-secondary !px-5 !py-2.5 text-[11px]">Apply</button>
                    </div>

                    <div class="mt-5 space-y-3 border-t border-line pt-5 text-sm">
                        <div class="flex justify-between text-muted">
                            <span>Subtotal</span>
                            <span class="text-charcoal" x-text="formatMoney(mrpTotal)">₹{{ number_format($mrpTotal) }}</span>
                        </div>
                        <div x-show="discount > 0" class="flex justify-between text-muted">
                            <span>Discount</span>
                            <span class="text-success" x-text="'-' + formatMoney(discount)">&minus;₹{{ number_format($discount) }}</span>
                        </div>
                        <div class="flex justify-between text-muted">
                            <span>Shipping</span>
                            <span class="text-charcoal">Free</span>
                        </div>
                        <div class="flex justify-between text-muted">
                            <span>Tax (GST incl.)</span>
                            <span class="text-charcoal" x-text="formatMoney(tax)">₹{{ number_format($tax) }}</span>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-between border-t border-line pt-5">
                        <span class="font-display text-lg text-charcoal">Total</span>
                        <span class="font-display text-lg text-charcoal" x-text="formatMoney(total)">₹{{ number_format($sellingTotal + $tax) }}</span>
                    </div>

                    <a href="{{ route('checkout') }}" class="btn-primary mt-6 w-full">Proceed to Checkout</a>

                    <div class="mt-5 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 border-t border-line pt-5 text-[11px] text-muted">
                        <span class="flex items-center gap-1.5"><svg class="h-3.5 w-3.5 text-champagne-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6l8-4z" /></svg> Secure Checkout</span>
                        <span class="flex items-center gap-1.5"><svg class="h-3.5 w-3.5 text-champagne-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h13v10H3zM16 10h3l2 3v4h-5z" /><circle cx="7.5" cy="18" r="1.5" /><circle cx="17.5" cy="18" r="1.5" /></svg> Free Shipping</span>
                        <span class="flex items-center gap-1.5"><svg class="h-3.5 w-3.5 text-champagne-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V4" /></svg> Easy Returns</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

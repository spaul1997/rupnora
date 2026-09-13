@php
    $subtotal = collect($items)->sum(fn ($i) => $i['product']['price'] * $i['qty']);
    $mrpTotal = collect($items)->sum(fn ($i) => $i['product']['mrp'] * $i['qty']);
    $discount = $mrpTotal - $subtotal;
    $tax = round($subtotal * 0.03);
@endphp

<x-layouts.app title="Shopping Cart">
    <div class="container-luxe pt-6">
        <x-ui.breadcrumb :trail="[['label' => 'Shopping Cart']]" />
    </div>

    <div class="container-luxe py-8">
        <h1 class="font-display text-3xl text-charcoal sm:text-4xl">Shopping Cart</h1>

        @if (count($items) === 0)
            <div class="mt-10">
                <x-ui.empty-state icon="bag" title="Your cart is empty" description="Looks like you haven't added anything yet. Explore our collections to find something you'll love." actionLabel="Start Shopping" :actionUrl="route('home')" />
            </div>
        @else
            <div class="mt-8 grid grid-cols-1 gap-10 lg:grid-cols-3 lg:gap-14">
                <div class="lg:col-span-2">
                    <div class="divide-y divide-line rounded-2xl border border-line px-5 sm:px-6">
                        @foreach ($items as $item)
                            <x-ui.cart-item :item="$item" />
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
                    <x-ui.order-summary :subtotal="$subtotal" :discount="$discount" :shipping="0" :tax="$tax" ctaLabel="Proceed to Checkout" :ctaUrl="route('checkout')" />
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>

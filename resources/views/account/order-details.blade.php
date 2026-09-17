<x-layouts.app :title="$title">
    <x-account.shell active="orders">
        <div class="mb-6">
            <a href="{{ route('account.orders') }}" class="flex items-center gap-1.5 text-sm text-muted hover:text-charcoal">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                Back to Orders
            </a>
        </div>

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl text-charcoal sm:text-3xl">Order {{ $order['id'] }}</h1>
                <p class="mt-1 text-sm text-muted">Placed on {{ $order['date'] }} &middot; {{ $order['payment_method'] }} &middot; Payment {{ $order['payment_status'] }}</p>
            </div>
            <x-ui.order-status-badge :status="$order['status']" />
        </div>

        {{-- Tracker --}}
        <div class="mt-8 rounded-2xl border border-line p-5 sm:p-8">
            <x-ui.order-tracker :timeline="$order['timeline']" :cancelled="$order['status'] === 'Cancelled'" />
        </div>

        {{-- Products --}}
        <div class="mt-8 rounded-2xl border border-line p-5 sm:p-6">
            <h2 class="font-display text-lg text-charcoal">Items in this Order</h2>
            <div class="mt-3 divide-y divide-line">
                @foreach ($order['items'] as $item)
                    @php
                        $product = $item['product'];
                        $productUrlKey = $product['slug'] ?? $product['id'];
                    @endphp
                    <div class="flex items-center gap-4 py-4">
                        @if (! empty($product['image']))
                            <x-ui.optimized-image :src="$product['image']" :alt="$product['name']" sizes="64px" class="h-16 w-16 flex-shrink-0 rounded-lg bg-ivory-soft object-cover" />
                        @else
                            <x-ui.product-art :art="$product['art']" class="h-16 w-16 flex-shrink-0 rounded-lg" />
                        @endif
                        <div class="min-w-0 flex-1">
                            @if ($product['available'])
                                <a href="{{ route('product.show', $productUrlKey) }}" class="block truncate font-display text-[15px] text-charcoal hover:text-champagne-dark">{{ $product['name'] }}</a>
                            @else
                                <p class="truncate font-display text-[15px] text-charcoal">{{ $product['name'] }}</p>
                            @endif
                            <p class="mt-0.5 text-xs text-muted">SKU: {{ $product['sku'] }} &middot; Qty: {{ $item['qty'] }}@if($item['size']) &middot; Size: {{ $item['size'] }}@endif</p>
                        </div>
                        <p class="flex-shrink-0 text-sm font-semibold text-charcoal">₹{{ number_format($product['price'] * $item['qty']) }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-line p-5 sm:p-6">
                <h2 class="font-display text-lg text-charcoal">Shipping Address</h2>
                <div class="mt-3"><x-ui.address-card :address="$order['address']" /></div>
            </div>
            <div class="rounded-2xl border border-line p-5 sm:p-6">
                <h2 class="font-display text-lg text-charcoal">Billing Address</h2>
                <div class="mt-3"><x-ui.address-card :address="$order['billing_address']" /></div>
            </div>
        </div>

        {{-- Price summary --}}
        <div class="mt-8 rounded-2xl border border-line p-5 sm:p-6">
            <h2 class="font-display text-lg text-charcoal">Price Summary</h2>
            <div class="mt-4 space-y-2.5 text-sm">
                <div class="flex justify-between text-muted"><span>Subtotal</span><span class="text-charcoal">₹{{ number_format($subtotal) }}</span></div>
                <div class="flex justify-between text-muted"><span>Discount</span><span class="text-success">&minus;₹{{ number_format($order['discount']) }}</span></div>
                <div class="flex justify-between text-muted"><span>Coupon {{ $order['coupon'] }}</span><span class="text-charcoal">{{ $order['coupon_discount'] ? '−₹'.number_format($order['coupon_discount']) : '—' }}</span></div>
                <div class="flex justify-between text-muted"><span>Shipping</span><span class="text-charcoal">{{ $shipping ? '₹'.number_format($shipping) : 'Free' }}</span></div>
                <div class="flex justify-between text-muted"><span>Tax (GST incl.)</span><span class="text-charcoal">₹{{ number_format($tax) }}</span></div>
            </div>
            <div class="mt-4 flex justify-between border-t border-line pt-4">
                <span class="font-display text-lg text-charcoal">Grand Total</span>
                <span class="font-display text-lg text-charcoal">₹{{ number_format($order['total']) }}</span>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-8 flex flex-wrap gap-3">
            @if (in_array($order['status'], ['Shipped', 'Out for Delivery']))
                @if ($order['tracking_url'])
                    <a href="{{ $order['tracking_url'] }}" target="_blank" rel="noopener noreferrer" class="btn-primary">Track Shipment</a>
                @endif
            @endif
            <a href="{{ route('account.orders.invoice', $order['id']) }}" target="_blank" rel="noopener" class="btn-secondary">Invoice / Save PDF</a>
            <a href="{{ route('account.support') }}" class="btn-secondary">Contact Support</a>
            @if (in_array($order['status'], ['Pending', 'Processing', 'Confirmed']))
                <form method="POST" action="{{ route('account.orders.cancel', $order['id']) }}">
                    @csrf
                    <button type="submit" class="btn-ghost text-error border-error/30 hover:bg-error/10">Cancel Order</button>
                </form>
            @endif
            @if ($order['status'] === 'Delivered')
                <form method="POST" action="{{ route('account.orders.return', $order['id']) }}">
                    @csrf
                    <button type="submit" class="btn-ghost">Request Return</button>
                </form>
            @endif
        </div>
        @if ($order['courier_name'] || $order['tracking_number'] || $order['expected_delivery'])
            <div class="mt-5 space-y-1 text-sm text-muted">
                @if ($order['courier_name']) <p>Courier: {{ $order['courier_name'] }}</p> @endif
                @if ($order['tracking_number']) <p>Tracking number: {{ $order['tracking_number'] }}</p> @endif
                @if ($order['expected_delivery']) <p>Expected delivery: {{ $order['expected_delivery'] }}</p> @endif
            </div>
        @endif
    </x-account.shell>
</x-layouts.app>

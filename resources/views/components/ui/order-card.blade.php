@props(['order'])

<div class="card-luxe p-5 sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line pb-4">
        <div>
            <p class="text-xs text-muted">Order ID</p>
            <p class="text-sm font-semibold text-charcoal">{{ $order['id'] }}</p>
        </div>
        <div>
            <p class="text-xs text-muted">Order Date</p>
            <p class="text-sm text-charcoal">{{ $order['date'] }}</p>
        </div>
        <div>
            <p class="text-xs text-muted">Payment</p>
            <p class="text-sm text-charcoal">{{ $order['payment_status'] }}</p>
        </div>
        <x-ui.order-status-badge :status="$order['status']" />
    </div>

    <div class="divide-y divide-line">
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
                    @if ($product['available'] ?? true)
                        <a href="{{ route('product.show', $productUrlKey) }}" class="block truncate font-display text-[15px] text-charcoal hover:text-champagne-dark">{{ $product['name'] }}</a>
                    @else
                        <p class="truncate font-display text-[15px] text-charcoal">{{ $product['name'] }}</p>
                    @endif
                    <p class="mt-0.5 text-xs text-muted">
                        Qty: {{ $item['qty'] }}
                        @if ($item['size']) &middot; Size: {{ $item['size'] }} @endif
                    </p>
                </div>
                <p class="flex-shrink-0 text-sm font-semibold text-charcoal">₹{{ number_format($product['price'] * $item['qty']) }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4">
        <p class="text-sm text-muted">Order Total <span class="ml-1 font-semibold text-charcoal">₹{{ number_format($order['total']) }}</span></p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('account.orders.show', $order['id']) }}" class="btn-ghost !px-4 !py-2 text-[11px]">View Details</a>
            @if (in_array($order['status'], ['Shipped', 'Out for Delivery']))
                <a href="{{ route('account.orders.show', $order['id']) }}" class="btn-secondary !px-4 !py-2 text-[11px]">Track Order</a>
            @endif
            <a href="{{ route('account.orders.invoice', $order['id']) }}" target="_blank" rel="noopener" class="btn-secondary !px-4 !py-2 text-[11px]">Invoice / Save PDF</a>
            @if ($order['status'] === 'Delivered')
                <form method="POST" action="{{ route('account.orders.buy-again', $order['id']) }}">
                    @csrf
                    <button type="submit" class="btn-secondary !px-4 !py-2 text-[11px]">Buy Again</button>
                </form>
                <form method="POST" action="{{ route('account.orders.return', $order['id']) }}">
                    @csrf
                    <button type="submit" class="btn-ghost !px-4 !py-2 text-[11px]">Request Return</button>
                </form>
            @endif
            @if (in_array($order['status'], ['Pending', 'Processing', 'Confirmed']))
                <form method="POST" action="{{ route('account.orders.cancel', $order['id']) }}">
                    @csrf
                    <button type="submit" class="btn-ghost !px-4 !py-2 text-[11px] text-error border-error/30 hover:bg-error/10">Cancel Order</button>
                </form>
            @endif
        </div>
    </div>
</div>

@php
    $subtotal = collect($order['items'])->sum(fn ($i) => $i['product']['price'] * $i['qty']);
@endphp

<x-layouts.app title="Order Confirmed">
    <div class="container-luxe py-16 sm:py-20">
        <div class="mx-auto max-w-2xl text-center">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-success/10">
                <svg class="h-9 w-9 text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </div>
            <h1 class="font-display mt-6 text-3xl text-charcoal sm:text-4xl">Thank You For Your Order</h1>
            <p class="mt-3 text-sm text-muted sm:text-base">Your order has been placed successfully. A confirmation has been sent to your registered email.</p>
        </div>

        <div class="mx-auto mt-10 max-w-2xl rounded-2xl border border-line p-6 sm:p-8">
            <div class="grid grid-cols-2 gap-6 sm:grid-cols-3">
                <div>
                    <p class="text-xs text-muted">Order ID</p>
                    <p class="mt-1 text-sm font-semibold text-charcoal">{{ $order['id'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted">Payment Status</p>
                    <p class="mt-1 text-sm font-semibold text-success">{{ $order['payment_status'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted">Expected Delivery</p>
                    <p class="mt-1 text-sm font-semibold text-charcoal">{{ now()->addDays(6)->format('d M Y') }}</p>
                </div>
            </div>

            <div class="mt-6 border-t border-line pt-6">
                <p class="text-xs font-medium uppercase tracking-wide text-muted">Shipping Address</p>
                <p class="mt-2 text-sm leading-relaxed text-charcoal">
                    {{ $order['address']['name'] }}<br>
                    {{ $order['address']['line1'] }}, {{ $order['address']['line2'] }}<br>
                    {{ $order['address']['city'] }}, {{ $order['address']['state'] }} {{ $order['address']['pincode'] }}<br>
                    {{ $order['address']['phone'] }}
                </p>
            </div>

            <div class="mt-6 border-t border-line pt-6">
                <p class="mb-3 text-xs font-medium uppercase tracking-wide text-muted">Order Summary</p>
                <div class="divide-y divide-line">
                    @foreach ($order['items'] as $item)
                        <div class="flex items-center gap-3 py-3">
                            <x-ui.product-art :art="$item['product']['art']" class="h-14 w-14 flex-shrink-0 rounded-lg" />
                            <span class="min-w-0 flex-1 truncate text-sm text-charcoal">{{ $item['product']['name'] }} &times; {{ $item['qty'] }}</span>
                            <span class="flex-shrink-0 text-sm font-medium text-charcoal">₹{{ number_format($item['product']['price'] * $item['qty']) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 flex justify-between border-t border-line pt-3">
                    <span class="font-display text-base text-charcoal">Total Paid</span>
                    <span class="font-display text-base text-charcoal">₹{{ number_format($subtotal) }}</span>
                </div>
            </div>
        </div>

        <div class="mx-auto mt-8 flex max-w-2xl flex-col gap-3 sm:flex-row">
            <a href="{{ route('account.orders.show', $order['id']) }}" class="btn-primary flex-1 text-center">View Order</a>
            <a href="{{ route('home') }}" class="btn-secondary flex-1 text-center">Continue Shopping</a>
        </div>
    </div>
</x-layouts.app>

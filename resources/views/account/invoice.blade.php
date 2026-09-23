<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->order_number }}</title>
    @vite(['resources/css/app.css'])
    <style>@media print { .no-print { display: none !important; } body { padding: 0 !important; } }</style>
</head>
<body class="bg-paper p-6 font-sans text-charcoal sm:p-10">
    <div class="mx-auto max-w-3xl">
        <div class="no-print mb-8 flex flex-wrap justify-between gap-3">
            <a href="{{ route('account.orders.show', $order->order_number) }}" class="btn-secondary">Back to Order</a>
            <button type="button" onclick="window.print()" class="btn-primary">Print / Save as PDF</button>
        </div>
        <div class="flex justify-between border-b border-line pb-6">
            <p class="font-display text-2xl">{{ $settings->company_name ?: 'Rupnora' }}</p>
            <div class="text-right">
                <h1 class="font-display text-2xl">Invoice</h1>
                <p class="mt-1 text-sm">{{ $order->order_number }}</p>
                <p class="text-sm text-muted">{{ $order->created_at->format('d M Y') }}</p>
            </div>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-6 text-sm">
            <div>
                <h2 class="font-semibold">Billed To</h2>
                <p class="mt-2">{{ $order->customer_name }}</p>
                <p>{{ $order->customer_email }}</p>
                <p>{{ $order->customer_phone }}</p>
            </div>
            <div>
                <h2 class="font-semibold">Shipping Address</h2>
                <p class="mt-2">{{ collect([$order->shipping_address['line1'] ?? '', $order->shipping_address['line2'] ?? ''])->filter()->join(', ') }}</p>
                <p>{{ collect([$order->shipping_address['city'] ?? '', $order->shipping_address['district'] ?? '', $order->shipping_address['state'] ?? ''])->filter()->join(', ') }} {{ $order->shipping_address['pincode'] ?? '' }}</p>
                <p>{{ $order->shipping_address['country'] ?? '' }}</p>
            </div>
        </div>
        @if ($order->gift_wrap)
            <div class="mt-6 rounded-lg border border-line p-4 text-sm">
                <div class="flex justify-between gap-4">
                    <h2 class="font-semibold">Gift Wrap</h2>
                    <span>₹{{ number_format($order->gift_wrap_charge, 2) }}</span>
                </div>
                @if ($order->gift_to || $order->gift_from)
                    <p class="mt-2">@if ($order->gift_to) To: {{ $order->gift_to }} @endif @if ($order->gift_to && $order->gift_from) &middot; @endif @if ($order->gift_from) From: {{ $order->gift_from }} @endif</p>
                @endif
                @if ($order->gift_message)<p class="mt-2 whitespace-pre-line italic text-muted">&ldquo;{{ $order->gift_message }}&rdquo;</p>@endif
            </div>
        @endif
        <table class="mt-8 w-full text-sm">
            <thead><tr class="border-b border-line text-left"><th class="py-3">Product</th><th>Qty</th><th class="text-right">Price</th><th class="text-right">Total</th></tr></thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr class="border-b border-line">
                        <td class="py-3">{{ $item->product_name }}<span class="block text-xs text-muted">{{ $item->sku }} @if ($item->size) &middot; {{ $item->size }} @endif</span></td>
                        <td>{{ $item->quantity }}</td><td class="text-right">₹{{ number_format($item->price, 2) }}</td><td class="text-right">₹{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="ml-auto mt-6 max-w-xs space-y-2 text-sm">
            <p class="flex justify-between"><span>Subtotal</span><span>₹{{ number_format($order->subtotal, 2) }}</span></p>
            <p class="flex justify-between"><span>Discount</span><span>−₹{{ number_format($order->discount_amount + $order->coupon_discount, 2) }}</span></p>
            <p class="flex justify-between"><span>Shipping</span><span>₹{{ number_format($order->shipping_charge, 2) }}</span></p>
            <p class="flex justify-between"><span>GST</span><span>₹{{ number_format($order->gst_amount, 2) }}</span></p>
            @if ($order->gift_wrap)<p class="flex justify-between"><span>Gift Wrap</span><span>₹{{ number_format($order->gift_wrap_charge, 2) }}</span></p>@endif
            <p class="flex justify-between border-t border-line pt-3 font-semibold"><span>Grand Total</span><span>₹{{ number_format($order->grand_total, 2) }}</span></p>
        </div>
        @if ($settings->support_email) <p class="mt-10 text-center text-xs text-muted">For queries, contact {{ $settings->support_email }}.</p> @endif
    </div>
</body>
</html>

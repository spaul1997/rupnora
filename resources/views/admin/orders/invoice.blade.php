<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_number }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:600|inter:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        @media print { .no-print { display: none !important; } body { padding: 0 !important; } }
    </style>
</head>
<body class="bg-white p-10 font-sans text-gray-900">
    <div class="no-print mb-6 flex justify-end">
        <button onclick="window.print()" class="admin-btn-primary">Print / Save as PDF</button>
    </div>

    <div class="mx-auto max-w-3xl">
        <div class="flex items-start justify-between border-b border-gray-200 pb-6">
            <div>
                <span class="font-display text-2xl">Aurelle</span>
                <p class="mt-1 text-xs text-gray-500">Certified Fine Jewellery</p>
            </div>
            <div class="text-right">
                <h1 class="text-lg font-semibold">Invoice</h1>
                <p class="text-sm text-gray-500">{{ $order->order_number }}</p>
                <p class="text-sm text-gray-500">{{ $order->created_at->format('d M Y') }}</p>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-6">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400">Billed To</p>
                <p class="mt-1 text-sm">{{ $order->customer_name }}</p>
                <p class="text-sm text-gray-500">{{ $order->customer_email }}</p>
                <p class="text-sm text-gray-500">{{ $order->customer_phone }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-400">Shipping Address</p>
                @if ($order->shipping_address)
                    <p class="mt-1 text-sm text-gray-600">
                        {{ $order->shipping_address['line1'] ?? '' }}, {{ $order->shipping_address['city'] ?? '' }}<br>
                        {{ $order->shipping_address['state'] ?? '' }} {{ $order->shipping_address['pincode'] ?? '' }}
                    </p>
                @endif
            </div>
        </div>

        <table class="mt-8 w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-400">
                    <th class="py-2">Product</th>
                    <th class="py-2">SKU</th>
                    <th class="py-2 text-right">Qty</th>
                    <th class="py-2 text-right">Price</th>
                    <th class="py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr class="border-b border-gray-100">
                        <td class="py-2.5">{{ $item->product_name }}</td>
                        <td class="py-2.5 text-gray-500">{{ $item->sku }}</td>
                        <td class="py-2.5 text-right">{{ $item->quantity }}</td>
                        <td class="py-2.5 text-right">₹{{ number_format($item->price, 2) }}</td>
                        <td class="py-2.5 text-right font-medium">₹{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-6 flex justify-end">
            <div class="w-64 space-y-1.5 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>₹{{ number_format($order->subtotal, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Discount</span><span>−₹{{ number_format($order->discount_amount + $order->coupon_discount, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Shipping</span><span>₹{{ number_format($order->shipping_charge, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">GST</span><span>₹{{ number_format($order->gst_amount, 2) }}</span></div>
                @if ($order->gift_wrap)<div class="flex justify-between"><span class="text-gray-500">Gift Wrap</span><span>₹{{ number_format($order->gift_wrap_charge, 2) }}</span></div>@endif
                <div class="flex justify-between border-t border-gray-200 pt-1.5 text-base font-semibold"><span>Grand Total</span><span>₹{{ number_format($order->grand_total, 2) }}</span></div>
            </div>
        </div>

        <p class="mt-10 text-center text-xs text-gray-400">Thank you for shopping with Aurelle Jewellery. For queries, contact care@aurellejewellery.com</p>
    </div>
</body>
</html>

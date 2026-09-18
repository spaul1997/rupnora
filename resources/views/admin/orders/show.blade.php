@extends('admin.layouts.app')

@section('title', 'Order ' . $order->order_number)

@section('content')
    <x-admin.page-header :title="'Order ' . $order->order_number" :breadcrumb="[['label' => 'Orders', 'url' => route('admin.orders.index')], ['label' => $order->order_number]]">
        <x-slot:actions>
            <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="admin-btn-secondary">Download Invoice</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">

            {{-- Overview --}}
            <div class="admin-card p-6">
                <div class="flex flex-wrap items-center gap-2">
                    <x-admin.status-badge :status="$order->status" />
                    <x-admin.status-badge :status="$order->payment_status" />
                </div>
                <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.5fr)_minmax(0,0.9fr)]">
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400">Order Date</p>
                        <p class="mt-0.5 font-medium leading-snug text-gray-900">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400">Customer</p>
                        <p class="mt-0.5 break-words font-medium leading-snug text-gray-900">{{ $order->customer_name }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400">Email</p>
                        <a href="mailto:{{ $order->customer_email }}" class="mt-0.5 block break-all font-medium leading-snug text-gray-900 hover:text-champagne-dark">{{ $order->customer_email }}</a>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400">Phone</p>
                        <a href="tel:{{ $order->customer_phone }}" class="mt-0.5 block whitespace-nowrap font-medium leading-snug text-gray-900 hover:text-champagne-dark">{{ $order->customer_phone }}</a>
                    </div>
                </div>
            </div>

            {{-- Addresses --}}
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="admin-card p-6">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">Shipping Address</h3>
                    @if ($order->shipping_address)
                        <p class="text-sm leading-relaxed text-gray-600">
                            {{ $order->shipping_address['name'] ?? '' }}<br>
                            {{ $order->shipping_address['line1'] ?? '' }}, {{ $order->shipping_address['line2'] ?? '' }}<br>
                            {{ collect([$order->shipping_address['city'] ?? '', $order->shipping_address['district'] ?? '', $order->shipping_address['state'] ?? ''])->filter()->join(', ') }} {{ $order->shipping_address['pincode'] ?? '' }}<br>
                            {{ $order->shipping_address['country'] ?? '' }}<br>
                            {{ $order->shipping_address['phone'] ?? '' }}
                        </p>
                    @else
                        <p class="text-sm text-gray-400">No shipping address on file.</p>
                    @endif
                </div>
                <div class="admin-card p-6">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">Billing Address</h3>
                    <p class="text-sm text-gray-500">Same as shipping address.</p>
                </div>
            </div>

            {{-- Items --}}
            <x-admin.table :headers="['Product', 'SKU', 'Metal / Purity', 'Size', 'Qty', 'Price', '!Total']">
                @foreach ($order->items as $item)
                    <tr>
                        <td class="font-medium text-gray-900">{{ $item->product_name }}</td>
                        <td class="text-gray-500">{{ $item->sku }}</td>
                        <td>{{ $item->metal }}@if($item->purity) / {{ $item->purity }}@endif</td>
                        <td>{{ $item->size ?? '—' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₹{{ number_format($item->price, 2) }}</td>
                        <td class="text-right font-medium text-gray-900">₹{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </x-admin.table>

            {{-- Price Summary --}}
            <div class="admin-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Price Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="text-gray-900">₹{{ number_format($order->subtotal, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Discount</span><span class="text-gray-900">−₹{{ number_format($order->discount_amount, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Coupon Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</span><span class="text-gray-900">−₹{{ number_format($order->coupon_discount, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Shipping</span><span class="text-gray-900">₹{{ number_format($order->shipping_charge, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">GST</span><span class="text-gray-900">₹{{ number_format($order->gst_amount, 2) }}</span></div>
                    <div class="flex justify-between border-t border-gray-100 pt-2 text-base font-semibold"><span>Grand Total</span><span>₹{{ number_format($order->grand_total, 2) }}</span></div>
                    <div class="flex justify-between text-gray-500"><span>Paid Amount</span><span>₹{{ number_format($order->paid_amount, 2) }}</span></div>
                    @if ($order->refund_amount > 0)
                        <div class="flex justify-between text-error"><span>Refund Amount</span><span>₹{{ number_format($order->refund_amount, 2) }}</span></div>
                    @endif
                </div>
            </div>

            {{-- Payment --}}
            <div class="admin-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Payment</h3>
                    <div x-data="{ open: false }">
                        <button @click="open = true" class="text-sm font-medium text-champagne-dark hover:underline">Update Payment Status</button>
                        <x-admin.modal model="open" title="Update Payment Status" max-width="max-w-sm">
                            <form method="POST" action="{{ route('admin.orders.update-payment-status', $order) }}" class="space-y-4">
                                @csrf @method('PATCH')
                                <x-admin.form.select label="Payment Status" name="payment_status" :value="$order->payment_status" :options="collect(\App\Models\Order::PAYMENT_STATUSES)->mapWithKeys(fn($s) => [$s => ucwords(str_replace('_',' ',$s))])" :placeholder="null" />
                                <x-admin.form.input label="Paid Amount" name="paid_amount" type="number" step="0.01" :value="$order->paid_amount" />
                                <x-admin.form.input label="Refund Amount" name="refund_amount" type="number" step="0.01" :value="$order->refund_amount" />
                                <button type="submit" class="admin-btn-primary w-full">Save</button>
                            </form>
                        </x-admin.modal>
                    </div>
                </div>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm sm:grid-cols-3">
                    <div><dt class="text-gray-400">Method</dt><dd class="font-medium text-gray-800">{{ $order->payment_method ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400">Transaction ID</dt><dd class="font-medium text-gray-800">{{ $order->transaction_id ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400">Gateway</dt><dd class="font-medium text-gray-800">{{ $order->payment_gateway ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400">Payment Date</dt><dd class="font-medium text-gray-800">{{ $order->paid_at?->format('d M Y, h:i A') ?? '—' }}</dd></div>
                </dl>
            </div>

            {{-- Shipping --}}
            <div class="admin-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Shipping</h3>
                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @csrf @method('PUT')
                    <x-admin.form.input label="Courier Name" name="courier_name" :value="$order->courier_name" />
                    <x-admin.form.input label="Tracking Number" name="tracking_number" :value="$order->tracking_number" />
                    <x-admin.form.input label="Tracking URL" name="tracking_url" :value="$order->tracking_url" />
                    <x-admin.form.input label="Estimated Delivery" name="estimated_delivery" type="date" :value="$order->estimated_delivery?->format('Y-m-d')" />
                    <div class="sm:col-span-2"><button type="submit" class="admin-btn-secondary">Save Shipping Details</button></div>
                </form>
                @if ($order->delivered_at)
                    <p class="mt-3 text-sm text-gray-500">Delivered on {{ $order->delivered_at->format('d M Y, h:i A') }}</p>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            {{-- Update status --}}
            <div class="admin-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Update Order Status</h3>
                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="space-y-4">
                    @csrf @method('PATCH')
                    <x-admin.form.select name="status" :value="$order->status" :options="collect(\App\Models\Order::STATUSES)->mapWithKeys(fn($s) => [$s => ucwords(str_replace('_',' ',$s))])" :placeholder="null" />
                    <x-admin.form.textarea name="remark" placeholder="Add a remark (optional)" :rows="2" />
                    <button type="submit" class="admin-btn-primary w-full">Update Status</button>
                </form>
            </div>

            {{-- Timeline --}}
            <div class="admin-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Order Timeline</h3>
                <div class="space-y-4">
                    @forelse ($order->statusHistories as $history)
                        <div class="flex gap-3">
                            <div class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-champagne-dark"></div>
                            <div class="min-w-0 flex-1 pb-1">
                                <p class="text-sm font-medium text-gray-800">{{ ucwords(str_replace('_', ' ', $history->status)) }}</p>
                                @if ($history->remark)
                                    <p class="mt-0.5 text-xs text-gray-500">{{ $history->remark }}</p>
                                @endif
                                <p class="mt-0.5 text-xs text-gray-400">{{ $history->created_at->format('d M Y, h:i A') }} @if($history->updatedBy) &middot; {{ $history->updatedBy->name }} @endif</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No status history yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

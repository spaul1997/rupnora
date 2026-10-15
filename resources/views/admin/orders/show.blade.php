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

            @if ($order->gift_wrap)
                <div class="admin-card p-6">
                    <div class="flex items-center justify-between gap-4">
                        <h3 class="text-sm font-semibold text-gray-900">Gift Wrap</h3>
                        @if ($order->gift_message_category)
                            <span class="rounded-full bg-champagne-light px-2.5 py-1 text-xs font-medium text-champagne-dark">{{ $order->gift_message_category }}</span>
                        @endif
                    </div>
                    <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                        <div><dt class="text-gray-400">To</dt><dd class="font-medium text-gray-800">{{ $order->gift_to ?: '—' }}</dd></div>
                        <div><dt class="text-gray-400">From</dt><dd class="font-medium text-gray-800">{{ $order->gift_from ?: '—' }}</dd></div>
                    </dl>
                    @if ($order->gift_message)
                        <div class="mt-4 rounded-lg bg-gray-50 p-4">
                            <p class="whitespace-pre-line text-sm italic leading-relaxed text-gray-700">&ldquo;{{ $order->gift_message }}&rdquo;</p>
                        </div>
                    @endif
                </div>
            @endif

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

            @if ($order->affiliate_id && in_array($order->status, ['delivered', 'return_requested', 'returned', 'refunded']))
                <form method="POST" action="{{ route('admin.orders.update-returned-items', $order) }}" class="admin-card p-6">
                    @csrf @method('PATCH')
                    <h3 class="text-sm font-semibold text-gray-900">Partial return reconciliation</h3>
                    <p class="mt-1 text-xs text-gray-400">Enter returned quantities per item. The immutable ledger records only the additional reversal.</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($order->items as $item)
                            <label class="text-sm text-gray-700"><span class="mb-1 block truncate">{{ $item->product_name }} (max {{ $item->quantity }})</span><input type="number" name="returned_quantities[{{ $item->id }}]" min="{{ $item->returned_quantity }}" max="{{ $item->quantity }}" value="{{ $item->returned_quantity }}" class="admin-input w-full"></label>
                        @endforeach
                    </div>
                    <button class="admin-btn-secondary mt-4">Reconcile returned items</button>
                </form>
            @endif

            {{-- Price Summary --}}
            <div class="admin-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Price Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="text-gray-900">₹{{ number_format($order->subtotal, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Discount</span><span class="text-gray-900">−₹{{ number_format($order->discount_amount, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Coupon Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</span><span class="text-gray-900">−₹{{ number_format($order->coupon_discount, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Shipping</span><span class="text-gray-900">₹{{ number_format($order->shipping_charge, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">GST (included)</span><span class="text-gray-900">₹{{ number_format($order->gst_amount, 2) }}</span></div>
                    @if ($order->gift_wrap)
                        <div class="flex justify-between"><span class="text-gray-500">Gift Wrap</span><span class="text-gray-900">₹{{ number_format($order->gift_wrap_charge, 2) }}</span></div>
                    @endif
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

            @if (! in_array($order->status, ['processing', 'confirmed'], true))
                {{-- Shipping --}}
                <div class="admin-card p-6">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-gray-900">Shipping</h3>
                        @if (! $order->shiprocket_order_id && config('services.shiprocket.email') && config('services.shiprocket.password'))
                            <form method="POST" action="{{ route('admin.orders.shiprocket.store', $order) }}"
                                data-swal-confirm
                                data-confirm-title="Create Shiprocket order?"
                                data-confirm-message="This will send order {{ $order->order_number }} and its customer details to Shiprocket."
                                data-confirm-button="Create order">
                                @csrf
                                <button type="submit" class="admin-btn-primary">Send to Shiprocket</button>
                            </form>
                        @endif
                    </div>
                    @error('shiprocket')
                        <p class="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-error">{{ $message }}</p>
                    @enderror
                    @error('shiprocket_address')
                        <p class="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-error">{{ $message }}</p>
                    @enderror
                    @error('shiprocket_cancel')
                        <p class="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-error">{{ $message }}</p>
                    @enderror
                    @error('shiprocket_awb')
                        <p class="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-error">{{ $message }}</p>
                    @enderror
                    @error('shiprocket_tracking')
                        <p class="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-error">{{ $message }}</p>
                    @enderror
                    @error('courier_id')
                        <p class="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-error">{{ $message }}</p>
                    @enderror
                    @if ($order->shiprocket_order_id)
                        @php
                            $shippingAddress = $order->shipping_address ?? [];
                            $shippingAddressLineTwo = collect([
                                $shippingAddress['line2'] ?? null,
                                $shippingAddress['landmark'] ?? null,
                            ])->filter()->join(', ');
                            $shiprocketAddressFields = [
                                'shiprocket_address', 'shipping_customer_name', 'shipping_phone',
                                'shipping_address', 'shipping_address_2', 'shipping_city',
                                'shipping_state', 'shipping_country', 'shipping_pincode',
                            ];
                            $shiprocketCouriers = session('shiprocket_couriers', []);
                            $shiprocketTracking = is_array($order->shiprocket_tracking) ? $order->shiprocket_tracking : [];
                            $shiprocketTrackingActivities = collect($shiprocketTracking['activities'] ?? [])->take(5);
                            $shiprocketCourierOptions = collect($shiprocketCouriers)->mapWithKeys(function ($courier) {
                                $label = $courier['name'];
                                if ($courier['recommended']) $label .= ' (Recommended)';
                                if ($courier['freight_charge'] > 0) $label .= ' — Rs '.number_format($courier['freight_charge'], 2);
                                if ($courier['estimated_delivery_days'] !== '') $label .= ' — '.$courier['estimated_delivery_days'].' days';
                                elseif ($courier['etd'] !== '') $label .= ' — ETA '.$courier['etd'];

                                return [$courier['id'] => $label];
                            });
                        @endphp
                        <dl class="mb-4 grid grid-cols-1 gap-3 rounded-lg bg-gray-50 p-4 text-sm">
                            <div><dt class="text-gray-400">Shiprocket Order ID</dt><dd class="break-all font-medium text-gray-800">{{ $order->shiprocket_order_id }}</dd></div>
                            <div><dt class="text-gray-400">Shipment ID</dt><dd class="break-all font-medium text-gray-800">{{ $order->shiprocket_shipment_id }}</dd></div>
                            <div><dt class="text-gray-400">Status</dt><dd class="font-medium text-gray-800">{{ $order->shiprocket_status ?? 'Created' }}</dd></div>
                        </dl>
                        @if ($order->tracking_number)
                            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm">
                                <dl class="grid grid-cols-1 gap-3">
                                    <div><dt class="text-emerald-700/70">Courier</dt><dd class="font-semibold text-emerald-950">{{ $order->courier_name ?? 'Shiprocket Courier' }}</dd></div>
                                    <div><dt class="text-emerald-700/70">Tracking ID / AWB</dt><dd class="break-all font-semibold text-emerald-950">{{ $order->tracking_number }}</dd></div>
                                    <div><dt class="text-emerald-700/70">Current Status</dt><dd class="font-semibold text-emerald-950">{{ $shiprocketTracking['status'] ?? $order->shiprocket_status ?? 'AWB Assigned' }}</dd></div>
                                    @if ($order->shiprocket_tracked_at)
                                        <div><dt class="text-emerald-700/70">Last Refreshed</dt><dd class="text-emerald-950">{{ $order->shiprocket_tracked_at->format('d M Y, h:i A') }}</dd></div>
                                    @endif
                                    @if ($order->tracking_url)
                                        <div><a href="{{ $order->tracking_url }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-emerald-800 underline underline-offset-2">Open Courier Tracking Page</a></div>
                                    @endif
                                </dl>
                                @if ($shiprocketTrackingActivities->isNotEmpty())
                                    <div class="mt-4 space-y-3 border-t border-emerald-200 pt-3">
                                        @foreach ($shiprocketTrackingActivities as $activity)
                                            <div>
                                                <p class="font-medium text-emerald-950">{{ ($activity['activity'] ?? '') ?: ($activity['status'] ?? 'Shipment updated') }}</p>
                                                <p class="mt-0.5 text-xs text-emerald-800/75">
                                                    {{ ($activity['date'] ?? '') ?: 'Date unavailable' }}
                                                    @if ($activity['location'] ?? null) &middot; {{ $activity['location'] }} @endif
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @elseif ($order->status === 'shipped')
                            <div x-data="{ awbOpen: @js($shiprocketCourierOptions->isNotEmpty()) }" class="mb-4">
                                <form method="POST" action="{{ route('admin.orders.shiprocket.couriers', $order) }}"
                                    data-swal-confirm
                                    data-confirm-title="Check courier serviceability?"
                                    data-confirm-message="This will ask Shiprocket for couriers available between the pickup and delivery pincodes."
                                    data-confirm-button="Show couriers">
                                    @csrf
                                    <button type="submit" class="admin-btn-primary w-full !px-3 !py-2 !text-xs">Choose Courier &amp; Generate AWB</button>
                                </form>

                                @if ($shiprocketCourierOptions->isNotEmpty())
                                    <x-admin.modal model="awbOpen" title="Generate Shiprocket AWB" max-width="max-w-lg">
                                        <form method="POST" action="{{ route('admin.orders.shiprocket.awb', $order) }}" class="space-y-4"
                                            data-swal-confirm
                                            data-confirm-title="Generate AWB?"
                                            data-confirm-message="Shiprocket will assign the selected courier and create tracking for order {{ $order->order_number }}."
                                            data-confirm-button="Generate AWB">
                                            @csrf
                                            <p class="text-xs leading-relaxed text-gray-500">Only couriers currently serviceable for this pickup and delivery route are listed. The customer will receive the tracking ID and link by email after generation.</p>
                                            <x-admin.form.select label="Courier" name="courier_id" :options="$shiprocketCourierOptions" required />
                                            <button type="submit" class="admin-btn-primary w-full">Generate AWB &amp; Send Tracking Email</button>
                                        </form>
                                    </x-admin.modal>
                                @endif
                            </div>
                        @endif
                        <div x-data="{ addressOpen: {{ $errors->hasAny($shiprocketAddressFields) ? 'true' : 'false' }} }" class="flex gap-2">
                            @if ($order->tracking_number)
                                <form method="POST" action="{{ route('admin.orders.shiprocket.tracking.refresh', $order) }}" class="min-w-0 flex-1"
                                    data-swal-confirm
                                    data-confirm-title="View live tracking?"
                                    data-confirm-message="This will request the latest AWB status and scan history from Shiprocket."
                                    data-confirm-button="View tracking">
                                    @csrf
                                    <button type="submit" class="admin-btn-primary w-full !px-2 !py-1.5 !text-xs" title="View Shiprocket Tracking">View Tracking</button>
                                </form>
                            @endif
                            <button type="button" @click="addressOpen = true" class="admin-btn-secondary min-w-0 flex-1 !px-2 !py-1.5 !text-xs" title="Change Delivery Address">Change Address</button>
                            <x-admin.modal model="addressOpen" title="Change Delivery Address" max-width="max-w-lg">
                                <form method="POST" action="{{ route('admin.orders.shiprocket.address.update', $order) }}" class="space-y-4"
                                    data-swal-confirm
                                    data-confirm-title="Update delivery address?"
                                    data-confirm-message="This will replace the delivery address for this order in Shiprocket."
                                    data-confirm-button="Update address">
                                    @csrf @method('PATCH')
                                    <p class="text-xs leading-relaxed text-gray-500">This updates the delivery address for this order in Shiprocket and in the local order snapshot.</p>
                                    <x-admin.form.input label="Customer Name" name="shipping_customer_name" :value="$shippingAddress['name'] ?? $order->customer_name" required maxlength="100" />
                                    <x-admin.form.input label="Phone" name="shipping_phone" type="tel" :value="$shippingAddress['phone'] ?? $order->customer_phone" required maxlength="30" inputmode="numeric" />
                                    <x-admin.form.input label="Address" name="shipping_address" :value="$shippingAddress['line1'] ?? null" required maxlength="180" />
                                    <x-admin.form.input label="Address Line 2 / Landmark" name="shipping_address_2" :value="$shippingAddressLineTwo" maxlength="180" />
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <x-admin.form.input label="City" name="shipping_city" :value="$shippingAddress['city'] ?? null" required maxlength="100" />
                                        <x-admin.form.input label="State" name="shipping_state" :value="$shippingAddress['state'] ?? null" required maxlength="100" />
                                        <x-admin.form.input label="Country" name="shipping_country" :value="$shippingAddress['country'] ?? 'India'" required readonly />
                                        <x-admin.form.input label="Pincode" name="shipping_pincode" :value="$shippingAddress['pincode'] ?? null" required maxlength="6" inputmode="numeric" />
                                    </div>
                                    <button type="submit" class="admin-btn-primary w-full">Update Delivery Address</button>
                                </form>
                            </x-admin.modal>
                            @if (! in_array($order->status, ['cancelled', 'delivered', 'returned', 'refunded'], true))
                                <form method="POST" action="{{ route('admin.orders.shiprocket.cancel', $order) }}" class="min-w-0 flex-1"
                                    data-swal-confirm
                                    data-confirm-title="Cancel Shiprocket order?"
                                    data-confirm-message="This will cancel Shiprocket order {{ $order->shiprocket_order_id }} and mark the local order as cancelled. This action cannot be undone."
                                    data-confirm-button="Yes, cancel order">
                                    @csrf
                                    <button type="submit" class="admin-btn-danger w-full !px-2 !py-1.5 !text-xs" title="Cancel Shiprocket Order">Cancel Order</button>
                                </form>
                            @endif
                        </div>
                    @endif
                    @if ($order->delivered_at)
                        <p class="text-sm text-gray-500">Delivered on {{ $order->delivered_at->format('d M Y, h:i A') }}</p>
                    @endif
                </div>
            @endif

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

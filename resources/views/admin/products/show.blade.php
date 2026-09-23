@extends('admin.layouts.app')

@section('title', $product->name)

@section('content')
    @php
        $chartHistory = $priceHistory->sortBy(fn ($history) => $history->recorded_at->format('Y-m-d H:i:s').str_pad((string) $history->id, 12, '0', STR_PAD_LEFT))->values();
        $priceSeries = [
            ['label' => 'MRP', 'colour' => '#9ca3af', 'values' => $chartHistory->map(fn ($history) => (float) $history->mrp)],
            ['label' => 'Selling Price', 'colour' => '#8b63fb', 'values' => $chartHistory->map(fn ($history) => (float) $history->selling_price)],
            ['label' => 'Final Price', 'colour' => '#47a545', 'values' => $chartHistory->map(fn ($history) => (float) $history->final_price)],
        ];
        $allChartPrices = collect($priceSeries)->flatMap(fn ($series) => $series['values']);
        $lowestChartPrice = (float) ($allChartPrices->min() ?? 0);
        $highestChartPrice = (float) ($allChartPrices->max() ?? 0);
        $chartPadding = max(($highestChartPrice - $lowestChartPrice) * 0.1, $highestChartPrice * 0.02, 1);
        $chartMinimum = max(0, $lowestChartPrice - $chartPadding);
        $chartMaximum = $highestChartPrice + $chartPadding;
        $chartRange = max($chartMaximum - $chartMinimum, 1);
        $chartLeft = 76;
        $chartTop = 18;
        $chartWidth = 694;
        $chartHeight = 182;
        $chartX = fn (int $index) => $chartLeft + ($chartHistory->count() > 1 ? ($index / ($chartHistory->count() - 1)) * $chartWidth : $chartWidth / 2);
        $chartY = fn (float $price) => $chartTop + (($chartMaximum - $price) / $chartRange) * $chartHeight;
        $gridLines = collect(range(0, 4))->map(fn ($step) => [
            'y' => $chartTop + ($step / 4) * $chartHeight,
            'value' => $chartMaximum - ($step / 4) * $chartRange,
        ]);

        foreach ($priceSeries as &$series) {
            $series['points'] = $series['values']->map(fn ($price, $index) => round($chartX($index), 2).','.round($chartY((float) $price), 2))->implode(' ');
        }
        unset($series);

        $finalPrices = $priceHistory->pluck('final_price')->map(fn ($price) => (float) $price);
    @endphp

    <x-admin.page-header :title="$product->name" :breadcrumb="[['label' => 'Products', 'url' => route('admin.products.index')], ['label' => $product->name]]">
        <x-slot:actions>
            <a href="{{ route('admin.products.edit', $product) }}" class="admin-btn-primary">Edit Product</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="admin-card p-6">
                <div class="flex flex-wrap items-center gap-2">
                    <x-admin.status-badge :status="$product->is_active ? 'active' : 'inactive'" />
                    <x-admin.status-badge :status="$product->stock_status" />
                    @if ($product->is_featured)<span class="admin-badge bg-champagne-light/40 text-champagne-dark">Featured</span>@endif
                    @if ($product->is_new_arrival)<span class="admin-badge bg-blue-100 text-blue-700">New Arrival</span>@endif
                    @if ($product->is_best_seller)<span class="admin-badge bg-purple-100 text-purple-700">Best Seller</span>@endif
                </div>
                <p class="mt-4 text-sm text-gray-500">SKU: <span class="font-medium text-gray-800">{{ $product->sku }}</span> &middot; Category: <span class="font-medium text-gray-800">{{ $product->category?->name }}</span></p>
                <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $product->short_description }}</p>

                <div class="mt-5 grid grid-cols-2 gap-4 border-t border-gray-100 pt-5 sm:grid-cols-4">
                    <div><p class="text-xs text-gray-400">MRP</p><p class="font-semibold text-gray-900">₹{{ number_format($product->mrp) }}</p></div>
                    <div><p class="text-xs text-gray-400">Selling Price</p><p class="font-semibold text-gray-900">₹{{ number_format($product->selling_price) }}</p></div>
                    <div><p class="text-xs text-gray-400">Final Price</p><p class="font-semibold text-gray-900">₹{{ number_format($product->final_price) }}</p></div>
                    <div><p class="text-xs text-gray-400">Stock</p><p class="font-semibold text-gray-900">{{ $product->stock_quantity }}</p></div>
                </div>
            </div>

            @if ($product->offer_price !== null || $product->discount_type)
                <div class="admin-card p-6">
                    <h3 class="mb-4 text-sm font-semibold text-gray-900">Offers &amp; Discounts</h3>
                    <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                        @if ($product->offer_price !== null)
                            <div>
                                <dt class="text-gray-400">Offer Price</dt>
                                <dd class="font-medium text-gray-800">₹{{ number_format($product->offer_price, 2) }} &middot; {{ $product->offerHasExpired() ? 'Expired' : 'Active' }}</dd>
                                <dd class="mt-1 text-xs text-gray-500">{{ $product->offer_expiry_date ? 'Valid through '.$product->offer_expiry_date->format('d M Y') : 'No expiry' }}</dd>
                            </div>
                        @endif
                        @if ($product->discount_type)
                            <div>
                                <dt class="text-gray-400">Discount</dt>
                                <dd class="font-medium text-gray-800">{{ $product->discount_type === 'percentage' ? $product->discount_value.'%' : '₹'.number_format($product->discount_value, 2) }} &middot; {{ $product->hasActiveDiscount() ? 'Active' : ($product->discountHasExpired() ? 'Expired' : 'Inactive') }}</dd>
                                <dd class="mt-1 text-xs text-gray-500">{{ $product->discount_expiry_date ? 'Valid through '.$product->discount_expiry_date->format('d M Y') : 'No expiry' }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif

            @if ($priceHistory->isNotEmpty())
                <div id="price-history" class="admin-card p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Price History</h3>
                            <p class="mt-1 text-xs text-gray-500">Automatic snapshots of every product-level price change.@if ($priceHistoryTotal > 100) The latest 100 of {{ number_format($priceHistoryTotal) }} records are shown.@endif</p>
                        </div>
                        <a href="{{ route('admin.products.edit', $product) }}#pricing" class="admin-btn-secondary">Manage Price</a>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-lg bg-gray-50 p-3">
                            <p class="text-xs text-gray-400">Current Final</p>
                            <p class="mt-1 font-semibold text-gray-900">₹{{ number_format((float) $priceHistory->first()->final_price, 2) }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3">
                            <p class="text-xs text-gray-400">Lowest Final</p>
                            <p class="mt-1 font-semibold text-success">₹{{ number_format((float) $finalPrices->min(), 2) }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3">
                            <p class="text-xs text-gray-400">Highest Final</p>
                            <p class="mt-1 font-semibold text-gray-900">₹{{ number_format((float) $finalPrices->max(), 2) }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3">
                            <p class="text-xs text-gray-400">Recorded Changes</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ max(0, $priceHistoryTotal - 1) }}</p>
                        </div>
                    </div>

                    <div class="mt-5 overflow-x-auto rounded-xl border border-gray-100 bg-white p-3">
                        <svg viewBox="0 0 800 245" class="h-auto min-w-[640px] w-full" role="img" aria-label="MRP, selling price and final price history chart">
                            @foreach ($gridLines as $line)
                                <line x1="{{ $chartLeft }}" y1="{{ $line['y'] }}" x2="{{ $chartLeft + $chartWidth }}" y2="{{ $line['y'] }}" stroke="#e5e7eb" stroke-width="1" />
                                <text x="{{ $chartLeft - 9 }}" y="{{ $line['y'] + 4 }}" text-anchor="end" font-size="10" fill="#9ca3af">₹{{ number_format($line['value'], 0) }}</text>
                            @endforeach

                            @foreach ($priceSeries as $series)
                                <polyline points="{{ $series['points'] }}" fill="none" stroke="{{ $series['colour'] }}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                                @foreach ($series['values'] as $index => $price)
                                    <circle cx="{{ $chartX($index) }}" cy="{{ $chartY((float) $price) }}" r="{{ $series['label'] === 'Final Price' ? 4 : 3 }}" fill="{{ $series['colour'] }}" stroke="#ffffff" stroke-width="2">
                                        <title>{{ $chartHistory[$index]->recorded_at->format('d M Y, h:i A') }} — {{ $series['label'] }} ₹{{ number_format((float) $price, 2) }}</title>
                                    </circle>
                                @endforeach
                            @endforeach

                            <text x="{{ $chartLeft }}" y="228" font-size="10" fill="#9ca3af">{{ $chartHistory->first()->recorded_at->format('d M Y') }}</text>
                            @if ($chartHistory->count() > 1)
                                <text x="{{ $chartLeft + $chartWidth }}" y="228" text-anchor="end" font-size="10" fill="#9ca3af">{{ $chartHistory->last()->recorded_at->format('d M Y') }}</text>
                            @endif
                        </svg>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-500">
                        @foreach ($priceSeries as $series)
                            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $series['colour'] }}"></span>{{ $series['label'] }}</span>
                        @endforeach
                    </div>

                    <div class="mt-5 overflow-x-auto rounded-xl border border-gray-100">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Recorded</th>
                                    <th class="px-4 py-3">Movement</th>
                                    <th class="px-4 py-3 text-right">MRP</th>
                                    <th class="px-4 py-3 text-right">Selling</th>
                                    <th class="px-4 py-3 text-right">Offer</th>
                                    <th class="px-4 py-3 text-right">Final</th>
                                    <th class="px-4 py-3">Changed By / Note</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($priceHistory as $history)
                                    @php($difference = $history->previous_final_price === null ? null : (float) $history->final_price - (float) $history->previous_final_price)
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-500">{{ $history->recorded_at->format('d M Y, h:i A') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $history->change_type === 'increase' ? 'bg-amber-100 text-amber-700' : ($history->change_type === 'decrease' ? 'bg-success/10 text-success' : 'bg-gray-100 text-gray-600') }}">
                                                {{ ucfirst($history->change_type) }}
                                                @if ($difference !== null && abs($difference) >= 0.01)
                                                    {{ $difference > 0 ? '+' : '−' }}₹{{ number_format(abs($difference), 2) }}
                                                @endif
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700">₹{{ number_format((float) $history->mrp, 2) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700">₹{{ number_format((float) $history->selling_price, 2) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700">{{ $history->offer_price === null ? '—' : '₹'.number_format((float) $history->offer_price, 2) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-gray-900">₹{{ number_format((float) $history->final_price, 2) }}</td>
                                        <td class="min-w-64 px-4 py-3">
                                            <p class="font-medium text-gray-700">{{ $history->changedBy?->name ?? ucfirst($history->source) }}</p>
                                            @if ($history->note)<p class="mt-0.5 text-xs text-gray-500">{{ $history->note }}</p>@endif
                                            @if ($history->changed_fields)
                                                <p class="mt-0.5 text-xs text-gray-400">{{ collect($history->changed_fields)->map(fn ($field) => Str::headline($field))->join(', ') }}</p>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($product->images->count() > 0)
                <div class="admin-card p-6">
                    <h3 class="mb-4 text-sm font-semibold text-gray-900">Images</h3>
                    <div class="grid grid-cols-4 gap-3 sm:grid-cols-6">
                        @foreach ($product->images as $image)
                            <x-ui.optimized-image :src="asset('storage/'.$image->image_path)" alt="" sizes="160px" class="aspect-square rounded-lg border {{ $image->is_primary ? 'border-champagne-dark' : 'border-gray-200' }} object-cover" />
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="admin-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Jewellery Details</h3>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm sm:grid-cols-3">
                    <div><dt class="text-gray-400">Jewellery Type</dt><dd class="font-medium text-gray-800">{{ $product->jewellery_type }}</dd></div>
                    <div><dt class="text-gray-400">Material</dt><dd class="font-medium text-gray-800">{{ $product->metal_type }}</dd></div>
                    <div><dt class="text-gray-400">Finish / Plating</dt><dd class="font-medium text-gray-800">{{ $product->finish_plating ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400">Colour</dt><dd class="font-medium text-gray-800">{{ $product->metal_colour ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400">Stone Type</dt><dd class="font-medium text-gray-800">{{ $product->gemstone_type ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400">Stone Colour</dt><dd class="font-medium text-gray-800">{{ $product->gemstone_colour ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400">Occasion</dt><dd class="font-medium text-gray-800">{{ $product->occasion ? \App\Models\Product::OCCASIONS[$product->occasion] ?? ucfirst($product->occasion) : '—' }}</dd></div>
                    <div><dt class="text-gray-400">Gender</dt><dd class="font-medium text-gray-800">{{ $product->gender ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400">Gross Weight</dt><dd class="font-medium text-gray-800">{{ $product->gross_weight ?? '—' }} g</dd></div>
                    <div><dt class="text-gray-400">Adjustable</dt><dd class="font-medium text-gray-800">{{ $product->is_adjustable ? 'Yes' : 'No' }}</dd></div>
                    <div><dt class="text-gray-400">Water Resistant</dt><dd class="font-medium text-gray-800">{{ $product->is_water_resistant ? 'Yes' : 'No' }}</dd></div>
                    <div><dt class="text-gray-400">Return</dt><dd class="font-medium text-gray-800">{{ $product->is_return_available ? 'Available' : 'Not Available' }}</dd></div>
                    <div><dt class="text-gray-400">Refund</dt><dd class="font-medium text-gray-800">{{ $product->is_refund_available ? 'Available' : 'Not Available' }}</dd></div>
                    @if ($product->has_diamond)
                        <div><dt class="text-gray-400">Diamond Carat</dt><dd class="font-medium text-gray-800">{{ $product->diamond_carat }} ct</dd></div>
                        <div><dt class="text-gray-400">Diamond Clarity</dt><dd class="font-medium text-gray-800">{{ $product->diamond_clarity }}</dd></div>
                        <div><dt class="text-gray-400">Diamond Shape</dt><dd class="font-medium text-gray-800">{{ $product->diamond_shape }}</dd></div>
                    @endif
                </dl>
            </div>

            @if ($product->variants->count() > 0)
                <x-admin.table :headers="['Size', 'Metal', 'Purity', 'Colour', 'Price', 'Stock', 'Status']">
                    @foreach ($product->variants as $variant)
                        <tr>
                            <td>{{ $variant->size ?? '—' }}</td>
                            <td>{{ $variant->metal ?? '—' }}</td>
                            <td>{{ $variant->purity ?? '—' }}</td>
                            <td>{{ $variant->colour ?? '—' }}</td>
                            <td>₹{{ number_format($variant->price ?? $product->selling_price) }}</td>
                            <td>{{ $variant->stock_quantity }}</td>
                            <td><x-admin.status-badge :status="$variant->status" /></td>
                        </tr>
                    @endforeach
                </x-admin.table>
            @endif

            @if ($product->reviews->count() > 0)
                <div class="admin-card p-6">
                    <h3 class="mb-4 text-sm font-semibold text-gray-900">Recent Reviews</h3>
                    <div class="space-y-4">
                        @foreach ($product->reviews->take(5) as $review)
                            <div class="border-b border-gray-100 pb-3 last:border-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-800">{{ $review->customer?->name }}</p>
                                    <x-admin.status-badge :status="$review->status" />
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ Str::limit($review->review, 120) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="admin-card p-6">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.products.edit', $product) }}" class="admin-btn-secondary w-full">Edit Product</a>
                    <form method="POST" action="{{ route('admin.products.duplicate', $product) }}">
                        @csrf
                        <button type="submit" class="admin-btn-secondary w-full">Duplicate Product</button>
                    </form>
                    <a href="{{ route('admin.inventory.history', $product) }}" class="admin-btn-secondary w-full">Stock History</a>
                    <a href="#price-history" class="admin-btn-secondary w-full">Price History</a>
                    <x-admin.confirm-modal :action="route('admin.products.destroy', $product)" title="Delete Product" :message="'Are you sure you want to delete \''.$product->name.'\'? This action cannot be undone.'" trigger-class="admin-btn-danger w-full" trigger-label="Delete Product" />
                </div>
            </div>
        </div>
    </div>
@endsection

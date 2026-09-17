@extends('admin.layouts.app')

@section('title', $product->name)

@section('content')
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
                    <x-admin.confirm-modal :action="route('admin.products.destroy', $product)" title="Delete Product" :message="'Are you sure you want to delete \''.$product->name.'\'? This action cannot be undone.'" trigger-class="admin-btn-danger w-full" trigger-label="Delete Product" />
                </div>
            </div>
        </div>
    </div>
@endsection

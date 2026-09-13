@extends('admin.layouts.app')

@section('title', 'Products')

@section('content')
    <x-admin.page-header title="Products" description="Manage your jewellery catalogue.">
        <x-slot:actions>
            <a href="{{ route('admin.products.export') }}" class="admin-btn-secondary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                Export CSV
            </a>
            <a href="{{ route('admin.products.create') }}" class="admin-btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                Add Product
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div x-data="{ filtersOpen: false }" class="mb-5">
        <div class="flex items-center gap-3 lg:hidden">
            <button @click="filtersOpen = true" class="admin-btn-secondary flex-1 justify-center">Filters</button>
        </div>

        <form method="GET" class="admin-card hidden flex-wrap items-center gap-3 p-4 lg:flex">
            @include('admin.products._filters')
        </form>

        <div x-cloak x-show="filtersOpen" x-transition.opacity class="fixed inset-0 z-[90] bg-gray-900/50 lg:hidden" @click.self="filtersOpen = false"></div>
        <div x-cloak x-show="filtersOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" class="fixed inset-x-0 bottom-0 z-[95] max-h-[85vh] overflow-y-auto rounded-t-2xl bg-white p-5 lg:hidden">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">Filters</h3>
                <button @click="filtersOpen = false" class="text-gray-400"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg></button>
            </div>
            <form method="GET" class="flex flex-col gap-3">
                @include('admin.products._filters')
            </form>
        </div>
    </div>

    @if ($products->isEmpty())
        <x-admin.empty-state title="No products found" description="Try adjusting your filters, or add your first product." icon="M20.4 14.5L16 10m0 0l4.4-4.5M16 10H3">
            <x-slot:actions>
                <a href="{{ route('admin.products.create') }}" class="admin-btn-primary">Add Product</a>
            </x-slot:actions>
        </x-admin.empty-state>
    @else
        <x-admin.table :headers="['Product', 'SKU', 'Category', 'Metal / Purity', 'Price', 'Stock', 'Status', 'Featured', 'Created', '!Actions']">
            @foreach ($products as $product)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            @php($primary = $product->images->firstWhere('is_primary', true) ?? $product->images->first())
                            @if ($primary ?? false)
                                <x-ui.optimized-image :src="asset('storage/'.$primary->image_path)" alt="" sizes="40px" class="h-10 w-10 rounded-lg object-cover" />
                            @else
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-beige text-champagne-dark">
                                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="8" /></svg>
                                </span>
                            @endif
                            <a href="{{ route('admin.products.show', $product) }}" class="max-w-[180px] truncate font-medium text-gray-900 hover:text-champagne-dark">{{ $product->name }}</a>
                        </div>
                    </td>
                    <td class="text-gray-500">{{ $product->sku }}</td>
                    <td>{{ $product->category?->name }}</td>
                    <td>{{ $product->metal_type }}@if($product->purity) &middot; {{ $product->purity }}@endif</td>
                    <td class="font-medium text-gray-900">₹{{ number_format($product->selling_price) }}</td>
                    <td>
                        <span class="{{ $product->stock_status === 'out_of_stock' ? 'text-error' : ($product->stock_status === 'low_stock' ? 'text-amber-600' : 'text-gray-700') }}">{{ $product->stock_quantity }}</span>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.products.toggle-active', $product) }}">
                            @csrf @method('PATCH')
                            <button type="submit"><x-admin.status-badge :status="$product->is_active ? 'active' : 'inactive'" /></button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.products.toggle-featured', $product) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="{{ $product->is_featured ? 'text-champagne-dark' : 'text-gray-300' }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.6 5.6 6.1.6-4.6 4.2 1.3 6.1L12 15l-5.4 3 1.3-6.1L3.3 8.2l6.1-.6L12 2z" /></svg>
                            </button>
                        </form>
                    </td>
                    <td class="text-gray-400">{{ $product->created_at->format('d M Y') }}</td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.products.show', $product) }}" class="text-sm font-medium text-gray-500 hover:text-gray-800">View</a>
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-sm font-medium text-champagne-dark hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.products.duplicate', $product) }}">
                                @csrf
                                <button type="submit" class="text-sm font-medium text-gray-500 hover:text-gray-800">Duplicate</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$products" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

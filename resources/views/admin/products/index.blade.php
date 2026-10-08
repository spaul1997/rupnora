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

        <form method="GET" class="admin-card hidden flex-wrap items-center gap-x-3 gap-y-2 p-3 lg:flex">
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
        <x-admin.table
            table-class="table-fixed"
            :headers="['Product', 'Category', 'Type', 'Price', 'Stock', 'Status', 'Created', '!Actions']"
            :column-classes="['w-[260px] xl:w-[340px]', '', '', '', '', '', '', 'w-[100px]']"
        >
            @foreach ($products as $product)
                <tr>
                    <td class="w-[260px] max-w-[260px] overflow-hidden xl:w-[340px] xl:max-w-[340px]">
                        <div class="flex min-w-0 items-center gap-3">
                            @php($primary = $product->images->firstWhere('is_primary', true) ?? $product->images->first())
                            <div class="h-14 w-14 flex-none overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                                @if ($primary ?? false)
                                    <x-ui.optimized-image :src="asset('storage/'.$primary->image_path)" :alt="$product->name" sizes="56px" class="h-14 w-14 object-cover" />
                                @else
                                    <span class="flex h-full w-full items-center justify-center bg-beige text-champagne-dark">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="8" /></svg>
                                    </span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.products.show', $product) }}" class="block truncate font-medium text-gray-900 hover:text-champagne-dark" title="{{ $product->name }}">{{ $product->name }}</a>
                                <span class="mt-1 block truncate text-xs text-gray-400" title="{{ $product->sku }}">{{ $product->sku }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        @php($category = $product->category)
                        @php($parentCategory = $category?->parent)
                        <span class="block whitespace-nowrap font-medium text-gray-800">{{ $parentCategory?->name ?? $category?->name ?? '—' }}</span>
                        @if ($parentCategory)
                            <span class="mt-0.5 block whitespace-nowrap text-xs text-gray-400">{{ $category->name }}</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap">{{ $product->jewellery_type ?: '—' }}</td>
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
                    <td class="whitespace-nowrap text-gray-400">{{ $product->created_at->format('d-m-y') }}</td>
                    <td class="text-right">
                        <div
                            x-data="{
                                open: false,
                                menuStyle: '',
                                toggleMenu() {
                                    if (this.open) {
                                        this.open = false;
                                        return;
                                    }

                                    const trigger = this.$refs.trigger.getBoundingClientRect();
                                    const width = 176;
                                    const height = 140;
                                    const gap = 6;
                                    const left = Math.min(window.innerWidth - width - 8, Math.max(8, trigger.right - width));
                                    const top = trigger.bottom + height + gap > window.innerHeight && trigger.top > height
                                        ? trigger.top - height - gap
                                        : trigger.bottom + gap;

                                    this.menuStyle = `top: ${top}px; left: ${left}px; width: ${width}px;`;
                                    this.open = true;
                                }
                            }"
                            @keydown.escape.window="open = false"
                            @resize.window="open = false"
                            class="flex items-center justify-end gap-1.5"
                        >
                            <form method="POST" action="{{ route('admin.products.toggle-featured', $product) }}">
                                @csrf @method('PATCH')
                                <button
                                    type="submit"
                                    class="rounded-lg p-2 transition-colors hover:bg-gray-100 {{ $product->is_featured ? 'text-champagne-dark' : 'text-gray-300 hover:text-gray-500' }}"
                                    aria-label="{{ $product->is_featured ? 'Remove from featured products' : 'Add to featured products' }}"
                                    title="{{ $product->is_featured ? 'Featured' : 'Mark as featured' }}"
                                >
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.6 5.6 6.1.6-4.6 4.2 1.3 6.1L12 15l-5.4 3 1.3-6.1L3.3 8.2l6.1-.6L12 2z" /></svg>
                                </button>
                            </form>

                            <button
                                x-ref="trigger"
                                type="button"
                                @click="toggleMenu()"
                                :aria-expanded="open"
                                aria-haspopup="menu"
                                aria-label="Open product actions"
                                class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-800"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <circle cx="5" cy="12" r="1.8" />
                                    <circle cx="12" cy="12" r="1.8" />
                                    <circle cx="19" cy="12" r="1.8" />
                                </svg>
                            </button>

                            <template x-teleport="body">
                                <div
                                    x-cloak
                                    x-show="open"
                                    @click.outside="open = false"
                                    x-transition.opacity.duration.100ms
                                    :style="menuStyle"
                                    role="menu"
                                    class="fixed z-[100] overflow-hidden rounded-xl border border-gray-200 bg-white p-1.5 text-left shadow-xl"
                                >
                                    <a href="{{ route('admin.products.show', $product) }}" role="menuitem" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900">
                                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" /></svg>
                                        View product
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" role="menuitem" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900">
                                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m4 16-.8 4 4-.8L18.5 7.9l-3.2-3.2L4 16Z" /><path d="m13.8 6.2 3.2 3.2" /></svg>
                                        Edit product
                                    </a>
                                    <form method="POST" action="{{ route('admin.products.duplicate', $product) }}">
                                        @csrf
                                        <button type="submit" role="menuitem" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900">
                                            <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="8" y="8" width="11" height="11" rx="2" /><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2" /></svg>
                                            Duplicate
                                        </button>
                                    </form>
                                </div>
                            </template>
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

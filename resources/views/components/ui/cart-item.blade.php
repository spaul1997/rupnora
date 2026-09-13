@props(['item'])

@php($product = $item['product'])

<div class="flex gap-4 py-6 sm:gap-5">
    <a href="{{ route('product.show', $product['id']) }}" class="flex-shrink-0">
        @if (! empty($product['image']))
            <x-ui.optimized-image :src="$product['image']" :alt="$product['name']" sizes="112px" class="h-24 w-24 rounded-xl object-cover sm:h-28 sm:w-28" />
        @else
            <x-ui.product-art :art="$product['art']" class="h-24 w-24 rounded-xl sm:h-28 sm:w-28" />
        @endif
    </a>
    <div class="flex min-w-0 flex-1 flex-col">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-wider text-muted">{{ $product['category_name'] ?? ucfirst(str_replace('-', ' ', $product['category'])) }}</p>
                <a href="{{ route('product.show', $product['id']) }}" class="mt-0.5 block truncate font-display text-[16px] text-charcoal hover:text-champagne-dark">{{ $product['name'] }}</a>
                <p class="mt-1 text-xs text-muted">
                    {{ $product['metal'] }}@if($product['purity']) &middot; {{ $product['purity'] }} @endif
                    @if ($item['size']) &middot; Size: {{ $item['size'] }} @endif
                </p>
            </div>
            <button type="button" class="icon-btn flex-shrink-0 text-muted hover:text-error" aria-label="Remove item">
                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-9 0h10l-1 13H8L7 7z" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </button>
        </div>

        <div class="mt-auto flex flex-wrap items-end justify-between gap-3 pt-3">
            <div class="flex items-center gap-3">
                <div x-data="{ qty: {{ $item['qty'] }} }">
                    <x-ui.quantity-selector model="qty" :max="5" />
                </div>
                <button type="button" class="text-xs font-medium text-muted underline decoration-line underline-offset-2 hover:text-champagne-dark">Move to Wishlist</button>
            </div>
            <x-ui.price :price="$product['price'] * $item['qty']" :mrp="$product['mrp'] * $item['qty']" size="sm" />
        </div>
    </div>
</div>

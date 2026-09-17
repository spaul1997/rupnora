@props([
    'item',
    'index' => 0,
])

@php
    $product = $item['product'];
    $productUrlKey = $product['slug'] ?? $product['id'];
    $maxQty = $item['max_qty'] ?? min(5, max(1, (int) ($product['stock_quantity'] ?? 5)));
    $hasDiscount = $product['mrp'] > $product['price'];
    $discountPercent = $hasDiscount ? round((($product['mrp'] - $product['price']) / $product['mrp']) * 100) : null;
@endphp

<div x-show="isItemVisible({{ $index }})" x-transition class="flex gap-4 py-6 sm:gap-5">
    <a href="{{ route('product.show', $productUrlKey) }}" class="flex-shrink-0">
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
                <a href="{{ route('product.show', $productUrlKey) }}" class="mt-0.5 block truncate font-display text-[16px] text-charcoal hover:text-champagne-dark">{{ $product['name'] }}</a>
                <p class="mt-1 text-xs text-muted">
                    {{ $product['metal'] }}@if($product['purity']) &middot; {{ $product['purity'] }} @endif
                    @if ($item['size']) &middot; Size: {{ $item['size'] }} @endif
                </p>
            </div>
            <button type="button" @click="removeItem({{ $index }})" :disabled="items[{{ $index }}]?.syncing" class="icon-btn flex-shrink-0 text-muted hover:text-error disabled:opacity-40" aria-label="Remove item">
                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-9 0h10l-1 13H8L7 7z" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </button>
        </div>

        <div class="mt-auto flex flex-wrap items-end justify-between gap-3 pt-3">
            <div class="flex items-center gap-3">
                <div>
                    <x-ui.quantity-selector model="items[{{ $index }}].qty" :max="$maxQty" after-change="updateItem({{ $index }})" disabled-when="items[{{ $index }}].syncing" />
                </div>
                <button type="button" @click="moveToWishlist({{ $index }})" :disabled="items[{{ $index }}]?.syncing" class="text-xs font-medium text-muted underline decoration-line underline-offset-2 hover:text-champagne-dark disabled:opacity-40">Move to Wishlist</button>
            </div>
            <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                <span class="font-semibold text-charcoal text-[15px]" x-text="formatMoney(itemLinePrice({{ $index }}))">₹{{ number_format($product['price'] * $item['qty']) }}</span>
                <span x-show="items[{{ $index }}].mrp > items[{{ $index }}].price" @if (! $hasDiscount) x-cloak @endif class="text-sm text-muted-light line-through" x-text="formatMoney(itemLineMrp({{ $index }}))">₹{{ number_format($product['mrp'] * $item['qty']) }}</span>
                <span x-show="items[{{ $index }}].mrp > items[{{ $index }}].price" @if (! $hasDiscount) x-cloak @endif class="text-sm font-semibold text-success" x-text="Math.round((1 - items[{{ $index }}].price / items[{{ $index }}].mrp) * 100) + '% OFF'">{{ $discountPercent }}% OFF</span>
            </div>
        </div>
    </div>
</div>

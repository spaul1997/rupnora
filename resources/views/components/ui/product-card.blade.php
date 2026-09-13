@props([
    'product',
])

@php
    $categoryLabel = $product['category_name'] ?? ucfirst(str_replace('-', ' ', $product['category']));
@endphp

<div class="group relative" x-data="{ quickView: false }">
    <a href="{{ route('product.show', $product['id']) }}" class="block">
        <div class="relative overflow-hidden rounded-lg border border-line">
            <div class="relative aspect-square">
                @if (! empty($product['image']))
                    <x-ui.optimized-image :src="$product['image']" :alt="$product['name']" sizes="(min-width: 1024px) 20vw, (min-width: 640px) 33vw, 50vw" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                @else
                    <div class="absolute inset-0 transition-opacity duration-500 group-hover:opacity-0">
                        <x-ui.product-art :art="$product['art']" :tone="1" class="aspect-auto h-full" />
                    </div>
                    <div class="absolute inset-0 opacity-0 transition-opacity duration-500 group-hover:opacity-100">
                        <x-ui.product-art :art="$product['art']" :tone="2" class="aspect-auto h-full" />
                    </div>
                @endif

                @if (! ($product['in_stock'] ?? true))
                    <div class="absolute inset-0 flex items-center justify-center bg-charcoal/40 backdrop-blur-[1px]">
                        <span class="badge-luxe !px-2 !py-0.5 !text-[9px] bg-paper text-charcoal">Out of Stock</span>
                    </div>
                @endif
            </div>

            <div class="absolute left-2 top-2 flex flex-col gap-1">
                @foreach ($product['badges'] ?? [] as $badge)
                    <x-ui.badge class="!px-2 !py-0.5 !text-[9px]" :tone="$badge === 'Limited' ? 'limited' : ($badge === 'New' ? 'charcoal' : 'champagne')">{{ $badge }}</x-ui.badge>
                @endforeach
            </div>

            <div class="absolute right-2 top-2">
                <x-ui.wishlist-button :id="$product['id']" size="sm" />
            </div>

            <button
                type="button"
                @click.stop.prevent="quickView = true"
                class="absolute inset-x-2 bottom-2 translate-y-2 rounded-full bg-paper/95 py-1.5 text-center text-[9.5px] font-semibold uppercase tracking-wide text-charcoal opacity-0 shadow-card backdrop-blur transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100 hidden sm:block"
            >
                Quick View
            </button>
        </div>
    </a>

    <div class="mt-2 space-y-0.5">
        <p class="text-[9.5px] uppercase tracking-wide text-muted">{{ $categoryLabel }}</p>
        <a href="{{ route('product.show', $product['id']) }}" class="block font-display text-[12.5px] leading-tight text-charcoal hover:text-champagne-dark transition-colors line-clamp-2">
            {{ $product['name'] }}
        </a>
        <x-ui.rating :value="$product['rating']" :count="$product['reviews_count']" size="xs" />
        <div class="pt-0.5"><x-ui.price :price="$product['price']" :mrp="$product['mrp']" size="xs" /></div>
    </div>

    <button
        type="button"
        @click.stop.prevent="$store.ui.addToCart({{ Illuminate\Support\Js::from($product['name']) }})"
        class="mt-2 w-full rounded-full border border-charcoal/80 py-1.5 text-[9.5px] font-semibold uppercase tracking-wide text-charcoal transition-all duration-300 hover:bg-charcoal hover:text-ivory sm:hidden"
    >
        Add to Cart
    </button>

    {{-- Quick View modal --}}
    <div x-cloak x-show="quickView" x-transition.opacity class="fixed inset-0 z-50 flex items-end justify-center bg-charcoal/50 p-0 sm:items-center sm:p-6" @click.self="quickView = false" @keydown.window.escape="quickView = false">
        <div x-show="quickView" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="w-full max-w-3xl rounded-t-2xl sm:rounded-2xl bg-paper p-5 sm:p-8 max-h-[90vh] overflow-y-auto">
            <div class="mb-4 flex items-center justify-between sm:hidden">
                <span class="text-sm font-semibold uppercase tracking-wide text-muted">Quick View</span>
                <button @click="quickView = false" class="icon-btn"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg></button>
            </div>
            <div class="grid gap-6 sm:grid-cols-2">
                @if (! empty($product['image']))
                    <x-ui.optimized-image :src="$product['image']" :alt="$product['name']" sizes="(min-width: 640px) 384px, 100vw" class="aspect-square rounded-xl object-cover" />
                @else
                    <x-ui.product-art :art="$product['art']" class="aspect-square rounded-xl" />
                @endif
                <div>
                    <button @click="quickView = false" class="icon-btn absolute right-6 top-6 hidden sm:inline-flex"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg></button>
                    <p class="eyebrow">{{ $categoryLabel }}</p>
                    <h3 class="mt-1 font-display text-2xl text-charcoal">{{ $product['name'] }}</h3>
                    <div class="mt-2"><x-ui.rating :value="$product['rating']" :count="$product['reviews_count']" /></div>
                    <div class="mt-3"><x-ui.price :price="$product['price']" :mrp="$product['mrp']" size="lg" /></div>
                    <p class="mt-4 text-sm leading-relaxed text-muted">{{ $product['short_desc'] }}</p>
                    <div class="mt-5 flex flex-wrap gap-2 text-xs text-muted">
                        <span class="rounded-full border border-line px-3 py-1">{{ $product['metal'] }}@if($product['purity']) &middot; {{ $product['purity'] }}@endif</span>
                        @if($product['diamond'])
                            <span class="rounded-full border border-line px-3 py-1">{{ $product['diamond']['carat'] }}</span>
                        @endif
                    </div>
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('product.show', $product['id']) }}" class="btn-secondary flex-1">View Full Details</a>
                        <button @click.stop.prevent="$store.ui.addToCart({{ Illuminate\Support\Js::from($product['name']) }})" class="btn-primary flex-1">Add to Cart</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

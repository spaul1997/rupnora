@props([
    'product',
])

@php
    $categoryLabel = $product['category_name'] ?? ucfirst(str_replace('-', ' ', $product['category']));
    $productUrlKey = $product['slug'] ?? $product['id'];
    $primaryImage = $product['primary_image'] ?? $product['image'] ?? null;
    $cartPayload = [
        'id' => $product['id'],
        'name' => $product['name'],
        'url' => route('cart.store'),
        'inStock' => (bool) ($product['in_stock'] ?? false),
    ];
    $isInStock = (bool) ($product['in_stock'] ?? false);
    $quickViewId = 'quick-view-'.\Illuminate\Support\Str::random(8);
    $quickViewImages = collect([$primaryImage, ...($product['gallery'] ?? [])])->filter()->unique()->take(5)->values()->all();
    $warrantyMonths = (int) ($product['warranty_months'] ?? 0);
    $quickViewSpecs = array_filter([
        'Metal' => trim(($product['metal'] ?? '').(filled($product['purity'] ?? null) ? ' · '.$product['purity'] : '')),
        'Diamond' => $product['diamond']['carat'] ?? null,
        'Gemstone' => $product['stone_type'] ?? null,
        'Warranty' => $warrantyMonths > 0 ? $warrantyMonths.' '.\Illuminate\Support\Str::plural('month', $warrantyMonths) : null,
    ], 'filled');
@endphp

<div
    class="group relative"
    data-motion-card="product"
    x-data="{ quickView: false, activeImage: 0 }"
    x-init="$watch('quickView', (open) => document.body.classList.toggle('overflow-hidden', open))"
>
    <a href="{{ route('product.show', $productUrlKey) }}" class="block">
        <div class="relative overflow-hidden rounded-lg border border-line">
            <div class="relative aspect-square">
                @if ($primaryImage)
                    <x-ui.optimized-image :src="$primaryImage" :alt="$product['name']" sizes="(min-width: 1024px) 20vw, (min-width: 640px) 33vw, 50vw" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                @else
                    <div class="absolute inset-0 transition-opacity duration-500 group-hover:opacity-0">
                        <x-ui.product-art :art="$product['art']" :tone="1" class="aspect-auto h-full" />
                    </div>
                    <div class="absolute inset-0 opacity-0 transition-opacity duration-500 group-hover:opacity-100">
                        <x-ui.product-art :art="$product['art']" :tone="2" class="aspect-auto h-full" />
                    </div>
                @endif

                @if (! $isInStock)
                    <div class="absolute inset-0 flex items-center justify-center bg-charcoal/40 backdrop-blur-[1px]">
                        <span class="badge-luxe !px-2 !py-0.5 !text-[9px] bg-paper text-charcoal">Out of Stock</span>
                    </div>
                @endif
            </div>

            <div class="absolute left-1.5 top-1.5 flex flex-col items-start gap-0.5">
                @foreach ($product['badges'] ?? [] as $badge)
                    <x-ui.badge
                        class="!h-4 !gap-0 !rounded-md !px-1.5 !py-0 !text-[7.5px] !leading-none !tracking-[0.06em] shadow-sm ring-1 ring-white/20"
                        :tone="$badge === 'Limited' ? 'limited' : ($badge === 'New' ? 'charcoal' : 'champagne')"
                    >{{ $badge }}</x-ui.badge>
                @endforeach
            </div>

            <div class="absolute right-2 top-2">
                <x-ui.wishlist-button :id="$productUrlKey" size="sm" />
            </div>

            <button
                type="button"
                @click.stop.prevent="activeImage = 0; quickView = true"
                class="absolute inset-x-2 bottom-2 translate-y-2 rounded-full bg-paper/95 py-1.5 text-center text-[9.5px] font-semibold uppercase tracking-wide text-charcoal opacity-0 shadow-card backdrop-blur transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100 hidden sm:block"
            >
                Quick View
            </button>
        </div>
    </a>

    <div class="mt-2 space-y-0.5">
        <p class="text-[9.5px] uppercase tracking-wide text-muted">{{ $categoryLabel }}</p>
        <a href="{{ route('product.show', $productUrlKey) }}" class="block min-h-[2.5em] font-display text-[12.5px] leading-tight text-charcoal hover:text-champagne-dark transition-colors line-clamp-2 sm:min-h-0 sm:line-clamp-1">
            {{ $product['name'] }}
        </a>
        <x-ui.rating :value="$product['rating']" :rating-count="$product['ratings_count']" :count="$product['reviews_count']" size="xs" />
        <div class="pt-0.5"><x-ui.price :price="$product['price']" :mrp="$product['mrp']" size="xs" /></div>
    </div>

    <button
        type="button"
        data-card-cart-action
        @click.stop.prevent="$store.ui.addToCart({{ Illuminate\Support\Js::from($cartPayload) }})"
        @disabled(! $isInStock)
        class="mt-2 inline-flex w-full items-center justify-center gap-1.5 rounded-full border border-charcoal/80 py-2 text-[9.5px] font-semibold uppercase tracking-wide text-charcoal transition-all duration-300 hover:-translate-y-0.5 hover:bg-charcoal hover:text-ivory hover:shadow-soft active:translate-y-0 disabled:cursor-not-allowed disabled:border-line disabled:bg-gray-100 disabled:text-muted disabled:opacity-70 disabled:hover:translate-y-0 disabled:hover:shadow-none"
    >
        @if ($isInStock)
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M3 7h13l1.5 12h-16zM8 7V5.5a3 3 0 016 0V7" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        @endif
        <span>{{ $isInStock ? 'Add to Cart' : 'Out of Stock' }}</span>
    </button>

    {{-- Quick View modal: teleported to <body> so the card's motion transform can't trap the fixed overlay --}}
    <template x-teleport="body">
        <div
            x-cloak
            x-show="quickView"
            x-transition.opacity.duration.250ms
            class="fixed inset-0 z-[90] flex items-end justify-center bg-charcoal/60 backdrop-blur-sm sm:items-center sm:p-6"
            @click.self="quickView = false"
            @keydown.window.escape="quickView = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $quickViewId }}-title"
        >
            <div
                x-show="quickView"
                x-transition:enter="transition duration-300 ease-out"
                x-transition:enter-start="translate-y-full sm:translate-y-4 sm:scale-[0.97] sm:opacity-0"
                x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
                x-transition:leave="transition duration-200 ease-in"
                x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
                x-transition:leave-end="translate-y-full sm:translate-y-4 sm:scale-[0.97] sm:opacity-0"
                class="relative flex max-h-[92vh] w-full max-w-lg flex-col overflow-hidden rounded-t-3xl bg-paper shadow-lift sm:max-h-[88vh] sm:rounded-3xl md:max-w-4xl"
            >
                <button
                    type="button"
                    @click="quickView = false"
                    x-effect="quickView && $nextTick(() => $el.focus({ preventScroll: true }))"
                    class="icon-btn absolute right-3 top-3 z-10 bg-paper/90 shadow-card backdrop-blur md:right-4 md:top-4"
                    aria-label="Close quick view"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round" /></svg>
                </button>

                <div class="flex min-h-0 flex-1 flex-col overflow-y-auto overscroll-contain md:flex-row md:overflow-hidden">
                    {{-- Media --}}
                    <div class="shrink-0 bg-ivory md:w-[46%] md:overflow-y-auto">
                        <div class="relative aspect-[4/3] overflow-hidden md:aspect-square">
                            @forelse ($quickViewImages as $i => $image)
                                <div x-show="activeImage === {{ $i }}" class="absolute inset-0">
                                    <x-ui.optimized-image :src="$image" :alt="$product['name']" sizes="(min-width: 768px) 420px, 512px" class="h-full w-full object-cover" />
                                </div>
                            @empty
                                <div class="absolute inset-0">
                                    <x-ui.product-art :art="$product['art']" class="aspect-auto h-full" />
                                </div>
                            @endforelse

                            @if (! empty($product['badges']))
                                <div class="absolute left-3 top-3 flex flex-col items-start gap-1 md:left-4 md:top-4">
                                    @foreach ($product['badges'] as $badge)
                                        <x-ui.badge class="shadow-sm" :tone="$badge === 'Limited' ? 'limited' : ($badge === 'New' ? 'charcoal' : 'champagne')">{{ $badge }}</x-ui.badge>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @if (count($quickViewImages) > 1)
                            <div class="flex gap-2 overflow-x-auto px-4 py-3 md:px-5 md:py-4">
                                @foreach ($quickViewImages as $i => $image)
                                    <button
                                        type="button"
                                        @click="activeImage = {{ $i }}"
                                        :aria-pressed="activeImage === {{ $i }}"
                                        :class="activeImage === {{ $i }} ? 'border-champagne-dark' : 'border-transparent opacity-70 hover:opacity-100'"
                                        class="h-14 w-14 flex-none overflow-hidden rounded-lg border-2 bg-paper transition"
                                        aria-label="Show image {{ $i + 1 }}"
                                    >
                                        <x-ui.optimized-image :src="$image" alt="" sizes="56px" class="h-full w-full object-cover" />
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Details --}}
                    <div class="flex min-w-0 flex-1 flex-col md:overflow-y-auto">
                        <div class="flex-1 px-5 pb-6 pt-5 sm:px-7 md:px-8 md:pt-8">
                            <p class="eyebrow !text-[11px]">{{ $categoryLabel }}</p>
                            <h2 id="{{ $quickViewId }}-title" class="mt-2 font-display text-[22px] leading-snug text-charcoal md:pr-10 md:text-[26px]">{{ $product['name'] }}</h2>
                            <div class="mt-2.5"><x-ui.rating :value="$product['rating']" :rating-count="$product['ratings_count']" :count="$product['reviews_count']" /></div>

                            <div class="mt-5 border-y border-line py-4">
                                <x-ui.price :price="$product['price']" :mrp="$product['mrp']" size="lg" />
                                <p class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold {{ $isInStock ? 'text-success' : 'text-error' }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                    {{ $isInStock ? 'In stock' : 'Out of stock' }}
                                </p>
                            </div>

                            @if (filled($product['short_desc'] ?? null))
                                <p class="mt-4 text-sm leading-relaxed text-charcoal-soft">{{ $product['short_desc'] }}</p>
                            @endif

                            @if ($quickViewSpecs)
                                <dl class="mt-5 grid grid-cols-2 gap-2">
                                    @foreach ($quickViewSpecs as $label => $value)
                                        <div class="rounded-xl bg-ivory px-3 py-2.5">
                                            <dt class="text-[10px] font-semibold uppercase tracking-[0.16em] text-muted">{{ $label }}</dt>
                                            <dd class="mt-0.5 text-[13px] font-medium text-charcoal">{{ $value }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @endif
                        </div>

                        <div class="sticky bottom-0 border-t border-line bg-paper/95 px-5 pt-4 pb-[max(1rem,env(safe-area-inset-bottom))] backdrop-blur sm:px-7 sm:pb-5 md:px-8">
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    @click.stop.prevent="$store.ui.addToCart({{ Illuminate\Support\Js::from($cartPayload) }}).then((added) => added && (quickView = false))"
                                    @disabled(! $isInStock)
                                    class="btn-primary !h-12 flex-1 whitespace-nowrap !px-4 !py-0 !text-[11.5px]"
                                >
                                    {{ $isInStock ? 'Add to Cart' : 'Out of Stock' }}
                                </button>
                                <x-ui.wishlist-button :id="$productUrlKey" class="!h-12 !w-12 shrink-0 border border-line !shadow-none" />
                            </div>
                            <a href="{{ route('product.show', $productUrlKey) }}" class="mt-3 flex items-center justify-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-charcoal-soft transition-colors hover:text-champagne-dark">
                                View full details
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

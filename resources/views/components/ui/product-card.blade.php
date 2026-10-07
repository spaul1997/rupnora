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
    x-data="{
        quickView: false,
        activeImage: 0,
        imageCount: {{ count($quickViewImages) }},
        touchX: 0,
        cartState: 'idle',
        stepImage(step) {
            if (this.imageCount > 1) this.activeImage = (this.activeImage + step + this.imageCount) % this.imageCount;
        },
        addToCart() {
            if (this.cartState !== 'idle') return Promise.resolve(false);
            this.cartState = 'busy';
            return this.$store.ui.addToCart({{ Illuminate\Support\Js::from($cartPayload) }}).then((added) => {
                this.cartState = added ? 'added' : 'idle';
                if (added) setTimeout(() => this.cartState = 'idle', 1800);
                return added;
            });
        },
    }"
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

    <div class="mt-1.5 space-y-0.5">
        <p class="text-[9.5px] uppercase tracking-wide text-muted">{{ $categoryLabel }}</p>
        <a href="{{ route('product.show', $productUrlKey) }}" class="block min-h-[2.5em] font-display text-[12.5px] leading-tight text-charcoal hover:text-champagne-dark transition-colors line-clamp-2 sm:min-h-0 sm:line-clamp-1">
            {{ $product['name'] }}
        </a>
        <x-ui.rating :value="$product['rating']" :rating-count="$product['ratings_count']" :count="$product['reviews_count']" size="xs" />
        <div class="flex items-center justify-between gap-1.5">
            <div class="min-w-0"><x-ui.price :price="$product['price']" :mrp="$product['mrp']" size="xs" /></div>

            {{-- Icon-only add to cart: disabled attr must stay before the click handler (OutOfStockPurchaseTest regex) --}}
            <button
                type="button"
                data-card-cart-action
                @disabled(! $isInStock)
                @click.stop.prevent="addToCart()"
                :aria-busy="cartState === 'busy'"
                :class="{ '!bg-none !bg-success !shadow-none': cartState === 'added', 'cursor-wait': cartState === 'busy' }"
                aria-label="{{ $isInStock ? 'Add '.$product['name'].' to cart' : 'Out of stock' }}"
                title="{{ $isInStock ? 'Add to cart' : 'Out of stock' }}"
                class="-my-1.5 inline-flex h-9 w-9 shrink-0 items-center sm:-my-1 sm:h-8 sm:w-8 justify-center rounded-full transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-champagne focus-visible:ring-offset-2 {{ $isInStock ? 'bg-[linear-gradient(135deg,var(--color-champagne),var(--color-champagne-dark))] text-white shadow-[0_4px_12px_-3px_color-mix(in_srgb,var(--color-champagne)_65%,transparent)] hover:-translate-y-0.5 hover:bg-[linear-gradient(135deg,var(--color-champagne-dark),var(--color-charcoal-soft))] hover:shadow-[0_6px_16px_-4px_color-mix(in_srgb,var(--color-champagne-dark)_75%,transparent)] active:scale-90' : 'cursor-not-allowed border border-line bg-gray-100 text-muted' }}"
            >
                <svg x-show="cartState === 'idle'" class="h-[18px] w-[18px] sm:h-4 sm:w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M2.5 3.5h2.2l2.4 11.1a1.6 1.6 0 001.6 1.3h8.4a1.6 1.6 0 001.6-1.2L20.8 7H5.6" />
                    <circle cx="9.5" cy="20" r="1.2" fill="currentColor" />
                    <circle cx="17" cy="20" r="1.2" fill="currentColor" />
                    @if ($isInStock)
                        <path d="M13 9v4.6M10.7 11.3h4.6" />
                    @endif
                </svg>
                <svg x-cloak x-show="cartState === 'busy'" class="h-4 w-4 animate-spin sm:h-3.5 sm:w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.3" stroke-width="3" />
                    <path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                </svg>
                <svg x-cloak x-show="cartState === 'added'" class="h-[18px] w-[18px] sm:h-4 sm:w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12.5l4.5 4.5L19 7.5" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Quick View modal: teleported to <body> so the card's motion transform can't trap the fixed overlay --}}
    <template x-teleport="body">
        <div
            x-cloak
            x-show="quickView"
            x-transition.opacity.duration.250ms
            class="fixed inset-0 z-[90] flex items-end justify-center bg-charcoal/55 backdrop-blur-sm sm:items-center sm:p-6"
            @click.self="quickView = false"
            @keydown.window.escape="quickView = false"
            @keydown.window.arrow-right="quickView && stepImage(1)"
            @keydown.window.arrow-left="quickView && stepImage(-1)"
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
                class="relative flex max-h-[90vh] w-full flex-col overflow-hidden rounded-t-2xl bg-paper shadow-lift sm:max-h-[85vh] sm:max-w-md sm:rounded-2xl md:max-w-3xl"
            >
                <span class="pointer-events-none absolute left-1/2 top-2 z-10 h-1 w-10 -translate-x-1/2 rounded-full bg-paper/85 shadow-sm sm:hidden" aria-hidden="true"></span>

                <button
                    type="button"
                    @click="quickView = false"
                    x-effect="quickView && $nextTick(() => $el.focus({ preventScroll: true }))"
                    class="absolute right-2.5 top-2.5 z-10 inline-flex h-8 w-8 items-center justify-center rounded-full bg-paper/90 text-charcoal shadow-card backdrop-blur transition hover:scale-105 hover:bg-paper focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-champagne md:right-3 md:top-3"
                    aria-label="Close quick view"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round" /></svg>
                </button>

                <div class="flex min-h-0 flex-1 flex-col overflow-y-auto overscroll-contain md:flex-row md:overflow-hidden">
                    {{-- Media: fills the column height on desktop so there's no empty gap under the image; swipe/arrow keys switch images --}}
                    <div
                        class="relative shrink-0 bg-ivory md:min-h-[420px] md:w-[44%]"
                        @touchstart.passive="touchX = $event.touches[0].clientX"
                        @touchend="Math.abs($event.changedTouches[0].clientX - touchX) > 40 && stepImage($event.changedTouches[0].clientX < touchX ? 1 : -1)"
                    >
                        <div class="relative aspect-[4/3] w-full overflow-hidden md:absolute md:inset-0 md:aspect-auto">
                            @forelse ($quickViewImages as $i => $image)
                                <div x-show="activeImage === {{ $i }}" x-transition.opacity.duration.200ms class="absolute inset-0">
                                    <x-ui.optimized-image :src="$image" :alt="$product['name']" sizes="(min-width: 768px) 340px, 448px" class="h-full w-full object-cover" />
                                </div>
                            @empty
                                <div class="absolute inset-0">
                                    <x-ui.product-art :art="$product['art']" class="aspect-auto h-full" />
                                </div>
                            @endforelse

                            @if (! empty($product['badges']))
                                <div class="absolute left-2.5 top-2.5 flex flex-col items-start gap-1 md:left-3 md:top-3">
                                    @foreach ($product['badges'] as $badge)
                                        <x-ui.badge
                                            class="!h-5 !gap-0 !rounded-md !px-2 !py-0 !text-[9px] !leading-none !tracking-[0.06em] shadow-sm ring-1 ring-white/20"
                                            :tone="$badge === 'Limited' ? 'limited' : ($badge === 'New' ? 'charcoal' : 'champagne')"
                                        >{{ $badge }}</x-ui.badge>
                                    @endforeach
                                </div>
                            @endif

                            @if (count($quickViewImages) > 1)
                                <div class="absolute inset-x-0 bottom-0 flex justify-center gap-1.5 bg-gradient-to-t from-charcoal/35 to-transparent px-3 pb-2.5 pt-8">
                                    @foreach ($quickViewImages as $i => $image)
                                        <button
                                            type="button"
                                            @click="activeImage = {{ $i }}"
                                            :aria-pressed="activeImage === {{ $i }}"
                                            :class="activeImage === {{ $i }} ? 'border-paper shadow-card' : 'border-transparent opacity-70 hover:opacity-100'"
                                            class="h-10 w-10 flex-none overflow-hidden rounded-md border-2 bg-paper transition"
                                            aria-label="Show image {{ $i + 1 }}"
                                        >
                                            <x-ui.optimized-image :src="$image" alt="" sizes="40px" class="h-full w-full object-cover" />
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Details --}}
                    <div class="flex min-w-0 flex-1 flex-col md:overflow-y-auto">
                        <div class="flex-1 px-4 pb-4 pt-4 sm:px-5 md:px-6 md:pt-5">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-champagne-dark">{{ $categoryLabel }}</p>
                            <h2 id="{{ $quickViewId }}-title" class="mt-1 line-clamp-2 font-display text-[17px] leading-snug text-charcoal md:pr-8 md:text-xl">{{ $product['name'] }}</h2>
                            <div class="mt-1.5"><x-ui.rating :value="$product['rating']" :rating-count="$product['ratings_count']" :count="$product['reviews_count']" /></div>

                            <div class="mt-3 flex flex-wrap items-center gap-x-2.5 gap-y-1.5 border-t border-line pt-3">
                                <x-ui.price :price="$product['price']" :mrp="$product['mrp']" />
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10.5px] font-semibold {{ $isInStock ? 'bg-success/10 text-success' : 'bg-error/10 text-error' }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                    {{ $isInStock ? 'In stock' : 'Out of stock' }}
                                </span>
                            </div>

                            @if (filled($product['short_desc'] ?? null))
                                <p class="mt-2.5 line-clamp-3 text-[13px] leading-relaxed text-charcoal-soft">{{ $product['short_desc'] }}</p>
                            @endif

                            @if ($quickViewSpecs)
                                <dl class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach ($quickViewSpecs as $label => $value)
                                        <div class="inline-flex items-center gap-1 rounded-full bg-ivory px-2.5 py-1 text-[11px]">
                                            <dt class="text-muted">{{ $label }}:</dt>
                                            <dd class="font-medium text-charcoal">{{ $value }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @endif

                            <a href="{{ route('product.show', $productUrlKey) }}" class="mt-3 inline-flex items-center gap-1 text-[11.5px] font-semibold text-champagne-dark transition-colors hover:text-charcoal">
                                View full details
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </a>
                        </div>

                        <div class="sticky bottom-0 flex gap-2 border-t border-line bg-paper/95 px-4 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] backdrop-blur sm:px-5 md:px-6 md:pb-4">
                            <button
                                type="button"
                                @click.stop.prevent="addToCart().then((added) => added && (quickView = false))"
                                @disabled(! $isInStock)
                                :aria-busy="cartState === 'busy'"
                                class="btn-primary !h-12 flex-1 !gap-2 whitespace-nowrap !px-4 !py-0 !text-[12px] !tracking-[0.12em] sm:!h-11 sm:!text-[11px]"
                            >
                                @if ($isInStock)
                                    <svg x-show="cartState !== 'busy'" class="h-[18px] w-[18px] sm:h-4 sm:w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M2.5 3.5h2.2l2.4 11.1a1.6 1.6 0 001.6 1.3h8.4a1.6 1.6 0 001.6-1.2L20.8 7H5.6" />
                                        <circle cx="9.5" cy="20" r="1.2" fill="currentColor" />
                                        <circle cx="17" cy="20" r="1.2" fill="currentColor" />
                                        <path d="M13 9v4.6M10.7 11.3h4.6" />
                                    </svg>
                                    <svg x-cloak x-show="cartState === 'busy'" class="h-[18px] w-[18px] animate-spin sm:h-4 sm:w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.3" stroke-width="3" />
                                        <path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                                    </svg>
                                @endif
                                <span>{{ $isInStock ? 'Add to Cart' : 'Out of Stock' }}</span>
                            </button>
                            <x-ui.wishlist-button :id="$productUrlKey" class="!h-12 !w-12 shrink-0 border border-line !shadow-none sm:!h-11 sm:!w-11" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

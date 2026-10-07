<x-layouts.app :title="$title ?? null">

    {{-- Home offer popup: stays dismissed for 24 hours in this browser. --}}
    <div
        x-data="{
            open: false,
            storageKey: 'rupnora-home-offer-dismissed-at',
            dismissalPeriod: 24 * 60 * 60 * 1000,
            showDelay: 1200,
            init() {
                let dismissedAt = 0;

                try {
                    dismissedAt = Number(window.localStorage.getItem(this.storageKey) || 0);
                } catch (error) {}

                if (!dismissedAt || Date.now() - dismissedAt >= this.dismissalPeriod) {
                    {{-- Short delay so the hero is seen before the offer interrupts. --}}
                    setTimeout(() => {
                        this.open = true;
                        this.$nextTick(() => this.$refs.closeButton?.focus());
                    }, this.showDelay);
                }
            },
            dismiss() {
                try {
                    window.localStorage.setItem(this.storageKey, String(Date.now()));
                } catch (error) {}

                this.open = false;
            },
        }"
        x-effect="document.body.classList.toggle('overflow-hidden', open)"
        @keydown.escape.window="if (open) dismiss()"
    >
        <div
            x-cloak
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click.self="dismiss()"
            class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto bg-charcoal/75 p-4 backdrop-blur-sm sm:p-6"
        >
            <section
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-[0.98]"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-[0.98]"
                role="dialog"
                aria-modal="true"
                aria-labelledby="home-offer-title"
                aria-describedby="home-offer-desc"
                class="relative grid max-h-[calc(100vh-2rem)] w-full max-w-3xl overflow-y-auto rounded-[1.75rem] bg-paper shadow-2xl sm:grid-cols-[0.9fr_1.1fr] sm:overflow-hidden"
            >
                <button
                    x-ref="closeButton"
                    type="button"
                    @click="dismiss()"
                    aria-label="Close offer"
                    class="absolute right-3 top-3 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-paper/90 text-charcoal shadow-sm transition hover:rotate-90 hover:bg-paper hover:text-champagne-dark focus:outline-none focus:ring-2 focus:ring-champagne-dark focus:ring-offset-2"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg>
                </button>

                <div class="relative h-48 overflow-hidden sm:h-auto sm:min-h-[430px]">
                    <x-ui.optimized-image
                        :src="asset('images/her.png')"
                        alt="Woman wearing Rupnora jewellery"
                        sizes="(min-width: 640px) 340px, 100vw"
                        loading="eager"
                        class="h-full w-full object-cover object-[center_32%]"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-charcoal/60 via-transparent to-transparent sm:bg-gradient-to-r sm:from-transparent sm:to-charcoal/10"></div>
                    <span class="absolute bottom-4 left-4 rounded-full border border-ivory/30 bg-charcoal/45 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-ivory backdrop-blur-sm sm:bottom-6 sm:left-6">Limited Time</span>
                </div>

                <div class="flex flex-col justify-center px-6 py-7 text-center sm:px-10 sm:py-10 sm:text-left">
                    <span class="eyebrow">The Sparkle Sale</span>
                    <h2 id="home-offer-title" class="font-display mt-2 text-3xl leading-tight text-charcoal sm:text-4xl">A little luxury,<br class="hidden sm:block"> for a lot less.</h2>
                    <div class="mt-5 flex items-baseline justify-center gap-2 sm:justify-start">
                        <span class="font-display text-5xl font-semibold leading-none text-champagne-dark sm:text-6xl">10–60%</span>
                        <span class="text-sm font-semibold uppercase tracking-[0.18em] text-charcoal">Off</span>
                    </div>
                    <p id="home-offer-desc" class="mt-3 text-sm leading-relaxed text-muted">Save on selected jewellery styles while the offer lasts.</p>
                    <a href="{{ route('best-sellers') }}" @click="dismiss()" class="btn-primary mt-6 w-full sm:w-auto">Shop the Offer</a>
                    <button type="button" @click="dismiss()" class="mt-3 py-1 text-xs font-medium text-muted underline-offset-4 hover:text-charcoal hover:underline">No thanks, maybe later</button>
                    <p class="mt-4 text-[10px] uppercase tracking-[0.12em] text-muted-light">Selected styles only. Terms apply.</p>
                </div>
            </section>
        </div>
    </div>

    {{-- A. Hero Banner --}}
    <x-ui.hero-slider :slides="$heroSlides" />

    {{-- C. Featured Collections --}}
    <section class="section-pad-sm bg-ivory-soft" aria-labelledby="home-collections-title">
        <div class="container-luxe">
            <div class="mb-6 text-center sm:mb-8">
                <span class="eyebrow">Curated For You</span>
                <h2 id="home-collections-title" class="font-display mt-2 text-2xl text-charcoal sm:text-3xl lg:text-4xl">Featured Collections</h2>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-muted">Thoughtfully curated edits, from everyday essentials to once-in-a-lifetime pieces.</p>
            </div>
            <div class="grid grid-cols-3 gap-2.5 sm:grid-cols-4 sm:gap-4 lg:grid-cols-5">
                @foreach ($collections as $collection)
                    <x-ui.collection-card :collection="$collection" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- B. Shop By Category --}}
    <section class="section-pad-sm" aria-labelledby="home-categories-title">
        <div class="container-luxe">
            <div class="mb-6 flex items-end justify-between gap-4 sm:mb-8">
                <div>
                    <span class="eyebrow">Explore</span>
                    <h2 id="home-categories-title" class="font-display mt-2 text-2xl text-charcoal sm:text-3xl lg:text-4xl">Shop by Category</h2>
                </div>
                <a href="{{ route('categories.index') }}" class="group/link inline-flex flex-shrink-0 items-center gap-1.5 text-sm font-medium text-charcoal">
                    <span class="link-underline group-hover/link:after:w-full">View All</span>
                    <svg class="h-3.5 w-3.5 transition-transform duration-300 group-hover/link:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </a>
            </div>
            <div class="-mx-5 snap-x snap-mandatory overflow-x-auto scroll-px-5 px-5 pb-1 [scrollbar-width:none] sm:mx-0 sm:scroll-px-0 sm:px-0 [&::-webkit-scrollbar]:hidden">
                <div class="*:snap-start" style="display: grid; min-width: {{ max(count($parentCategories), 1) * 132 }}px; grid-template-columns: repeat({{ max(count($parentCategories), 1) }}, minmax(0, 1fr)); gap: 0.625rem;">
                    @foreach ($parentCategories as $cat)
                        <x-ui.category-card :category="$cat" />
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- D. New Arrivals --}}
    @php
        // Trim to full rows on the 3-column tablet grid so no single card is left orphaned.
        $newArrivalsTabletCount = count($newArrivals) >= 3 ? intdiv(count($newArrivals), 3) * 3 : count($newArrivals);
    @endphp
    <section class="section-pad" aria-labelledby="home-new-arrivals-title">
        <div class="container-luxe">
            <div class="mb-8 flex items-end justify-between gap-4 sm:mb-10">
                <div>
                    <span class="eyebrow">Just In</span>
                    <h2 id="home-new-arrivals-title" class="font-display mt-2 text-2xl text-charcoal sm:text-3xl lg:text-4xl">New Arrivals</h2>
                </div>
                <a href="{{ route('new-arrivals') }}" class="group/link hidden flex-shrink-0 items-center gap-1.5 text-sm font-medium text-charcoal sm:inline-flex">
                    <span class="link-underline group-hover/link:after:w-full">View All</span>
                    <svg class="h-3.5 w-3.5 transition-transform duration-300 group-hover/link:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </a>
            </div>
            <div class="-mr-5 flex snap-x snap-mandatory gap-3 overflow-x-auto pr-5 pb-2 [scrollbar-width:none] sm:mr-0 sm:grid sm:grid-cols-3 sm:gap-4 sm:overflow-visible sm:pr-0 sm:pb-0 lg:grid-cols-5 [&::-webkit-scrollbar]:hidden">
                @foreach ($newArrivals as $product)
                    <div @class([
                        'w-[72%] flex-shrink-0 snap-start sm:w-auto',
                        'sm:max-lg:hidden' => $loop->iteration > $newArrivalsTabletCount,
                    ])>
                        <x-ui.product-card :product="$product" />
                    </div>
                @endforeach
            </div>
            <a href="{{ route('new-arrivals') }}" class="btn-secondary mt-8 flex w-full justify-center sm:hidden">View All New Arrivals</a>
        </div>
    </section>

    {{-- E. Shop by Jewellery Type --}}
    <section class="section-pad bg-charcoal" aria-labelledby="home-jewellery-type-title">
        <div class="container-luxe">
            <div class="mb-8 text-center sm:mb-10">
                <span class="eyebrow text-champagne-light">By Metal &amp; Stone</span>
                <h2 id="home-jewellery-type-title" class="font-display mt-2 text-2xl text-ivory sm:text-3xl lg:text-4xl">Shop by Jewellery Type</h2>
            </div>
            @php
                $typeIcons = ['ring', 'necklace', 'earring', 'bangle', 'diamond'];
            @endphp
            {{-- Flex-wrap + centering keeps an odd last card centred instead of hanging left. --}}
            <div class="flex flex-wrap justify-center gap-3 sm:gap-4">
                @foreach ($jewelleryTypes as $type)
                    <a href="{{ route('jewellery-type.show', $type['slug']) }}" class="group flex w-[calc(50%-0.375rem)] flex-col items-center gap-3 rounded-2xl border border-ivory/10 bg-ivory/5 p-5 text-center transition-all duration-300 hover:-translate-y-1 hover:border-champagne-light/40 hover:bg-ivory/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-champagne-light sm:w-[calc(33.333%-0.667rem)] sm:gap-4 sm:p-6 lg:w-[calc(20%-0.8rem)]">
                        <x-ui.product-art :art="$typeIcons[$loop->index % count($typeIcons)]" class="h-20 w-20 rounded-full transition-transform duration-500 group-hover:scale-105 sm:h-24 sm:w-24" icon-class="text-charcoal" stroke-width="2.2" />
                        <span class="font-display text-sm text-ivory sm:text-base">{{ $type['name'] }}</span>
                        <span class="inline-flex items-center gap-1 text-xs text-ivory/60 transition-colors group-hover:text-champagne-light">
                            {{ $type['tag'] }}
                            <svg class="h-3 w-3 -translate-x-1 opacity-0 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- F. Best Sellers --}}
    @php
        $bestSellerItems = array_slice($bestSellers, 0, 10);
        $bestSellersTabletCount = count($bestSellerItems) >= 3 ? intdiv(count($bestSellerItems), 3) * 3 : count($bestSellerItems);
    @endphp
    <section class="section-pad" aria-labelledby="home-best-sellers-title">
        <div class="container-luxe">
            <div class="mb-8 flex items-end justify-between gap-4 sm:mb-10">
                <div>
                    <span class="eyebrow">Loved By Many</span>
                    <h2 id="home-best-sellers-title" class="font-display mt-2 text-2xl text-charcoal sm:text-3xl lg:text-4xl">Best Sellers</h2>
                </div>
                <a href="{{ route('best-sellers') }}" class="group/link hidden flex-shrink-0 items-center gap-1.5 text-sm font-medium text-charcoal sm:inline-flex">
                    <span class="link-underline group-hover/link:after:w-full">View All</span>
                    <svg class="h-3.5 w-3.5 transition-transform duration-300 group-hover/link:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </a>
            </div>
            <div class="grid grid-cols-2 gap-x-3 gap-y-6 sm:grid-cols-3 sm:gap-4 lg:grid-cols-5">
                @foreach ($bestSellerItems as $product)
                    <div @class(['sm:max-lg:hidden' => $loop->iteration > $bestSellersTabletCount])>
                        <x-ui.product-card :product="$product" />
                    </div>
                @endforeach
            </div>
            <a href="{{ route('best-sellers') }}" class="btn-secondary mt-8 flex w-full justify-center sm:hidden">View All Best Sellers</a>
        </div>
    </section>

    {{-- G. Promotional Luxury Banner --}}
    <section class="relative overflow-hidden bg-charcoal-soft py-20 sm:py-28" aria-labelledby="home-promo-title">
        <div class="absolute inset-0 opacity-[0.06]" style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 20px 20px; color: var(--color-champagne-light);"></div>
        <div class="pointer-events-none absolute left-1/2 top-1/2 h-[28rem] w-[28rem] -translate-x-1/2 -translate-y-1/2 rounded-full border border-champagne-light/10 sm:h-[34rem] sm:w-[34rem]" aria-hidden="true"></div>
        <div class="container-luxe relative mx-auto max-w-2xl text-center">
            <span class="eyebrow text-champagne-light">Limited Time</span>
            <h2 id="home-promo-title" class="font-display mt-3 text-3xl leading-tight text-ivory sm:text-5xl">Celebrate Every Moment</h2>
            <span class="mx-auto mt-5 block h-px w-12 bg-champagne-light/50" aria-hidden="true"></span>
            <p class="mt-5 text-base text-champagne-light sm:text-lg">Up to 20% Off Selected Designs</p>
            <a href="{{ route('best-sellers') }}" class="btn-light group mt-8 inline-flex">
                Shop the Offer
                <svg class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </a>
        </div>
    </section>

    {{-- H. Popular Subcategories --}}
    @if (count($topSubcategories) > 0)
        <section class="section-pad" aria-labelledby="home-subcategories-title">
            <div class="container-luxe">
                <div class="mb-8 text-center sm:mb-10">
                    <span class="eyebrow">Popular Picks</span>
                    <h2 id="home-subcategories-title" class="font-display mt-2 text-2xl text-charcoal sm:text-3xl lg:text-4xl">Shop by Subcategory</h2>
                </div>
                @php
                    $subIcons = ['ring', 'necklace', 'earring', 'bangle', 'diamond', 'bracelet'];
                @endphp
                <div class="grid grid-cols-3 gap-x-3 gap-y-7 sm:gap-x-4 md:grid-cols-6">
                    @foreach ($topSubcategories as $cat)
                        <a href="{{ route('category.show', $cat['slug']) }}" class="group flex flex-col items-center gap-2.5 rounded-xl text-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-champagne-dark focus-visible:ring-offset-4">
                            <span class="rounded-full p-1 ring-1 ring-transparent transition-all duration-300 group-hover:ring-champagne">
                                @if (! empty($cat['image']))
                                    <x-ui.optimized-image :src="$cat['image']" :alt="$cat['name']" sizes="96px" class="h-20 w-20 rounded-full border border-line object-cover transition-transform duration-300 group-hover:scale-105 sm:h-24 sm:w-24" />
                                @else
                                    <x-ui.product-art :art="$subIcons[$loop->index % count($subIcons)]" class="h-20 w-20 rounded-full border border-line transition-transform duration-300 group-hover:scale-105 sm:h-24 sm:w-24" icon-class="text-charcoal" stroke-width="2.2" />
                                @endif
                            </span>
                            <span class="text-xs font-medium leading-snug text-charcoal-soft transition-colors group-hover:text-champagne-dark sm:text-sm">{{ $cat['name'] }}</span>
                            <span class="-mt-1.5 text-[10px] text-muted sm:text-[11px]">{{ $cat['count'] }} {{ \Illuminate\Support\Str::plural('Product', $cat['count']) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- I. Shop By Recipient --}}
    <section class="section-pad bg-ivory-soft" aria-labelledby="home-recipient-title">
        <div class="container-luxe">
            <div class="mb-8 text-center sm:mb-10">
                <span class="eyebrow">Who Are You Shopping For</span>
                <h2 id="home-recipient-title" class="font-display mt-2 text-2xl text-charcoal sm:text-3xl lg:text-4xl">Shop by Recipient</h2>
            </div>
            <div class="mx-auto grid max-w-xl grid-cols-2 gap-3 sm:gap-4">
                @foreach ($recipients as $rec)
                    <a href="{{ route('recipient.show', $rec['slug']) }}" class="group relative block overflow-hidden rounded-xl border border-line bg-charcoal transition-shadow duration-300 hover:shadow-lift focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-champagne-dark focus-visible:ring-offset-2">
                        @if (! empty($rec['image']))
                            <x-ui.optimized-image :src="asset($rec['image'])" :alt="$rec['name']" sizes="(min-width: 640px) 400px, 50vw" class="aspect-square h-full w-full object-cover opacity-90 transition-transform duration-700 ease-out group-hover:scale-105" />
                        @else
                            <x-ui.product-art :art="$rec['art']" tone="{{ $rec['slug'] === 'for-him' ? 1 : 2 }}" class="aspect-square opacity-90 transition-transform duration-700 ease-out group-hover:scale-105" />
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-charcoal via-charcoal/20 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-2 p-3 sm:p-4">
                            <div class="min-w-0">
                                <span class="text-[10px] font-semibold uppercase tracking-wide text-champagne-light">{{ $rec['slug'] === 'for-him' ? "Men's Edit" : "Women's Edit" }}</span>
                                <h3 class="font-display mt-0.5 text-base text-ivory sm:text-lg">{{ $rec['name'] }}</h3>
                            </div>
                            <span class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-ivory/10 text-ivory transition-all duration-300 group-hover:translate-x-0.5 group-hover:bg-champagne-light group-hover:text-charcoal sm:h-8 sm:w-8" aria-hidden="true">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- J. Why Shop With Us --}}
    <section class="section-pad" aria-labelledby="home-promise-title">
        <div class="container-luxe">
            <div class="mb-8 text-center sm:mb-10">
                <span class="eyebrow">Our Promise</span>
                <h2 id="home-promise-title" class="font-display mt-2 text-2xl text-charcoal sm:text-3xl lg:text-4xl">Why Shop With Us</h2>
            </div>
            <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5 lg:grid-cols-6">
                @php
                    $trust = [
                        ['label' => '100% Certified', 'icon' => 'M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['label' => 'Secure Payments', 'icon' => 'M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6l8-4z'],
                        ['label' => 'Easy Returns', 'icon' => 'M3 12a9 9 0 1 0 2.64-6.36L3 8M3 3v5h5'],
                        ['label' => 'Free Shipping', 'icon' => 'M1 4h14v12H1zM15 8h4l3 3v5h-7zM5.5 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM18.5 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4z'],
                        ['label' => 'Lifetime Support', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['label' => 'Quality Guarantee', 'icon' => 'M12 2l2.6 5.6 6.1.6-4.6 4.2 1.3 6.1L12 15l-5.4 3 1.3-6.1L3.3 8.2l6.1-.6L12 2z'],
                    ];
                @endphp
                @foreach ($trust as $item)
                    <li class="group flex flex-col items-center gap-3 rounded-2xl border border-line p-5 text-center transition-all duration-300 hover:-translate-y-0.5 hover:border-beige-dark/60 hover:shadow-soft">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-beige text-champagne-dark transition-transform duration-300 group-hover:scale-110" aria-hidden="true">
                            <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="{{ $item['icon'] }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </div>
                        <span class="text-xs font-medium leading-snug text-charcoal-soft sm:text-sm">{{ $item['label'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- K. Customer Reviews --}}
    <section class="section-pad bg-ivory-soft" aria-labelledby="home-reviews-title">
        <div class="container-luxe">
            <div class="mb-8 text-center sm:mb-10">
                <span class="eyebrow">Testimonials</span>
                <h2 id="home-reviews-title" class="font-display mt-2 text-2xl text-charcoal sm:text-3xl lg:text-4xl">What Our Customers Say</h2>
            </div>
            <div
                x-data="{
                    active: 0,
                    total: {{ count($reviews) }},
                    perView: 3,
                    touchStartX: null,
                    get maxIndex() { return Math.max(0, this.total - this.perView); },
                    get pages() { return this.maxIndex + 1; },
                    setPerView() {
                        this.perView = window.innerWidth < 640 ? 1 : (window.innerWidth < 1024 ? 2 : 3);
                        this.active = Math.min(this.active, this.maxIndex);
                    },
                    prev() { this.active = Math.max(0, this.active - 1); },
                    next() { this.active = Math.min(this.maxIndex, this.active + 1); },
                    swipeStart(event) { this.touchStartX = event.changedTouches[0].clientX; },
                    swipeEnd(event) {
                        if (this.touchStartX === null) return;
                        const delta = event.changedTouches[0].clientX - this.touchStartX;
                        if (Math.abs(delta) > 40) { delta < 0 ? this.next() : this.prev(); }
                        this.touchStartX = null;
                    },
                }"
                x-init="setPerView()"
                @resize.window.debounce.150ms="setPerView()"
                @keydown.arrow-left="prev()"
                @keydown.arrow-right="next()"
                role="region"
                aria-roledescription="carousel"
                aria-label="Customer reviews"
                class="relative"
            >
                <div class="-mx-2.5 overflow-hidden py-2" @touchstart.passive="swipeStart($event)" @touchend="swipeEnd($event)">
                    <div class="flex transition-transform duration-500 ease-out" :style="`transform: translateX(-${active * (100 / perView)}%)`">
                        @foreach ($reviews as $review)
                            <div class="w-full flex-shrink-0 px-2.5 sm:w-1/2 lg:w-1/3">
                                <x-ui.review-card :review="$review" />
                            </div>
                        @endforeach
                    </div>
                </div>
                <div x-show="pages > 1" class="mt-8 flex items-center justify-center gap-4">
                    <button type="button" @click="prev()" :disabled="active === 0" aria-label="Previous reviews" class="icon-btn border border-line hover:border-champagne-dark disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-line disabled:hover:bg-transparent">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </button>
                    <div class="flex items-center gap-1.5">
                        <template x-for="page in pages" :key="page">
                            <button
                                type="button"
                                @click="active = page - 1"
                                :aria-label="`Go to review ${page}`"
                                :aria-current="active === page - 1 ? 'true' : 'false'"
                                :class="active === page - 1 ? 'w-6 bg-champagne-dark' : 'w-2 bg-beige hover:bg-beige-dark'"
                                class="h-2 rounded-full transition-all duration-300"
                            ></button>
                        </template>
                    </div>
                    <button type="button" @click="next()" :disabled="active >= maxIndex" aria-label="Next reviews" class="icon-btn border border-line hover:border-champagne-dark disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-line disabled:hover:bg-transparent">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- K2. Influencer Program --}}
    <section class="section-pad bg-charcoal" aria-labelledby="home-influencer-title">
        <div class="container-luxe grid grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <div class="overflow-hidden rounded-2xl">
                    <x-ui.optimized-image :src="asset('images/inf1.png')" alt="Rupnora influencer styling" sizes="(min-width: 1024px) 25vw, 50vw" class="aspect-[4/5] w-full object-cover transition-transform duration-700 ease-out hover:scale-105" />
                </div>
                <div class="mt-8 overflow-hidden rounded-2xl">
                    <x-ui.optimized-image :src="asset('images/inf2.png')" alt="Rupnora influencer piece" sizes="(min-width: 1024px) 25vw, 50vw" class="aspect-[4/5] w-full object-cover transition-transform duration-700 ease-out hover:scale-105" />
                </div>
            </div>
            <div class="text-center lg:text-left">
                <span class="eyebrow text-champagne-light">Rupnora Partner Program</span>
                <h2 id="home-influencer-title" class="font-display mt-3 text-2xl text-ivory sm:text-3xl lg:text-4xl">Become a Rupnora Influencer</h2>
                <p class="mx-auto mt-4 max-w-xl text-[15px] leading-relaxed text-ivory/70 lg:mx-0">
                    Partner with us to style, shoot and share Rupnora pieces with your audience. Get early access to new collections, complimentary pieces for content and exclusive commission on every sale you drive.
                </p>
                <ul class="mx-auto mt-6 inline-flex flex-col space-y-3 text-left lg:mx-0 lg:flex">
                    @foreach (['Early access to new collections', 'Complimentary pieces for content', 'Exclusive commission on referred sales'] as $perk)
                        <li class="flex items-start gap-3 text-sm text-ivory/70">
                            <span class="mt-0.5 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border border-champagne-light/30 text-champagne-light" aria-hidden="true">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </span>
                            {{ $perk }}
                        </li>
                    @endforeach
                </ul>
                <div class="mt-8 flex flex-wrap justify-center gap-3 lg:justify-start">
                    <a href="{{ route('influencer') }}" class="btn-light group">
                        Join as Influencer
                        <svg class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- L. Random Available Products --}}
    @if (count($randomInStockProducts) > 0)
        <section class="section-pad" aria-labelledby="home-styled-title">
            <div class="container-luxe">
                <div class="mb-8 text-center sm:mb-10">
                    <span class="eyebrow">@rupnora.jewellery</span>
                    <h2 id="home-styled-title" class="font-display mt-2 text-2xl text-charcoal sm:text-3xl lg:text-4xl">Styled By You</h2>
                </div>
                <div class="grid grid-cols-3 gap-2 sm:gap-4 lg:grid-cols-6">
                    @foreach ($randomInStockProducts as $product)
                        @php($productUrlKey = $product['slug'] ?? $product['id'])
                        <a href="{{ route('product.show', $productUrlKey) }}" class="group relative block overflow-hidden rounded-xl border border-line transition-shadow duration-300 hover:shadow-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-champagne-dark focus-visible:ring-offset-2">
                            @if (! empty($product['image']))
                                <x-ui.optimized-image :src="$product['image']" :alt="$product['name']" sizes="(min-width: 1024px) 16vw, (min-width: 640px) 33vw, 33vw" class="aspect-square w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                            @else
                                <x-ui.product-art :art="$product['art']" class="aspect-square transition-transform duration-500 group-hover:scale-110" />
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-paper/95 via-paper/20 to-transparent opacity-95"></div>
                            <div class="absolute inset-x-0 bottom-0 p-2.5 sm:p-3">
                                <span class="block truncate font-display text-[11px] leading-tight text-charcoal transition-colors group-hover:text-champagne-dark sm:text-sm">{{ $product['name'] }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- L2. Rupnora Style Partners --}}
    <section class="section-pad bg-ivory-soft" aria-labelledby="home-style-partners-title">
        <div class="container-luxe">
            <div class="grid overflow-hidden rounded-3xl bg-beige shadow-soft lg:grid-cols-2">
                <div class="flex items-center px-6 py-10 text-center sm:px-10 sm:py-16 lg:px-14 lg:text-left">
                    <div class="mx-auto max-w-xl lg:mx-0">
                        <span class="eyebrow">Earn With Rupnora</span>
                        <h2 id="home-style-partners-title" class="font-display mt-3 text-2xl text-charcoal sm:text-3xl lg:text-4xl">Rupnora Style Partners</h2>
                        <p class="mt-4 text-[15px] leading-relaxed text-muted">
                            Share the styles you love and earn commission when someone shops through your unique link. Join Rupnora Style Partners, track your earnings, and request a withdrawal when you&rsquo;re ready.
                        </p>
                        <a href="{{ route('account.affiliate.dashboard') }}" class="btn-primary group mt-7 inline-flex">
                            Join Style Partners
                            <svg class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </a>
                    </div>
                </div>
                <x-ui.optimized-image
                    :src="asset('images/affilate.png')"
                    alt="Rupnora Style Partner sharing jewellery with her audience"
                    sizes="(min-width: 1024px) 50vw, 100vw"
                    class="h-full min-h-72 w-full object-cover object-right lg:min-h-[430px]"
                />
            </div>
        </div>
    </section>

    {{-- M. Newsletter --}}
    <!-- <section class="relative overflow-hidden bg-beige py-20">
        <div class="container-luxe relative text-center">
            <span class="eyebrow">Stay Connected</span>
            <h2 class="font-display mt-3 text-3xl text-charcoal sm:text-4xl">Join Our World of Jewellery</h2>
            <p class="mx-auto mt-3 max-w-md text-sm text-muted">Subscribe for early access to new collections, styling edits and members-only offers.</p>
            <form class="mx-auto mt-7 flex max-w-md flex-col gap-3 sm:flex-row" onsubmit="event.preventDefault(); $store.ui.notify('Thank you for subscribing!', 'success'); this.reset()">
                <input type="email" required placeholder="Enter your email address" class="input-luxe !rounded-full flex-1">
                <button type="submit" class="btn-primary flex-shrink-0">Subscribe</button>
            </form>
        </div>
    </section> -->

</x-layouts.app>

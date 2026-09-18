<x-layouts.app :title="$title ?? null">

    {{-- Home offer popup: stays dismissed for 24 hours in this browser. --}}
    <div
        x-data="{
            open: false,
            storageKey: 'rupnora-home-offer-dismissed-at',
            dismissalPeriod: 24 * 60 * 60 * 1000,
            init() {
                let dismissedAt = 0;

                try {
                    dismissedAt = Number(window.localStorage.getItem(this.storageKey) || 0);
                } catch (error) {}

                if (!dismissedAt || Date.now() - dismissedAt >= this.dismissalPeriod) {
                    this.$nextTick(() => {
                        this.open = true;
                        this.$nextTick(() => this.$refs.closeButton?.focus());
                    });
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
                role="dialog"
                aria-modal="true"
                aria-labelledby="home-offer-title"
                class="relative grid max-h-[calc(100vh-2rem)] w-full max-w-3xl overflow-y-auto rounded-[1.75rem] bg-paper shadow-2xl sm:grid-cols-[0.9fr_1.1fr] sm:overflow-hidden"
            >
                <button
                    x-ref="closeButton"
                    type="button"
                    @click="dismiss()"
                    aria-label="Close offer"
                    class="absolute right-3 top-3 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-paper/90 text-charcoal shadow-sm transition hover:bg-paper hover:text-champagne-dark focus:outline-none focus:ring-2 focus:ring-champagne-dark focus:ring-offset-2"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg>
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
                    <p class="mt-3 text-sm leading-relaxed text-muted">Save on selected jewellery styles while the offer lasts.</p>
                    <a href="{{ route('best-sellers') }}" @click="dismiss()" class="btn-primary mt-6 w-full sm:w-auto">Shop the Offer</a>
                    <button type="button" @click="dismiss()" class="mt-3 text-xs font-medium text-muted underline-offset-4 hover:text-charcoal hover:underline">No thanks, maybe later</button>
                    <p class="mt-4 text-[10px] uppercase tracking-[0.12em] text-muted-light">Selected styles only. Terms apply.</p>
                </div>
            </section>
        </div>
    </div>

    {{-- A. Hero Banner --}}
    <x-ui.hero-slider :slides="$heroSlides" />

    {{-- C. Featured Collections --}}
    <section class="section-pad-sm bg-ivory-soft">
        <div class="container-luxe">
            <div class="mb-5 text-center">
                <span class="eyebrow">Curated For You</span>
                <h2 class="font-display mt-1.5 text-2xl text-charcoal sm:text-3xl">Featured Collections</h2>
                <p class="mx-auto mt-2 max-w-xl text-sm text-muted">Thoughtfully curated edits, from everyday essentials to once-in-a-lifetime pieces.</p>
            </div>
            <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-5">
                @foreach ($collections as $collection)
                    <x-ui.collection-card :collection="$collection" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- B. Shop By Category --}}
    <section class="section-pad-sm">
        <div class="container-luxe">
            <div class="mb-5 flex items-end justify-between">
                <div>
                    <span class="eyebrow">Explore</span>
                    <h2 class="font-display mt-1.5 text-2xl text-charcoal sm:text-3xl">Shop by Category</h2>
                </div>
                <a href="{{ route('categories.index') }}" class="link-underline text-sm font-medium text-charcoal">View All</a>
            </div>
            <div class="-mx-5 px-5 sm:mx-0 sm:px-0" style="overflow-x: auto;">
                <div style="display: grid; min-width: {{ max(count($parentCategories), 1) * 132 }}px; grid-template-columns: repeat({{ max(count($parentCategories), 1) }}, minmax(0, 1fr)); gap: 0.625rem;">
                    @foreach ($parentCategories as $cat)
                        <x-ui.category-card :category="$cat" />
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- D. New Arrivals --}}
    <section class="section-pad">
        <div class="container-luxe">
            <div class="mb-10 flex items-end justify-between">
                <div>
                    <span class="eyebrow">Just In</span>
                    <h2 class="font-display mt-2 text-3xl text-charcoal sm:text-4xl">New Arrivals</h2>
                </div>
                <a href="{{ route('new-arrivals') }}" class="hidden link-underline text-sm font-medium text-charcoal sm:block">View All</a>
            </div>
            <div x-data="{}" class="-mr-5 flex snap-x gap-3 overflow-x-auto pr-5 pb-4 sm:mr-0 sm:grid sm:grid-cols-3 sm:overflow-visible sm:pr-0 sm:pb-0 lg:grid-cols-5">
                @foreach ($newArrivals as $product)
                    <div class="w-[72%] flex-shrink-0 snap-start sm:w-auto">
                        <x-ui.product-card :product="$product" />
                    </div>
                @endforeach
            </div>
            <a href="{{ route('new-arrivals') }}" class="btn-secondary mt-8 flex w-full justify-center sm:hidden">View All New Arrivals</a>
        </div>
    </section>

    {{-- E. Shop by Jewellery Type --}}
    <section class="section-pad bg-charcoal">
        <div class="container-luxe">
            <div class="mb-10 text-center">
                <span class="eyebrow text-champagne-light">By Metal &amp; Stone</span>
                <h2 class="font-display mt-2 text-3xl text-ivory sm:text-4xl">Shop by Jewellery Type</h2>
            </div>
            @php
                $typeIcons = ['ring', 'necklace', 'earring', 'bangle', 'diamond'];
            @endphp
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                @foreach ($jewelleryTypes as $type)
                    <a href="{{ route('jewellery-type.show', $type['slug']) }}" class="group flex flex-col items-center gap-4 rounded-2xl border border-ivory/10 bg-ivory/5 p-6 text-center transition-colors hover:border-champagne-light/40 hover:bg-ivory/10">
                        <x-ui.product-art :art="$typeIcons[$loop->index % count($typeIcons)]" class="h-20 w-20 rounded-full sm:h-24 sm:w-24" icon-class="text-charcoal" stroke-width="2.2" />
                        <span class="font-display text-sm text-ivory sm:text-base">{{ $type['name'] }}</span>
                        <span class="text-xs text-ivory/60">{{ $type['tag'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- F. Best Sellers --}}
    <section class="section-pad">
        <div class="container-luxe">
            <div class="mb-10 flex items-end justify-between">
                <div>
                    <span class="eyebrow">Loved By Many</span>
                    <h2 class="font-display mt-2 text-3xl text-charcoal sm:text-4xl">Best Sellers</h2>
                </div>
                <a href="{{ route('best-sellers') }}" class="hidden link-underline text-sm font-medium text-charcoal sm:block">View All</a>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-5">
                @foreach (array_slice($bestSellers, 0, 10) as $product)
                    <x-ui.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- G. Promotional Luxury Banner --}}
    <section class="relative overflow-hidden bg-charcoal-soft py-20 sm:py-28">
        <div class="absolute inset-0 opacity-[0.06]" style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 20px 20px; color: var(--color-champagne-light);"></div>
        <div class="container-luxe relative text-center">
            <span class="eyebrow text-champagne-light">Limited Time</span>
            <h2 class="font-display mt-3 text-4xl text-ivory sm:text-5xl">Celebrate Every Moment</h2>
            <p class="mt-4 text-lg text-champagne-light">Up to 20% Off Selected Designs</p>
            <a href="{{ route('best-sellers') }}" class="btn-light mt-8 inline-flex">Shop the Offer</a>
        </div>
    </section>

    {{-- H. Popular Subcategories --}}
    @if (count($topSubcategories) > 0)
        <section class="section-pad">
            <div class="container-luxe">
                <div class="mb-10 text-center">
                    <span class="eyebrow">Popular Picks</span>
                    <h2 class="font-display mt-2 text-3xl text-charcoal sm:text-4xl">Shop by Subcategory</h2>
                </div>
                @php
                    $subIcons = ['ring', 'necklace', 'earring', 'bangle', 'diamond', 'bracelet'];
                @endphp
                <div class="grid grid-cols-3 gap-3 sm:grid-cols-6 sm:gap-4">
                    @foreach ($topSubcategories as $cat)
                        <a href="{{ route('category.show', $cat['slug']) }}" class="group flex flex-col items-center gap-3 text-center">
                            @if (! empty($cat['image']))
                                <x-ui.optimized-image :src="$cat['image']" :alt="$cat['name']" sizes="96px" class="h-20 w-20 rounded-full border border-line object-cover transition-transform duration-300 group-hover:scale-105 sm:h-24 sm:w-24" />
                            @else
                                <x-ui.product-art :art="$subIcons[$loop->index % count($subIcons)]" class="h-20 w-20 rounded-full border border-line transition-transform duration-300 group-hover:scale-105 sm:h-24 sm:w-24" icon-class="text-charcoal" stroke-width="2.2" />
                            @endif
                            <span class="text-xs font-medium text-charcoal-soft sm:text-sm">{{ $cat['name'] }}</span>
                            <span class="text-[10px] text-muted">{{ $cat['count'] }} {{ \Illuminate\Support\Str::plural('Product', $cat['count']) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- I. Shop By Recipient --}}
    <section class="section-pad bg-ivory-soft">
        <div class="container-luxe">
            <div class="mb-10 text-center">
                <span class="eyebrow">Who Are You Shopping For</span>
                <h2 class="font-display mt-2 text-3xl text-charcoal sm:text-4xl">Shop by Recipient</h2>
            </div>
            <div class="mx-auto grid max-w-xl grid-cols-2 gap-3 sm:gap-4">
                @foreach ($recipients as $rec)
                    <a href="{{ route('recipient.show', $rec['slug']) }}" class="group relative block overflow-hidden rounded-xl border border-line bg-charcoal">
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
                            <span class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-ivory/10 text-ivory transition-all duration-300 group-hover:translate-x-0.5 group-hover:bg-champagne-light group-hover:text-charcoal">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- J. Why Shop With Us --}}
    <section class="section-pad">
        <div class="container-luxe">
            <div class="mb-10 text-center">
                <span class="eyebrow">Our Promise</span>
                <h2 class="font-display mt-2 text-3xl text-charcoal sm:text-4xl">Why Shop With Us</h2>
            </div>
            <div class="grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-6">
                @php
                    $trust = [
                        ['label' => '100% Certified', 'icon' => 'M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['label' => 'Secure Payments', 'icon' => 'M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6l8-4z'],
                        ['label' => 'Easy Returns', 'icon' => 'M4 4v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V4'],
                        ['label' => 'Free Shipping', 'icon' => 'M3 7h13v10H3zM16 10h3l2 3v4h-5z'],
                        ['label' => 'Lifetime Support', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['label' => 'Quality Guarantee', 'icon' => 'M12 2l2.6 5.6 6.1.6-4.6 4.2 1.3 6.1L12 15l-5.4 3 1.3-6.1L3.3 8.2l6.1-.6L12 2z'],
                    ];
                @endphp
                @foreach ($trust as $item)
                    <div class="flex flex-col items-center gap-3 rounded-2xl border border-line p-5 text-center transition-shadow hover:shadow-soft">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-beige text-champagne-dark">
                            <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="{{ $item['icon'] }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </div>
                        <span class="text-xs font-medium leading-snug text-charcoal-soft sm:text-sm">{{ $item['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- K. Customer Reviews --}}
    <section class="section-pad bg-ivory-soft">
        <div class="container-luxe">
            <div class="mb-10 text-center">
                <span class="eyebrow">Testimonials</span>
                <h2 class="font-display mt-2 text-3xl text-charcoal sm:text-4xl">What Our Customers Say</h2>
            </div>
            <div
                x-data="{ active: 0, total: {{ count($reviews) }}, perView: 3 }"
                x-init="() => { const set = () => perView = window.innerWidth < 640 ? 1 : (window.innerWidth < 1024 ? 2 : 3); set(); window.addEventListener('resize', set) }"
                class="relative"
            >
                <div class="overflow-hidden">
                    <div class="flex transition-transform duration-500 ease-out" :style="`transform: translateX(-${active * (100 / perView)}%)`">
                        @foreach ($reviews as $review)
                            <div class="w-full flex-shrink-0 px-2.5 sm:w-1/2 lg:w-1/3">
                                <x-ui.review-card :review="$review" />
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-8 flex items-center justify-center gap-3">
                    <button @click="active = Math.max(0, active - 1)" class="icon-btn border border-line"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round" /></svg></button>
                    <button @click="active = Math.min(total - perView, active + 1)" class="icon-btn border border-line"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg></button>
                </div>
            </div>
        </div>
    </section>

    {{-- K2. Influencer Program --}}
    <section class="section-pad bg-charcoal">
        <div class="container-luxe grid grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <x-ui.optimized-image :src="asset('images/inf1.png')" alt="Rupnora influencer styling" sizes="(min-width: 1024px) 25vw, 50vw" class="aspect-[4/5] w-full rounded-2xl object-cover" />
                <x-ui.optimized-image :src="asset('images/inf2.png')" alt="Rupnora influencer piece" sizes="(min-width: 1024px) 25vw, 50vw" class="mt-8 aspect-[4/5] w-full rounded-2xl object-cover" />
            </div>
            <div>
                <span class="eyebrow text-champagne-light">Rupnora Partner Program</span>
                <h2 class="font-display mt-3 text-3xl text-ivory sm:text-4xl">Become a Rupnora Influencer</h2>
                <p class="mt-4 text-[15px] leading-relaxed text-ivory/70">
                    Partner with us to style, shoot and share Rupnora pieces with your audience. Get early access to new collections, complimentary pieces for content and exclusive commission on every sale you drive.
                </p>
                <ul class="mt-6 space-y-3">
                    @foreach (['Early access to new collections', 'Complimentary pieces for content', 'Exclusive commission on referred sales'] as $perk)
                        <li class="flex items-start gap-3 text-sm text-ivory/70">
                            <svg class="mt-0.5 h-4.5 w-4.5 flex-shrink-0 text-champagne-light" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            {{ $perk }}
                        </li>
                    @endforeach
                </ul>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('influencer') }}" class="btn-light">Join as Influencer</a>
                </div>
            </div>
        </div>
    </section>

    {{-- L. Random Available Products --}}
    @if (count($randomInStockProducts) > 0)
        <section class="section-pad">
            <div class="container-luxe">
                <div class="mb-10 text-center">
                    <span class="eyebrow">@rupnora.jewellery</span>
                    <h2 class="font-display mt-2 text-3xl text-charcoal sm:text-4xl">Styled By You</h2>
                </div>
                <div class="grid grid-cols-3 gap-2 sm:gap-4 lg:grid-cols-6">
                    @foreach ($randomInStockProducts as $product)
                        @php($productUrlKey = $product['slug'] ?? $product['id'])
                        <a href="{{ route('product.show', $productUrlKey) }}" class="group relative block overflow-hidden rounded-xl border border-line">
                            @if (! empty($product['image']))
                                <x-ui.optimized-image :src="$product['image']" :alt="$product['name']" sizes="(min-width: 1024px) 16vw, (min-width: 640px) 33vw, 33vw" class="aspect-square w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                            @else
                                <x-ui.product-art :art="$product['art']" class="aspect-square transition-transform duration-500 group-hover:scale-110" />
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-paper/95 via-paper/20 to-transparent opacity-95"></div>
                            <div class="absolute inset-x-0 bottom-0 p-2.5 sm:p-3">
                                <span class="block truncate font-display text-[11px] leading-tight text-charcoal sm:text-sm">{{ $product['name'] }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

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

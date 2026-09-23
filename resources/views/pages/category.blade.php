@php
    $meta = collect($meta ?? []);
    $showBanner = $showBanner ?? true;
    $productCollection = collect($filterProducts ?? $products);
    $totalProducts = $totalProducts ?? count($products);
    $priceMin = max(0, (int) floor($productCollection->min('price') ?? 0));
    $priceMax = max(1000, (int) ceil($productCollection->max('price') ?? 500000));
    $currentCategory = \App\Models\Category::query()
        ->active()
        ->where('slug', $slug)
        ->first();
    $subcategoryOptions = $currentCategory
        ? \App\Models\Category::query()
            ->active()
            ->where('parent_id', $currentCategory->id)
            ->withCount(['products as products_count' => fn ($query) => $query->active()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn ($category) => [
                $category->slug => [
                    'label' => $category->name,
                    'count' => $category->products_count,
                ],
            ])
            ->filter(fn ($option) => $option['count'] > 0)
            ->all()
        : [];
    $jewelleryTypeOptions = $productCollection
        ->whereNotNull('type')
        ->groupBy('type')
        ->mapWithKeys(fn ($items, $value) => [$value => ['label' => $value, 'count' => $items->count()]])
        ->filter(fn ($option) => $option['count'] > 0)
        ->sortBy('label')
        ->all();
    $metalOptions = $productCollection
        ->whereNotNull('metal')
        ->groupBy('metal')
        ->mapWithKeys(fn ($items, $value) => [$value => ['label' => $value, 'count' => $items->count()]])
        ->filter(fn ($option) => $option['count'] > 0)
        ->sortBy('label')
        ->all();
    $purityOptions = $productCollection
        ->whereNotNull('purity')
        ->groupBy('purity')
        ->mapWithKeys(fn ($items, $value) => [$value => ['label' => $value, 'count' => $items->count()]])
        ->sortKeys()
        ->all();
    $genderOptions = $productCollection
        ->whereNotNull('gender')
        ->groupBy('gender')
        ->mapWithKeys(fn ($items, $value) => [$value => ['label' => $value, 'count' => $items->count()]])
        ->sortBy('label')
        ->all();
    $collectionCounts = $productCollection
        ->flatMap(function ($product) {
            $collections = collect($product['collections'] ?? [])->filter();

            return $collections->isNotEmpty()
                ? $collections
                : collect([$product['collection'] ?? null])->filter();
        })
        ->filter()
        ->countBy();
    $collectionLabels = collect(\App\Support\Catalog::collections())
        ->pluck('name', 'slug')
        ->merge(
            \App\Models\JewelleryCollection::query()
                ->active()
                ->whereIn('slug', $collectionCounts->keys())
                ->pluck('name', 'slug')
        );
    $collectionOptions = $collectionCounts
        ->mapWithKeys(fn ($count, $value) => [
            $value => [
                'label' => $collectionLabels[$value] ?? str($value)->replace('-', ' ')->title()->toString(),
                'count' => $count,
            ],
        ])
        ->filter(fn ($option) => $option['count'] > 0)
        ->sortBy('label')
        ->all();
    $ratingOptions = collect([4 => '4★ & above', 3 => '3★ & above'])
        ->filter(fn ($label, $value) => $productCollection->where('rating', '>=', $value)->isNotEmpty())
        ->mapWithKeys(fn ($label, $value) => [$value => ['label' => $label, 'count' => $productCollection->where('rating', '>=', $value)->count()]])
        ->all();
    $availabilityOptions = $productCollection->where('in_stock', true)->isNotEmpty()
        ? ['in_stock' => ['label' => 'In Stock Only', 'count' => $productCollection->where('in_stock', true)->count()]]
        : [];
    $flagOptions = collect([
        'new' => ['label' => 'New Arrivals', 'count' => $productCollection->where('is_new', true)->count()],
        'bestseller' => ['label' => 'Best Sellers', 'count' => $productCollection->where('is_bestseller', true)->count()],
        'discount' => ['label' => 'On Discount', 'count' => $meta->where('discount', '>', 0)->count()],
    ])->filter(fn ($option) => $option['count'] > 0)->all();
    $filterSections = collect([
        'subcategory' => ['label' => 'Sub-Category', 'type' => 'checkbox', 'options' => $subcategoryOptions],
        'type' => ['label' => 'Jewellery Type', 'type' => 'checkbox', 'options' => $jewelleryTypeOptions],
        'metal' => ['label' => 'Material Type', 'type' => 'checkbox', 'options' => $metalOptions],
        'purity' => ['label' => 'Gold Purity', 'type' => 'checkbox', 'options' => $purityOptions],
        'gender' => ['label' => 'Gender', 'type' => 'checkbox', 'options' => $genderOptions],
        'collection' => ['label' => 'Collection', 'type' => 'checkbox', 'options' => $collectionOptions],
        'rating' => ['label' => 'Rating', 'type' => 'checkbox', 'options' => $ratingOptions],
        'availability' => ['label' => 'Availability', 'type' => 'checkbox', 'options' => $availabilityOptions],
        'flags' => ['label' => 'Highlights', 'type' => 'checkbox', 'options' => $flagOptions],
    ])->filter(fn ($section) => filled($section['options']))->all();
    $filterReset = [
        'subcategory' => [], 'type' => [], 'metal' => [], 'purity' => [], 'gender' => [], 'collection' => [],
        'rating' => [], 'availability' => [], 'flags' => [], 'priceMin' => $priceMin, 'priceMax' => $priceMax,
    ];
@endphp

<x-layouts.app :title="$title">
    <div
        x-data="{
            loading: true,
            loadingMore: false,
            loadingAll: false,
            loadError: '',
            hasMore: {{ Illuminate\Support\Js::from($hasMore ?? false) }},
            nextPage: {{ Illuminate\Support\Js::from($nextPage ?? null) }},
            loadUrl: {{ Illuminate\Support\Js::from($loadUrl ?? request()->url()) }},
            sort: 'recommended',
            filters: { subcategory: [], type: [], metal: [], purity: [], gender: {{ Illuminate\Support\Js::from($initialGenderFilter ?? []) }}, collection: [], rating: [], availability: [], flags: [], priceMin: {{ $priceMin }}, priceMax: {{ $priceMax }} },
            meta: {{ Illuminate\Support\Js::from($meta) }},
            matches(p) {
                if (this.filters.subcategory.length && !this.filters.subcategory.includes(p.subcategory)) return false;
                if (this.filters.type.length && !this.filters.type.includes(p.type)) return false;
                if (this.filters.metal.length && !this.filters.metal.includes(p.metal)) return false;
                if (this.filters.purity.length && !this.filters.purity.includes(p.purity)) return false;
                if (this.filters.gender.length && !this.filters.gender.includes(p.gender)) return false;
                if (this.filters.collection.length && !this.filters.collection.some(collection => p.collections.includes(collection))) return false;
                if (this.filters.availability.includes('in_stock') && !p.in_stock) return false;
                if (this.filters.flags.includes('new') && !p.is_new) return false;
                if (this.filters.flags.includes('bestseller') && !p.is_bestseller) return false;
                if (this.filters.flags.includes('discount') && p.discount <= 0) return false;
                if (this.filters.rating.length) {
                    const min = Math.min(...this.filters.rating.map(Number));
                    if (p.rating < min) return false;
                }
                if (p.price < this.filters.priceMin || p.price > this.filters.priceMax) return false;
                return true;
            },
            rank(p) {
                switch (this.sort) {
                    case 'newest': return -p.i;
                    case 'price_low': return p.price;
                    case 'price_high': return -p.price;
                    case 'best_selling': return -(p.is_bestseller ? 100000 : 0) - p.reviews;
                    case 'rating': return -p.rating;
                    case 'discount': return -p.discount;
                    default: return p.i;
                }
            },
            get visibleCount() { return this.meta.filter(p => this.matches(p)).length; },
            init() {
                setTimeout(() => this.loading = false, 500);
                this.$watch('filters', () => this.loadRemaining());
                this.$watch('sort', () => this.loadRemaining());
                this.$nextTick(() => {
                    this.observer = new IntersectionObserver((entries) => {
                        if (entries.some(entry => entry.isIntersecting)) this.loadMore();
                    }, { rootMargin: '600px 0px' });
                    this.observer.observe(this.$refs.loadSentinel);
                });
            },
            async loadMore() {
                if (this.loadingMore || !this.hasMore || !this.nextPage) return;

                this.loadingMore = true;
                this.loadError = '';

                try {
                    const url = new URL(this.loadUrl, window.location.origin);
                    url.searchParams.set('page', this.nextPage);
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) throw new Error('Unable to load more products.');

                    const data = await response.json();
                    this.meta.push(...data.meta);
                    this.$refs.productGrid.insertAdjacentHTML('beforeend', data.html);
                    this.hasMore = data.has_more;
                    this.nextPage = data.next_page;
                } catch (error) {
                    this.loadError = error.message || 'Unable to load more products.';
                } finally {
                    this.loadingMore = false;
                    this.queueNextPageIfNeeded();
                }
            },
            async loadRemaining() {
                if (this.loadingAll || !this.hasMore) return;

                this.loadingAll = true;
                try {
                    while (this.hasMore) {
                        if (this.loadingMore) {
                            await new Promise(resolve => setTimeout(resolve, 50));
                            continue;
                        }

                        await this.loadMore();
                        if (this.loadError) break;
                    }
                } finally {
                    this.loadingAll = false;
                }
            },
            queueNextPageIfNeeded() {
                this.$nextTick(() => {
                    const sentinel = this.$refs.loadSentinel;
                    if (this.hasMore && !this.loadingMore && !this.loadError && sentinel && sentinel.getBoundingClientRect().top < window.innerHeight + 600) {
                        this.loadMore();
                    }
                });
            }
        }"
    >
        <div class="container-luxe pt-6">
            <x-ui.breadcrumb :trail="[['label' => 'Jewellery', 'url' => route('collections.index')], ['label' => $category['name']]]" />
        </div>

        @if ($showBanner)
            {{-- Category banner --}}
            @php
                $categoryHeroImage = $category['banner'] ?? $category['image'] ?? null;
            @endphp
            <div class="relative mt-5 h-56 overflow-hidden">
                @if ($categoryHeroImage)
                    <x-ui.optimized-image :src="$categoryHeroImage" :alt="$category['name']" sizes="100vw" class="h-full w-full object-contain" />
                @else
                    <x-ui.product-art :art="$category['art']" class="aspect-[16/6] sm:aspect-[16/4]" />
                @endif
            </div>
        @else
            <div class="container-luxe py-8 text-center sm:py-10">
                <span class="eyebrow">Explore</span>
                <h1 class="font-display mt-2 text-4xl text-charcoal sm:text-5xl">{{ $category['name'] }}</h1>
                @if (! empty($category['blurb']))
                    <p class="mx-auto mt-3 max-w-xl text-sm text-muted sm:text-base">{{ $category['blurb'] }}</p>
                @endif
            </div>
        @endif

        <div class="container-luxe {{ $showBanner ? 'py-8 sm:py-10' : 'pb-8 sm:pb-10' }}">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-line pb-5">
                <p class="text-sm text-muted">
                    <span x-text="visibleCount" class="font-semibold text-charcoal"></span>
                    <span x-show="hasMore && !loadingAll"> of {{ number_format($totalProducts) }}</span>
                    Products
                </p>
                <div class="flex items-center gap-3">
                    <x-ui.filter-drawer :sections="$filterSections" :price-min="$priceMin" :price-max="$priceMax" :reset="$filterReset" />
                    <x-ui.sort-dropdown />
                </div>
            </div>

            <div class="mt-8 flex gap-10">
                <x-ui.filter-sidebar :sections="$filterSections" :price-min="$priceMin" :price-max="$priceMax" :reset="$filterReset" />

                <div class="min-w-0 flex-1">
                    {{-- Loading skeleton --}}
                    <div x-show="loading" x-cloak class="grid grid-cols-2 gap-x-3 gap-y-6 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        <x-ui.skeleton-card :count="8" />
                    </div>

                    {{-- Empty state --}}
                    <div x-show="!loading && !loadingAll && visibleCount === 0" x-cloak>
                        <x-ui.empty-state
                            icon="search"
                            title="No products match your filters"
                            description="Try adjusting or clearing your filters to see more results."
                        />
                    </div>

                    {{-- Product grid --}}
                    <div x-ref="productGrid" x-show="!loading && visibleCount > 0" x-cloak class="grid grid-cols-2 gap-x-3 gap-y-6 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        @include('pages.partials.category-products', ['products' => $products, 'startIndex' => 0])
                    </div>

                    <div x-ref="loadSentinel" x-show="hasMore || loadingMore || loadError || meta.length > 20" x-cloak class="mt-10 flex min-h-12 items-center justify-center" aria-live="polite">
                        <div x-show="loadingMore" x-cloak class="flex items-center gap-2 text-sm text-muted">
                            <svg class="h-5 w-5 animate-spin text-champagne-dark" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" />
                                <path class="opacity-75" fill="currentColor" d="M12 3a9 9 0 00-9 9h3a6 6 0 016-6V3z" />
                            </svg>
                            <span>Loading more products...</span>
                        </div>
                        <button x-show="loadError && hasMore" x-cloak type="button" @click="loadMore" class="btn-ghost text-sm">
                            <span x-text="loadError"></span> Retry
                        </button>
                        <p x-show="!hasMore && meta.length > 20 && visibleCount > 0" x-cloak class="text-sm text-muted">You have viewed all products.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

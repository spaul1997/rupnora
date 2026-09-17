@php
    $meta = collect($products)->map(fn ($p, $i) => [
        'i' => $i, 'category' => $p['category'], 'type' => $p['type'], 'metal' => $p['metal'], 'purity' => $p['purity'],
        'gender' => $p['gender'], 'occasion' => $p['occasion'], 'price' => $p['price'],
        'rating' => $p['rating'], 'reviews' => $p['reviews_count'], 'in_stock' => $p['in_stock'],
        'is_new' => $p['is_new'], 'is_bestseller' => $p['is_bestseller'],
        'discount' => $p['mrp'] > $p['price'] ? round((($p['mrp'] - $p['price']) / $p['mrp']) * 100) : 0,
    ])->values();
    $showBanner = $showBanner ?? true;
    $productCollection = collect($products);
    $priceMin = max(0, (int) floor($productCollection->min('price') ?? 0));
    $priceMax = max(1000, (int) ceil($productCollection->max('price') ?? 500000));
    $categoryOptions = \App\Models\Category::query()
        ->active()
        ->parents()
        ->with([
            'children' => fn ($query) => $query
                ->active()
                ->withCount(['products as products_count' => fn ($products) => $products->active()]),
        ])
        ->withCount(['products as products_count' => fn ($query) => $query->active()])
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get()
        ->mapWithKeys(fn ($category) => [
            $category->slug => [
                'label' => $category->name,
                'count' => $category->products_count + $category->children->sum('products_count'),
            ],
        ])
        ->all();
    $jewelleryTypeOptions = \App\Models\JewelleryType::query()
        ->active()
        ->withCount(['products as products_count' => fn ($query) => $query->active()])
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get()
        ->mapWithKeys(fn ($type) => [$type->name => ['label' => $type->name, 'count' => $type->products_count]])
        ->all();
    $metalOptions = \App\Models\MetalType::query()
        ->active()
        ->withCount(['products as products_count' => fn ($query) => $query->active()])
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get()
        ->mapWithKeys(fn ($type) => [$type->name => ['label' => $type->name, 'count' => $type->products_count]])
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
    $occasionLabels = \App\Models\Product::OCCASIONS;
    $occasionOptions = $productCollection
        ->flatMap(fn ($product) => $product['occasion'] ?? [])
        ->filter()
        ->countBy()
        ->mapWithKeys(fn ($count, $value) => [$value => ['label' => $occasionLabels[$value] ?? str($value)->replace('-', ' ')->title()->toString(), 'count' => $count]])
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
        'category' => ['label' => 'Parent Category', 'type' => 'checkbox', 'options' => $categoryOptions],
        'type' => ['label' => 'Jewellery Type', 'type' => 'checkbox', 'options' => $jewelleryTypeOptions],
        'metal' => ['label' => 'Material Type', 'type' => 'checkbox', 'options' => $metalOptions],
        'purity' => ['label' => 'Gold Purity', 'type' => 'checkbox', 'options' => $purityOptions],
        'gender' => ['label' => 'Gender', 'type' => 'checkbox', 'options' => $genderOptions],
        'occasion' => ['label' => 'Occasion', 'type' => 'checkbox', 'options' => $occasionOptions],
        'rating' => ['label' => 'Rating', 'type' => 'checkbox', 'options' => $ratingOptions],
        'availability' => ['label' => 'Availability', 'type' => 'checkbox', 'options' => $availabilityOptions],
        'flags' => ['label' => 'Highlights', 'type' => 'checkbox', 'options' => $flagOptions],
    ])->filter(fn ($section) => filled($section['options']))->all();
@endphp

<x-layouts.app :title="$title">
    <div
        x-data="{
            loading: true,
            sort: 'recommended',
            filters: { category: [], type: [], metal: [], purity: [], gender: {{ Illuminate\Support\Js::from($initialGenderFilter ?? []) }}, occasion: [], rating: [], availability: [], flags: [], priceMin: {{ $priceMin }}, priceMax: {{ $priceMax }} },
            meta: {{ Illuminate\Support\Js::from($meta) }},
            matches(p) {
                if (this.filters.category.length && !this.filters.category.includes(p.category)) return false;
                if (this.filters.type.length && !this.filters.type.includes(p.type)) return false;
                if (this.filters.metal.length && !this.filters.metal.includes(p.metal)) return false;
                if (this.filters.purity.length && !this.filters.purity.includes(p.purity)) return false;
                if (this.filters.gender.length && !this.filters.gender.includes(p.gender)) return false;
                if (this.filters.occasion.length && !this.filters.occasion.some(o => p.occasion.includes(o))) return false;
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
            get visibleCount() { return this.meta.filter(p => this.matches(p)).length; }
        }"
        x-init="setTimeout(() => loading = false, 500)"
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
                    <span x-text="visibleCount" class="font-semibold text-charcoal"></span> Products
                </p>
                <div class="flex items-center gap-3">
                    <x-ui.filter-drawer :sections="$filterSections" :price-min="$priceMin" :price-max="$priceMax" />
                    <x-ui.sort-dropdown />
                </div>
            </div>

            <div class="mt-8 flex gap-10">
                <x-ui.filter-sidebar :sections="$filterSections" :price-min="$priceMin" :price-max="$priceMax" />

                <div class="min-w-0 flex-1">
                    {{-- Loading skeleton --}}
                    <div x-show="loading" x-cloak class="grid grid-cols-2 gap-x-3 gap-y-6 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        <x-ui.skeleton-card :count="8" />
                    </div>

                    {{-- Empty state --}}
                    <div x-show="!loading && visibleCount === 0" x-cloak>
                        <x-ui.empty-state
                            icon="search"
                            title="No products match your filters"
                            description="Try adjusting or clearing your filters to see more results."
                        />
                    </div>

                    {{-- Product grid --}}
                    <div x-show="!loading && visibleCount > 0" x-cloak class="grid grid-cols-2 gap-x-3 gap-y-6 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        @foreach ($products as $i => $product)
                            <div x-show="matches(meta[{{ $i }}])" :style="`order: ${rank(meta[{{ $i }}]) + 100000}`">
                                <x-ui.product-card :product="$product" />
                            </div>
                        @endforeach
                    </div>

                    @if (count($products) > 0)
                        <div class="mt-14">
                            <x-ui.pagination :current="1" :total="3" />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

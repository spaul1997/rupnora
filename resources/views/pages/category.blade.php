@php
    $meta = collect($products)->map(fn ($p, $i) => [
        'i' => $i, 'category' => $p['category'], 'metal' => $p['metal'], 'purity' => $p['purity'],
        'gender' => $p['gender'], 'occasion' => $p['occasion'], 'price' => $p['price'],
        'rating' => $p['rating'], 'reviews' => $p['reviews_count'], 'in_stock' => $p['in_stock'],
        'is_new' => $p['is_new'], 'is_bestseller' => $p['is_bestseller'],
        'discount' => $p['mrp'] > $p['price'] ? round((($p['mrp'] - $p['price']) / $p['mrp']) * 100) : 0,
    ])->values();
@endphp

<x-layouts.app :title="$title">
    <div
        x-data="{
            loading: true,
            sort: 'recommended',
            filters: { category: [], metal: [], purity: [], gender: [], occasion: [], rating: [], availability: [], flags: [], priceMin: 0, priceMax: 500000 },
            meta: {{ Illuminate\Support\Js::from($meta) }},
            matches(p) {
                if (this.filters.category.length && !this.filters.category.includes(p.category)) return false;
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

        {{-- Category banner --}}
        <div class="relative mt-5 overflow-hidden">
            <x-ui.product-art :art="$category['art']" class="aspect-[16/6] sm:aspect-[16/4]" />
            <div class="absolute inset-0 bg-gradient-to-r from-charcoal/55 via-charcoal/25 to-transparent"></div>
            <div class="absolute inset-0 flex items-center">
                <div class="container-luxe">
                    <h1 class="font-display text-3xl text-ivory sm:text-5xl">{{ $category['name'] }}</h1>
                    <p class="mt-2 max-w-md text-sm text-ivory/85 sm:text-base">{{ $category['blurb'] }}</p>
                </div>
            </div>
        </div>

        <div class="container-luxe py-8 sm:py-10">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-line pb-5">
                <p class="text-sm text-muted">
                    <span x-text="visibleCount" class="font-semibold text-charcoal"></span> Products
                </p>
                <div class="flex items-center gap-3">
                    <x-ui.filter-drawer />
                    <x-ui.sort-dropdown />
                </div>
            </div>

            <div class="mt-8 flex gap-10">
                <x-ui.filter-sidebar />

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

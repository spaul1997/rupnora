@php
    $meta = collect($products)->map(fn ($p, $i) => [
        'i' => $i, 'category' => $p['category'], 'type' => $p['type'], 'metal' => $p['metal'], 'purity' => $p['purity'],
        'gender' => $p['gender'], 'occasion' => $p['occasion'], 'price' => $p['price'],
        'rating' => $p['rating'], 'reviews' => $p['reviews_count'], 'in_stock' => $p['in_stock'],
        'is_new' => $p['is_new'], 'is_bestseller' => $p['is_bestseller'],
        'discount' => $p['mrp'] > $p['price'] ? round((($p['mrp'] - $p['price']) / $p['mrp']) * 100) : 0,
    ])->values();
@endphp

<x-layouts.app title="Search">
    <div
        x-data="{
            sort: 'recommended',
            filters: { category: [], type: [], metal: [], purity: [], gender: [], occasion: [], rating: [], availability: [], flags: [], priceMin: 0, priceMax: 500000 },
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
    >
        <div class="container-luxe pt-6">
            <x-ui.breadcrumb :trail="[['label' => 'Search Results']]" />
        </div>

        <div class="container-luxe py-6">
            <div class="mx-auto max-w-xl">
                <x-ui.search-bar large />
            </div>
        </div>

        @if ($query === '')
            <div class="container-luxe pb-20">
                <x-ui.empty-state icon="search" title="Start typing to search" description="Search for rings, necklaces, gold jewellery, and more." />
            </div>
        @else
            <div class="container-luxe pb-20">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-line pb-5">
                    <p class="text-sm text-muted">
                        <span x-text="visibleCount" class="font-semibold text-charcoal"></span> results for
                        <span class="font-semibold text-charcoal">&ldquo;{{ $query }}&rdquo;</span>
                    </p>
                    <div class="flex items-center gap-3">
                        <x-ui.filter-drawer />
                        <x-ui.sort-dropdown />
                    </div>
                </div>

                <div class="mt-8 flex gap-10">
                    <x-ui.filter-sidebar />

                    <div class="min-w-0 flex-1">
                        <div x-show="visibleCount === 0" x-cloak>
                            <x-ui.empty-state icon="search" title="No results found" description="We couldn't find anything matching your search. Try a different keyword or browse our categories." action-label="Browse Collections" :action-url="route('collections.index')" />
                        </div>

                        <div x-show="visibleCount > 0" x-cloak class="grid grid-cols-2 gap-x-3 gap-y-6 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                            @foreach ($products as $i => $product)
                                <div x-show="matches(meta[{{ $i }}])" :style="`order: ${rank(meta[{{ $i }}]) + 100000}`">
                                    <x-ui.product-card :product="$product" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>

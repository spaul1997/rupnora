@props([
    'autofocus' => false,
    'large' => false,
])

@php
    $searchProducts = collect(\App\Support\StorefrontCatalog::products())->map(fn ($p) => [
        'id' => $p['id'], 'slug' => $p['slug'] ?? $p['id'], 'name' => $p['name'], 'category' => $p['category'], 'art' => $p['art'], 'price' => $p['price'],
    ])->values()->all();
    $searchCategories = collect(\App\Support\StorefrontCatalog::categories())->map(fn ($c) => ['slug' => $c['slug'], 'name' => $c['name']])->values()->all();
@endphp

<div
    x-data="{
        query: '',
        products: {{ Illuminate\Support\Js::from($searchProducts) }},
        categories: {{ Illuminate\Support\Js::from($searchCategories) }},
        recent: ['Diamond ring', 'Gold bangles', 'Silver earrings'],
        popular: ['Wedding rings', 'Gold chains', 'Diamond studs', 'Bridal sets'],
        get results() {
            if (this.query.length < 2) return [];
            const q = this.query.toLowerCase();
            return this.products.filter(p => p.name.toLowerCase().includes(q) || p.category.includes(q)).slice(0, 6);
        },
        get matchingCategories() {
            if (this.query.length < 2) return [];
            const q = this.query.toLowerCase();
            return this.categories.filter(c => c.name.toLowerCase().includes(q)).slice(0, 4);
        }
    }"
    class="relative w-full"
>
    <div class="relative">
        <svg class="pointer-events-none absolute left-4 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" stroke-linecap="round" /></svg>
        <input
            type="text"
            x-model="query"
            @if($autofocus) autofocus @endif
            placeholder="Search for rings, necklaces, gold jewellery..."
            class="input-luxe {{ $large ? '!py-4 !pl-11 !text-base' : '!py-2.5 !pl-11' }} !rounded-full"
        >
        <button x-show="query.length > 0" x-cloak @click="query = ''" class="absolute right-4 top-1/2 -translate-y-1/2 text-muted hover:text-charcoal">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg>
        </button>
    </div>

    <div x-cloak x-show="query.length > 0" x-transition class="absolute inset-x-0 top-full z-30 mt-2 max-h-[70vh] overflow-y-auto rounded-xl border border-line bg-paper p-4 shadow-lift">
        <template x-if="query.length < 2">
            <div class="space-y-5">
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted">Recent Searches</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="term in recent" :key="term">
                            <button @click="query = term" class="rounded-full border border-line px-3 py-1.5 text-xs text-charcoal hover:border-champagne-dark" x-text="term"></button>
                        </template>
                    </div>
                </div>
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted">Popular Searches</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="term in popular" :key="term">
                            <button @click="query = term" class="rounded-full bg-beige px-3 py-1.5 text-xs text-charcoal-soft hover:bg-champagne-light">·<span x-text="term"></span></button>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="query.length >= 2">
            <div>
                <template x-if="results.length === 0 && matchingCategories.length === 0">
                    <p class="py-6 text-center text-sm text-muted">No results for "<span x-text="query"></span>". Try a different keyword.</p>
                </template>

                <div x-show="matchingCategories.length > 0" class="mb-4">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted">Categories</p>
                    <template x-for="cat in matchingCategories" :key="cat.slug">
                        <a :href="'/category/' + cat.slug" class="block rounded-lg px-2 py-2 text-sm text-charcoal hover:bg-ivory-soft" x-text="cat.name"></a>
                    </template>
                </div>

                <div x-show="results.length > 0">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted">Products</p>
                    <template x-for="p in results" :key="p.id">
                        <a :href="'/product/' + p.slug" class="flex items-center gap-3 rounded-lg px-2 py-2 hover:bg-ivory-soft">
                            <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-lg bg-beige text-champagne-dark">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="8" /></svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm text-charcoal" x-text="p.name"></span>
                                <span class="block text-xs text-muted" x-text="'₹' + p.price.toLocaleString('en-IN')"></span>
                            </span>
                        </a>
                    </template>
                </div>

                <a :href="'/search?q=' + query" x-show="query.length >= 2" class="mt-3 block rounded-lg border border-line py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-charcoal hover:border-champagne-dark">
                    View All Results
                </a>
            </div>
        </template>
    </div>
</div>

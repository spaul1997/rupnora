@props([
    'options' => [
        'recommended' => 'Recommended',
        'newest' => 'Newest',
        'price_low' => 'Price: Low to High',
        'price_high' => 'Price: High to Low',
        'best_selling' => 'Best Selling',
        'rating' => 'Highest Rated',
        'discount' => 'Discount',
    ],
    'model' => 'sort',
])

<div x-data="{ open: false, opts: {{ Illuminate\Support\Js::from($options) }} }" class="relative">
    <button @click="open = !open" type="button" class="flex items-center gap-2 rounded-full border border-line bg-paper px-4 py-2.5 text-sm text-charcoal transition-colors hover:border-champagne-dark">
        <span class="text-muted">Sort:</span>
        <span class="font-medium" x-text="opts[{{ $model }}]"></span>
        <svg class="h-3.5 w-3.5 text-muted transition-transform" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
    </button>
    <div x-cloak x-show="open" @click.outside="open = false" x-transition class="absolute right-0 z-20 mt-2 w-56 rounded-xl border border-line bg-paper p-1.5 shadow-lift">
        <template x-for="(label, key) in opts" :key="key">
            <button type="button" @click="{{ $model }} = key; open = false" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm hover:bg-ivory-soft" :class="{{ $model }} === key ? 'text-champagne-dark font-medium' : 'text-charcoal'">
                <span x-text="label"></span>
                <svg x-show="{{ $model }} === key" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </button>
        </template>
    </div>
</div>

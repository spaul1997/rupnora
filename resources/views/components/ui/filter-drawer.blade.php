@props([
    'model' => 'filters',
    'sections' => null,
    'priceMin' => 0,
    'priceMax' => 500000,
])

<button type="button" @click="$store.ui.filterDrawerOpen = true" class="flex items-center gap-2 rounded-full border border-line bg-paper px-4 py-2.5 text-sm font-medium text-charcoal lg:hidden">
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M7 12h10M10 18h4" stroke-linecap="round" /></svg>
    Filters
</button>

<div x-cloak x-show="$store.ui.filterDrawerOpen" x-transition.opacity class="fixed inset-0 z-[70] bg-charcoal/50 lg:hidden" @click.self="$store.ui.filterDrawerOpen = false"></div>

<div
    x-cloak
    x-show="$store.ui.filterDrawerOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-y-full"
    x-transition:enter-end="translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-y-0"
    x-transition:leave-end="translate-y-full"
    class="fixed inset-x-0 bottom-0 z-[75] max-h-[85vh] rounded-t-2xl bg-paper p-5 pb-8 lg:hidden overflow-y-auto"
>
    <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-line"></div>
    <div class="mb-4 flex items-center justify-between">
        <h3 class="font-display text-lg text-charcoal">Filters</h3>
        <button @click="$store.ui.filterDrawerOpen = false" class="icon-btn"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg></button>
    </div>
    <x-ui.filter-panel :model="$model" :sections="$sections" :price-min="$priceMin" :price-max="$priceMax" />
    <button type="button" @click="$store.ui.filterDrawerOpen = false" class="btn-primary mt-6 w-full">Show Results</button>
</div>

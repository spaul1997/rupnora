@props([
    'model' => 'filters',
    'sections' => null,
    'priceMin' => 0,
    'priceMax' => 500000,
])

<aside class="hidden w-full max-w-[260px] flex-shrink-0 lg:block">
    <div class="sticky top-28 rounded-2xl border border-line bg-paper p-5">
        <x-ui.filter-panel :model="$model" :sections="$sections" :price-min="$priceMin" :price-max="$priceMax" />
    </div>
</aside>

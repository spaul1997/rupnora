@props(['model' => 'filters'])

<aside class="hidden w-full max-w-[260px] flex-shrink-0 lg:block">
    <div class="sticky top-28 rounded-2xl border border-line bg-paper p-5">
        <x-ui.filter-panel :model="$model" />
    </div>
</aside>

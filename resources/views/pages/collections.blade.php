<x-layouts.app :title="$title">
    <div class="container-luxe pt-6">
        <x-ui.breadcrumb :trail="[['label' => 'Collections']]" />
    </div>

    <div class="container-luxe py-8 text-center sm:py-10">
        <span class="eyebrow">Curated Edits</span>
        <h1 class="font-display mt-2 text-4xl text-charcoal sm:text-5xl">Our Collections</h1>
        <p class="mx-auto mt-3 max-w-xl text-sm text-muted sm:text-base">Thoughtfully curated edits, from everyday essentials to once-in-a-lifetime pieces.</p>
    </div>

    <div class="container-luxe pb-20">
        @if (count($collections) === 0)
            <x-ui.empty-state icon="box" title="No collections are live yet" description="Collections added in admin will appear here once they are marked active." action-label="Explore Categories" :action-url="route('categories.index')" />
        @else
            <div class="flex flex-wrap justify-center gap-4">
                @foreach ($collections as $collection)
                    <div class="w-[calc(50%-0.5rem)] sm:w-[calc(33.333%-0.667rem)] lg:w-[calc(25%-0.75rem)]">
                        <x-ui.collection-card :collection="$collection" />
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>

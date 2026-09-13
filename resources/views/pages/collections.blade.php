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
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($collections as $collection)
                <x-ui.collection-card :collection="$collection" />
            @endforeach
        </div>
    </div>
</x-layouts.app>

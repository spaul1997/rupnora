<x-layouts.app :title="$title">
    <div class="container-luxe pt-6">
        <x-ui.breadcrumb :trail="[['label' => 'Categories']]" />
    </div>

    <div class="container-luxe py-8 text-center sm:py-10">
        <span class="eyebrow">Explore</span>
        <h1 class="font-display mt-2 text-4xl text-charcoal sm:text-5xl">Shop by Category</h1>
        <p class="mx-auto mt-3 max-w-xl text-sm text-muted sm:text-base">Explore all our jewellery categories and find your next favourite piece.</p>
    </div>

    <div class="container-luxe pb-20">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
            @foreach ($categories as $category)
                <x-ui.category-card :category="$category" />
            @endforeach
        </div>
    </div>
</x-layouts.app>

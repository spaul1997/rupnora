@props([
    'category',
    'size' => 'md',
])

<a href="{{ route('category.show', $category['slug']) }}" class="group block">
    <div class="relative overflow-hidden rounded-lg border border-line">
        <x-ui.product-art :art="$category['art']" class="aspect-square transition-transform duration-700 ease-out group-hover:scale-105" />
        <div class="absolute inset-0 bg-gradient-to-t from-charcoal/55 via-charcoal/0 to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 p-2.5 sm:p-3">
            <h3 class="font-display text-[13px] leading-tight text-ivory sm:text-[15px]">{{ $category['name'] }}</h3>
            @if ($size !== 'sm')
                <p class="mt-0.5 hidden text-[10px] text-ivory/80 sm:block">{{ $category['blurb'] }}</p>
            @endif
        </div>
    </div>
</a>

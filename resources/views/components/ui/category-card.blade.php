@props([
    'category',
    'size' => 'md',
])

<a href="{{ route('category.show', $category['slug']) }}" class="group block overflow-hidden rounded-lg border border-line bg-paper transition-colors hover:border-champagne">
    <div class="overflow-hidden">
        @if (! empty($category['image']))
            <x-ui.optimized-image :src="$category['image']" alt="" sizes="(min-width: 1024px) 13vw, (min-width: 640px) 25vw, 33vw" class="aspect-square w-full object-cover transition-transform duration-700 ease-out group-hover:scale-105" />
        @else
            <x-ui.product-art :art="$category['art']" class="aspect-square transition-transform duration-700 ease-out group-hover:scale-105" />
        @endif
    </div>
    <!-- <div class="px-2.5 py-2 text-center sm:px-3 sm:py-2.5">
        <h3 class="text-xs font-medium leading-snug text-charcoal sm:text-sm">{{ $category['name'] }}</h3>
    </div> -->
</a>

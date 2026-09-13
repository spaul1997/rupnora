@props([
    'collection',
])

<a href="{{ route('collection.show', $collection['slug']) }}" class="group relative block overflow-hidden rounded-lg border border-line">
    <x-ui.product-art :art="$collection['art']" class="aspect-[4/3] transition-transform duration-700 ease-out group-hover:scale-105" />
    <div class="absolute inset-0 bg-gradient-to-t from-charcoal/70 via-charcoal/10 to-transparent"></div>
    <div class="absolute inset-x-0 bottom-0 p-3.5 sm:p-4">
        <span class="text-[9.5px] font-semibold uppercase tracking-wide text-champagne-light">{{ $collection['tag'] }}</span>
        <h3 class="mt-1 font-display text-base leading-tight text-ivory sm:text-lg">{{ $collection['name'] }}</h3>
        <p class="mt-0.5 line-clamp-1 text-[11px] text-ivory/80">{{ $collection['blurb'] }}</p>
        <span class="mt-2 inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wide text-ivory">
            Explore
            <svg class="h-3 w-3 transition-transform duration-300 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
        </span>
    </div>
</a>

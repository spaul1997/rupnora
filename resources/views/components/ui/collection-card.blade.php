@props([
    'collection',
])

@php
    $logo = $collection['logo'] ?? $collection['image'] ?? null;
@endphp

<a href="{{ route('collection.show', $collection['slug']) }}" class="group relative flex aspect-square w-full items-center justify-center overflow-hidden rounded-lg border border-line bg-white">
    @if ($logo)
        <x-ui.optimized-image :src="$logo" alt="{{ $collection['name'] }}" sizes="(min-width: 1024px) 16vw, (min-width: 640px) 25vw, 45vw" class="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-105" />
    @else
        <x-ui.product-art :art="$collection['art']" class="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-105" />
    @endif
    <div class="absolute inset-0 bg-gradient-to-t from-charcoal/70 via-charcoal/10 to-transparent"></div>
    <div class="absolute inset-x-0 bottom-0 p-2.5 sm:p-3">
        <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wide text-ivory">
            Explore
            <svg class="h-3 w-3 transition-transform duration-300 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
        </span>
    </div>
</a>

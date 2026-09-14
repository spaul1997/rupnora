@props([
    'collection',
])

@php
    $logo = $collection['logo'] ?? $collection['image'] ?? null;
    $blurb = $collection['blurb'] ?? null;
    $tag = $collection['tag'] ?? null;
@endphp

<a href="{{ route('collection.show', $collection['slug']) }}" class="group relative block overflow-hidden rounded-lg border border-line bg-paper transition-colors hover:border-champagne">
    <div class="relative aspect-square overflow-hidden">
    @if ($logo)
        <x-ui.optimized-image :src="$logo" alt="{{ $collection['name'] }}" sizes="(min-width: 1024px) 16vw, (min-width: 640px) 25vw, 45vw" class="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-105" />
    @else
        <x-ui.product-art :art="$collection['art']" class="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-105" />
    @endif
        <div class="absolute inset-0 bg-gradient-to-t from-charcoal/35 via-transparent to-transparent"></div>
        @if ($tag)
            <span class="absolute left-2.5 top-2.5 rounded-full bg-paper/90 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-charcoal shadow-card">
                {{ $tag }}
            </span>
        @endif
    </div>

    <div class="p-3 sm:p-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h3 class="truncate font-display text-base text-charcoal sm:text-lg">{{ $collection['name'] }}</h3>
                @if ($blurb)
                    <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-muted sm:text-sm">{{ $blurb }}</p>
                @endif
            </div>
            <span class="mt-1 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-beige text-champagne-dark transition-transform duration-300 group-hover:translate-x-0.5">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </span>
        </div>
    </div>
</a>

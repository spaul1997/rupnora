@props([
    'collection',
])

@php
    $logo = $collection['logo'] ?? $collection['image'] ?? null;
    $blurb = $collection['blurb'] ?? null;
    $tag = $collection['tag'] ?? null;
    $mobileTag = $tag && preg_match('/^\d+/', $tag, $matches) ? $matches[0] : $tag;
@endphp

<a href="{{ route('collection.show', $collection['slug']) }}" data-motion-card="collection" class="group relative flex h-full flex-col overflow-hidden rounded-lg border border-line bg-paper transition-colors hover:border-champagne">
    <div class="relative aspect-square shrink-0 overflow-hidden">
    @if ($logo)
        <x-ui.optimized-image :src="$logo" alt="{{ $collection['name'] }}" sizes="(min-width: 1024px) 16vw, (min-width: 640px) 25vw, 45vw" class="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-105" />
    @else
        <x-ui.product-art :art="$collection['art']" class="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-105" />
    @endif
        <div class="absolute inset-0 bg-gradient-to-t from-charcoal/35 via-transparent to-transparent"></div>
        @if ($tag)
            <span class="absolute left-2.5 top-2.5 rounded-full bg-paper/90 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-charcoal shadow-card">
                <span class="sm:hidden">{{ $mobileTag }}</span>
                <span class="hidden sm:inline">{{ $tag }}</span>
            </span>
        @endif
    </div>

    <div class="flex flex-1 p-2.5 sm:p-4">
        <div class="flex w-full items-start justify-between gap-2 sm:gap-3">
            <div class="min-w-0">
                <h3 class="line-clamp-2 font-display text-[12.5px] leading-snug text-charcoal sm:text-lg sm:leading-normal">{{ $collection['name'] }}</h3>
                @if ($blurb)
                    <p class="mt-1 hidden text-xs leading-relaxed text-muted sm:line-clamp-2 sm:text-sm">{{ $blurb }}</p>
                @endif
            </div>
            <span class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-beige text-champagne-dark transition-transform duration-300 group-hover:translate-x-0.5 sm:mt-1 sm:h-7 sm:w-7">
                <svg class="h-3 w-3 sm:h-3.5 sm:w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </span>
        </div>
    </div>
</a>

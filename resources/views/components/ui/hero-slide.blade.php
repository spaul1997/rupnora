@props([
    'eyebrow' => 'New Collection',
    'heading' => 'Jewellery Crafted for Every Story',
    'subheading' => "Discover timeless designs created to celebrate life's most precious moments.",
    'primaryLabel' => 'Shop Collection',
    'primaryUrl' => '#',
    'secondaryLabel' => 'Explore New Arrivals',
    'secondaryUrl' => '#',
    'image' => null,
    'mobileImage' => null,
    'tone' => 1,
])

@php
    $bgs = [
        1 => 'from-beige via-ivory to-champagne-light/70',
        2 => 'from-champagne-light/80 via-paper to-beige',
        3 => 'from-ivory-soft via-beige/70 to-champagne-light/60',
    ];
    $bg = $bgs[$tone] ?? $bgs[1];
@endphp

<div class="relative overflow-hidden bg-gradient-to-br {{ $bg }}">
    @if ($image)
        <x-ui.optimized-image :src="$image" :mobile-src="$mobileImage" alt="" sizes="100vw" loading="eager" fetchpriority="high" class="absolute inset-0 h-full w-full object-cover" />
    @else
        <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 22px 22px; color: var(--color-charcoal);"></div>

        <svg class="pointer-events-none absolute -right-24 top-1/2 hidden h-[130%] w-[70%] -translate-y-1/2 text-champagne-dark/[0.12] sm:block lg:-right-10 lg:w-[50%]" viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="0.8">
            <circle cx="100" cy="130" r="55" />
            <path d="M100 75 L118 45 L100 22 L82 45 Z" />
            <circle cx="100" cy="130" r="38" />
            <circle cx="60" cy="60" r="14" />
            <circle cx="150" cy="70" r="10" />
        </svg>
    @endif

    <div class="container-luxe relative flex min-h-[68vh] flex-col justify-center py-20 sm:min-h-[76vh]">
        @unless ($image)
            <div class="max-w-xl" x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 80)">
                <span class="eyebrow inline-block" :class="shown && 'animate-fade-up'">{{ $eyebrow }}</span>
                <h1 class="font-display mt-4 text-[2.4rem] leading-[1.08] text-charcoal sm:text-6xl lg:text-[4rem]" :class="shown && 'animate-fade-up'" style="animation-delay: 80ms">
                    {{ $heading }}
                </h1>
                <p class="mt-5 max-w-md text-[15px] leading-relaxed text-muted sm:text-base" :class="shown && 'animate-fade-up'" style="animation-delay: 160ms">
                    {{ $subheading }}
                </p>
                @if (($primaryLabel && $primaryUrl) || ($secondaryLabel && $secondaryUrl))
                    <div class="mt-9 flex flex-wrap gap-4" :class="shown && 'animate-fade-up'" style="animation-delay: 240ms">
                        @if ($primaryLabel && $primaryUrl)
                            <a href="{{ $primaryUrl }}" class="btn-primary">{{ $primaryLabel }}</a>
                        @endif
                        @if ($secondaryLabel && $secondaryUrl)
                            <a href="{{ $secondaryUrl }}" class="btn-ghost">{{ $secondaryLabel }}</a>
                        @endif
                    </div>
                @endif
            </div>
        @endunless
    </div>
</div>

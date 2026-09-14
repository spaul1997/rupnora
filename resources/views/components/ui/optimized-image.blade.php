@props([
    'src' => null,
    'mobileSrc' => null,
    'alt' => '',
    'sizes' => '100vw',
    'loading' => 'lazy',
    'fetchpriority' => null,
])

@php
    $src = (string) $src;
    $mobileSrc = (string) $mobileSrc;
    $hasResponsiveVariants = preg_match('/^(.*)-lg\.webp$/', $src, $matches);
    $base = $hasResponsiveVariants ? $matches[1] : null;
    $avifStoragePath = $base ? ltrim(\Illuminate\Support\Str::after(parse_url($base.'-lg.avif', PHP_URL_PATH) ?: '', '/storage/'), '/') : null;
    $hasAvif = $avifStoragePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($avifStoragePath);
    $hasMobileResponsiveVariants = preg_match('/^(.*)-lg\.webp$/', $mobileSrc, $mobileMatches);
    $mobileBase = $hasMobileResponsiveVariants ? $mobileMatches[1] : null;
    $mobileAvifStoragePath = $mobileBase ? ltrim(\Illuminate\Support\Str::after(parse_url($mobileBase.'-lg.avif', PHP_URL_PATH) ?: '', '/storage/'), '/') : null;
    $hasMobileAvif = $mobileAvifStoragePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($mobileAvifStoragePath);
@endphp

@if ($src && ($hasResponsiveVariants || $mobileSrc))
    <picture>
        @if ($mobileSrc && $hasMobileResponsiveVariants)
            @if ($hasMobileAvif)
                <source media="(max-width: 639px)" type="image/avif" srcset="{{ $mobileBase }}-sm.avif 480w, {{ $mobileBase }}-md.avif 960w, {{ $mobileBase }}-lg.avif 1600w" sizes="{{ $sizes }}">
            @endif
            <source media="(max-width: 639px)" type="image/webp" srcset="{{ $mobileBase }}-sm.webp 480w, {{ $mobileBase }}-md.webp 960w, {{ $mobileBase }}-lg.webp 1600w" sizes="{{ $sizes }}">
        @elseif ($mobileSrc)
            <source media="(max-width: 639px)" srcset="{{ $mobileSrc }}">
        @endif
        @if ($hasAvif)
            <source type="image/avif" srcset="{{ $base }}-sm.avif 480w, {{ $base }}-md.avif 960w, {{ $base }}-lg.avif 1600w" sizes="{{ $sizes }}">
        @endif
        @if ($hasResponsiveVariants)
            <source type="image/webp" srcset="{{ $base }}-sm.webp 480w, {{ $base }}-md.webp 960w, {{ $base }}-lg.webp 1600w" sizes="{{ $sizes }}">
        @endif
        <img
            src="{{ $src }}"
            alt="{{ $alt }}"
            loading="{{ $loading }}"
            decoding="async"
            @if ($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
            {{ $attributes }}
        >
    </picture>
@elseif ($src)
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        loading="{{ $loading }}"
        decoding="async"
        @if ($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
        {{ $attributes }}
    >
@endif

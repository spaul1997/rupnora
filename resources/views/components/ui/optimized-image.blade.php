@props([
    'src' => null,
    'alt' => '',
    'sizes' => '100vw',
    'loading' => 'lazy',
    'fetchpriority' => null,
])

@php
    $src = (string) $src;
    $hasResponsiveVariants = preg_match('/^(.*)-lg\.webp$/', $src, $matches);
    $base = $hasResponsiveVariants ? $matches[1] : null;
    $avifStoragePath = $base ? ltrim(\Illuminate\Support\Str::after(parse_url($base.'-lg.avif', PHP_URL_PATH) ?: '', '/storage/'), '/') : null;
    $hasAvif = $avifStoragePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($avifStoragePath);
@endphp

@if ($src && $hasResponsiveVariants)
    <picture>
        @if ($hasAvif)
            <source type="image/avif" srcset="{{ $base }}-sm.avif 480w, {{ $base }}-md.avif 960w, {{ $base }}-lg.avif 1600w" sizes="{{ $sizes }}">
        @endif
        <source type="image/webp" srcset="{{ $base }}-sm.webp 480w, {{ $base }}-md.webp 960w, {{ $base }}-lg.webp 1600w" sizes="{{ $sizes }}">
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

@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'robots' => null,
    'image' => null,
    'ogType' => 'website',
    'schemaType' => 'WebPage',
    'schema' => [],
    'breadcrumbs' => [],
    'previousUrl' => null,
    'nextUrl' => null,
    'trackingProductId' => null,
])
@php
    $routeName = request()->route()?->getName();
    $seoTitle = \App\Support\Seo::pageTitle($title);
    $seoDescription = \App\Support\Seo::description($description, $routeName);
    $seoCanonical = \App\Support\Seo::absoluteUrl($canonical ?: url()->current());
    $seoImage = \App\Support\Seo::absoluteUrl($image) ?: asset('logo-tag.png');
    $seoRobots = $robots ?: (\App\Support\Seo::isIndexableRoute($routeName)
        ? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'
        : 'noindex, nofollow');
    $isIndexable = ! str_contains($seoRobots, 'noindex');
    $seoBreadcrumbs = collect($breadcrumbs)->map(fn ($crumb) => [
        'name' => $crumb['name'] ?? $crumb['label'] ?? '',
        'url' => \App\Support\Seo::absoluteUrl($crumb['url'] ?? null),
    ])->filter(fn ($crumb) => filled($crumb['name']))->values()->all();

    if ($isIndexable && $seoBreadcrumbs === [] && $routeName !== 'home') {
        $seoBreadcrumbs = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => trim((string) $title) ?: 'Rupnora', 'url' => $seoCanonical],
        ];
    }

    $extraSchema = isset($schema['@type']) ? [$schema] : array_values($schema);
    $seoGraph = $isIndexable
        ? \App\Support\Seo::graph(
            $seoTitle,
            $seoDescription,
            $seoCanonical,
            $seoImage,
            $schemaType,
            $seoBreadcrumbs,
            $extraSchema,
            $seoSettings,
        )
        : null;
@endphp
<!DOCTYPE html>
<html lang="en-IN" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f3f9">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- <meta name="robots" content="{{ $seoRobots }}"> -->
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <link rel="canonical" href="{{ $seoCanonical }}">
    @if ($previousUrl)
        <link rel="prev" href="{{ \App\Support\Seo::absoluteUrl($previousUrl) }}">
    @endif
    @if ($nextUrl)
        <link rel="next" href="{{ \App\Support\Seo::absoluteUrl($nextUrl) }}">
    @endif

    <meta property="og:locale" content="en_IN">
    <meta property="og:site_name" content="Rupnora">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta property="og:image:alt" content="{{ $title ?: 'Rupnora jewellery' }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">

    @if ($seoGraph)
        <script type="application/ld+json">{!! json_encode($seoGraph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @endif

    <link rel="icon" href="{{ asset('favicon.png') }}" sizes="any">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:500,600,700|inter:400,500,600,700" rel="stylesheet">

    <script>
        window.rupnoraInitialState = {
            cartCount: {{ \App\Support\ShoppingCart::count() }},
            wishlistIds: {{ Illuminate\Support\Js::from(\App\Support\ShoppingCart::wishlistIds()) }},
            socialProofItems: {{ Illuminate\Support\Js::from($socialProofItems ?? []) }},
            visitorTracking: {{ Illuminate\Support\Js::from([
                'endpoint' => route('visitor-tracking.store'),
                'routeName' => request()->route()?->getName(),
                'productId' => $trackingProductId,
            ]) }},
        };
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront flex min-h-screen flex-col bg-ivory font-sans text-charcoal antialiased" data-image-fallback="{{ asset('images/image-placeholder.svg') }}" x-data>

    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[999] focus:rounded-full focus:bg-charcoal focus:px-4 focus:py-2 focus:text-sm focus:text-ivory">
        Skip to content
    </a>

    <x-layout.header />
    <x-layout.mobile-menu />

    <main id="main-content" data-motion-page class="flex-1 pb-16 lg:pb-0">
        {{ $slot }}
    </main>

    <x-layout.footer />
    <x-layout.mobile-bottom-nav />
    <x-ui.toast-container />

</body>
</html>

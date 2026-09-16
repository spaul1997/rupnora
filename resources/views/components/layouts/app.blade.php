@props([
    'title' => null,
    'description' => null,
])
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f3f9">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' — Rupnora' : 'Rupnora — Everyday Style, Endless Sparkle' }}</title>
    <meta name="description" content="{{ $description ?? 'Discover certified gold, diamond and silver jewellery — rings, earrings, necklaces and bridal collections crafted for life\'s most precious moments.' }}">

    <link rel="icon" href="{{ asset('favicon.png') }}" sizes="any">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:500,600,700|inter:400,500,600,700" rel="stylesheet">

    <script>
        window.rupnoraInitialState = {
            cartCount: {{ \App\Support\ShoppingCart::count() }},
            wishlistIds: {{ Illuminate\Support\Js::from(\App\Support\ShoppingCart::wishlistIds()) }},
        };
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-ivory font-sans text-charcoal antialiased" x-data>

    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[999] focus:rounded-full focus:bg-charcoal focus:px-4 focus:py-2 focus:text-sm focus:text-ivory">
        Skip to content
    </a>

    <x-layout.header />
    <x-layout.mobile-menu />

    <main id="main-content" class="flex-1 pb-16 lg:pb-0">
        {{ $slot }}
    </main>

    <x-layout.footer />
    <x-layout.mobile-bottom-nav />
    <x-ui.toast-container />

</body>
</html>

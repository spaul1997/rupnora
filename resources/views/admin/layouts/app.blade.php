<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#231535">
    <title>@yield('title', 'Dashboard') — Rupnora Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('favicon.png') }}" sizes="any">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:500,600,700|inter:400,500,600,700" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="admin-scope bg-gray-50 font-sans text-gray-900 antialiased" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">
        {{-- Desktop sidebar --}}
        <aside class="hidden w-64 flex-shrink-0 lg:block">
            @include('admin.partials.sidebar')
        </aside>

        {{-- Mobile off-canvas sidebar --}}
        <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden" @click.self="sidebarOpen = false"></div>
        <div
            x-cloak
            x-show="sidebarOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 z-50 w-72 lg:hidden"
        >
            <div class="absolute right-0 top-4 -mr-11">
                <button @click="sidebarOpen = false" class="flex h-8 w-8 items-center justify-center rounded-full bg-charcoal text-ivory">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg>
                </button>
            </div>
            @include('admin.partials.sidebar')
        </div>

        {{-- Main --}}
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            @include('admin.partials.topbar')

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                @if (session('success'))
                    <div class="mb-6 flex items-center gap-2 rounded-xl bg-success/10 px-4 py-3 text-sm text-success">
                        <svg class="h-4.5 w-4.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-6 flex items-center gap-2 rounded-xl bg-error/10 px-4 py-3 text-sm text-error">
                        <svg class="h-4.5 w-4.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path d="M12 8v4m0 4h.01" stroke-linecap="round" /></svg>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="flex-shrink-0 border-t border-gray-200 bg-white px-6 py-3 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} Rupnora Admin. All rights reserved.
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

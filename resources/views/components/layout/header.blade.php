@php
    $headerCategories = \App\Support\StorefrontCatalog::headerCategories();
    $navItems = collect([
            ['label' => 'Home', 'url' => route('home'), 'children' => []],
        ])
        ->merge(collect($headerCategories)
        ->map(fn (array $category) => [
            'label' => $category['name'],
            'url' => route('category.show', $category['slug']),
            'children' => $category['children'] ?? [],
        ]))
        ->merge([
            ['label' => 'Collections', 'url' => route('collections.index'), 'children' => []],
            ['label' => 'New Arrivals', 'url' => route('new-arrivals'), 'children' => []],
            ['label' => 'Best Sellers', 'url' => route('best-sellers'), 'children' => []],
        ])
        ->values()
        ->all();
@endphp

<div x-data="{ scrolled: false, activeMenu: null, }" @scroll.window="scrolled = window.scrollY > 8" class="sticky top-0 z-50">
    {{-- Announcement bar --}}
    <div class="bg-charcoal text-ivory">
        <div class="container-luxe flex flex-col items-center justify-center gap-0.5 py-1 text-center text-[10.5px] tracking-wide sm:flex-row sm:gap-4 sm:text-[11px]">
            <span>Free Shipping on Orders Above Rs. 2,999</span>
            <span class="hidden h-3 w-px bg-ivory/25 sm:block"></span>
            <span>Certified Jewellery &middot; Easy Returns &middot; Secure Payments</span>
        </div>
    </div>

    {{-- Main header --}}
    <div class="border-b border-line bg-ivory/95 backdrop-blur transition-shadow duration-300" :class="scrolled && 'shadow-soft'">
        <div class="container-luxe flex items-center gap-2.5 py-1.5 lg:py-1">
            <button @click="$store.ui.mobileMenuOpen = true" class="icon-btn -ml-2 h-9 w-9 lg:hidden" aria-label="Open menu">
                <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" /></svg>
            </button>

            <a href="{{ route('home') }}" class="mr-2 flex shrink-0 items-center gap-1.5 border-r border-line pr-3 lg:mr-6 lg:pr-5">
                <img src="{{ asset('logo.png') }}" alt="Rupnora" class="h-14 w-auto object-contain sm:h-16">
            </a>

            <div class="hidden flex-1 lg:block lg:max-w-md">
                <x-ui.search-bar />
            </div>

            <div class="ml-auto flex items-center gap-1 sm:gap-2">
                <button @click="$store.ui.searchOpen = true" class="icon-btn h-9 w-9 lg:hidden" aria-label="Search">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" stroke-linecap="round" /></svg>
                </button>

                <div class="relative hidden sm:block" x-data="{ open: false }">
                    <button @click="open = !open" class="icon-btn h-9 w-9" aria-label="Account">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="4" /><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke-linecap="round" /></svg>
                    </button>
                    <div x-cloak x-show="open" @click.outside="open = false" x-transition class="absolute right-0 z-30 mt-2 w-52 rounded-xl border border-line bg-paper p-2 shadow-lift">
                        @if (auth()->check() && auth()->user()->role === 'customer')
                            <p class="truncate px-3 py-2 text-xs text-muted">{{ auth()->user()->name }}</p>
                            <div class="my-1.5 border-t border-line"></div>
                            <a href="{{ route('account.dashboard') }}" class="block rounded-lg px-3 py-2 text-sm text-charcoal hover:bg-ivory-soft">My Account</a>
                            <a href="{{ route('account.orders') }}" class="block rounded-lg px-3 py-2 text-sm text-muted hover:bg-ivory-soft">My Orders</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-sm text-muted hover:bg-ivory-soft">Sign Out</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 text-sm text-charcoal hover:bg-ivory-soft">Sign In</a>
                            <a href="{{ route('register') }}" class="block rounded-lg px-3 py-2 text-sm text-charcoal hover:bg-ivory-soft">Create Account</a>
                        @endif
                    </div>
                </div>

                <a href="{{ route('account.wishlist') }}" class="icon-btn relative hidden h-9 w-9 sm:inline-flex" aria-label="Wishlist">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 20.5s-7.5-4.9-10.1-9.6C.3 7.9 1.6 4.5 4.9 3.6c2-.5 4 .3 5.1 2 .3.4.7.4 1 0 1.1-1.7 3.1-2.5 5.1-2 3.3.9 4.6 4.3 3 7.3-2.6 4.7-10.1 9.6-10.1 9.6z" stroke-linejoin="round" /></svg>
                    <span x-cloak x-show="$store.ui.wishlistIds.length > 0" x-text="$store.ui.wishlistIds.length" class="absolute -right-0.5 -top-0.5 flex h-4.5 w-4.5 items-center justify-center rounded-full bg-champagne-dark text-[10px] font-semibold text-ivory"></span>
                </a>

                <a href="{{ route('cart') }}" class="icon-btn relative h-9 w-9" aria-label="Cart">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 7h13l1.5 12h-16z" stroke-linejoin="round" /><path d="M8 7V5.5a3 3 0 016 0V7" /></svg>
                    <span x-cloak x-show="$store.ui.cartCount > 0" x-text="$store.ui.cartCount" class="absolute -right-0.5 -top-0.5 flex h-4.5 w-4.5 items-center justify-center rounded-full bg-champagne-dark text-[10px] font-semibold text-ivory"></span>
                </a>
            </div>
        </div>

        {{-- Desktop nav --}}
        <nav class="container-luxe hidden border-t border-line lg:block">
            <ul class="flex items-center justify-center gap-5 py-2 xl:gap-7">
                @foreach ($navItems as $i => $item)
                    <li
                        @if(! empty($item['children']))
                            @mouseenter="activeMenu = {{ $i }}" @mouseleave="activeMenu = null"
                        @endif
                        class="relative"
                    >
                        <a href="{{ $item['url'] }}" class="flex items-center gap-1 text-[12px] font-medium uppercase tracking-wide text-charcoal-soft transition-colors hover:text-champagne-dark">
                            {{ $item['label'] }}
                            @if (! empty($item['children']))
                                <svg class="h-3 w-3 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            @endif
                        </a>

                        @if (! empty($item['children']))
                            <div
                                x-cloak
                                x-show="activeMenu === {{ $i }}"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute left-0 top-full z-40 mt-2 w-56 rounded-xl border border-line bg-paper p-2 shadow-lift"
                            >
                                <a href="{{ $item['url'] }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-charcoal hover:bg-ivory-soft">View all {{ $item['label'] }}</a>
                                <div class="my-1.5 border-t border-line"></div>
                                @foreach ($item['children'] as $child)
                                    <a href="{{ route('category.show', $child['slug']) }}" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm text-charcoal hover:bg-ivory-soft">
                                        <span>{{ $child['name'] }}</span>
                                        <span class="text-xs text-muted">{{ $child['count'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>

    {{-- Mobile search overlay --}}
    <div x-cloak x-show="$store.ui.searchOpen" x-transition.opacity class="fixed inset-0 z-[90] bg-ivory p-4 lg:hidden">
        <div class="flex items-center gap-3">
            <div class="flex-1"><x-ui.search-bar autofocus /></div>
            <button @click="$store.ui.searchOpen = false" class="icon-btn"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg></button>
        </div>
    </div>
</div>

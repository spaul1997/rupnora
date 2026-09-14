@php
    $mobileCategories = \App\Support\StorefrontCatalog::headerCategories();
@endphp

<div x-cloak x-show="$store.ui.mobileMenuOpen" x-transition.opacity class="fixed inset-0 z-[95] bg-charcoal/50 lg:hidden" @click.self="$store.ui.mobileMenuOpen = false"></div>

<div
    x-cloak
    x-show="$store.ui.mobileMenuOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed inset-y-0 left-0 z-[100] w-[86%] max-w-sm overflow-y-auto bg-paper lg:hidden"
    x-data="{ openSection: null, openCategory: null }"
>
    <div class="flex items-center justify-between border-b border-line px-5 py-4">
        <a href="{{ route('home') }}" class="flex items-center">
            <img src="{{ asset('logo.png') }}" alt="Rupnora" class="h-10 w-auto object-contain">
        </a>
        <button @click="$store.ui.mobileMenuOpen = false" class="icon-btn" aria-label="Close menu">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg>
        </button>
    </div>

    <div class="flex items-center gap-3 border-b border-line px-5 py-4">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-beige text-champagne-dark">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="4" /><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke-linecap="round" /></svg>
        </div>
        <div class="flex gap-3 text-sm">
            <a href="{{ route('login') }}" class="font-semibold text-charcoal">Sign In</a>
            <span class="text-line">/</span>
            <a href="{{ route('register') }}" class="text-muted">Create Account</a>
        </div>
    </div>

    <nav class="px-2 py-3">
        <a href="{{ route('home') }}" class="block rounded-lg px-3 py-3 text-[15px] font-medium text-charcoal hover:bg-ivory-soft">Home</a>

        <button @click="openSection = openSection === 'shop' ? null : 'shop'" class="flex w-full items-center justify-between rounded-lg px-3 py-3 text-[15px] font-medium text-charcoal hover:bg-ivory-soft">
            Shop by Category
            <svg class="h-4 w-4 text-muted transition-transform" :class="openSection === 'shop' && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
        </button>
        <div x-cloak x-show="openSection === 'shop'" x-collapse class="space-y-0.5 pb-2 pl-5">
            @foreach ($mobileCategories as $i => $cat)
                @if (! empty($cat['children']))
                    <button @click="openCategory = openCategory === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-sm text-muted hover:bg-ivory-soft hover:text-charcoal">
                        {{ $cat['name'] }}
                        <svg class="h-3.5 w-3.5 transition-transform" :class="openCategory === {{ $i }} && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </button>
                    <div x-cloak x-show="openCategory === {{ $i }}" x-collapse class="space-y-0.5 pl-4">
                        <a href="{{ route('category.show', $cat['slug']) }}" class="block rounded-lg px-3 py-2 text-xs font-medium text-charcoal hover:bg-ivory-soft">View all {{ $cat['name'] }}</a>
                        @foreach ($cat['children'] as $child)
                            <a href="{{ route('category.show', $child['slug']) }}" class="block rounded-lg px-3 py-2 text-xs text-muted hover:bg-ivory-soft hover:text-charcoal">{{ $child['name'] }}</a>
                        @endforeach
                    </div>
                @else
                    <a href="{{ route('category.show', $cat['slug']) }}" class="block rounded-lg px-3 py-2.5 text-sm text-muted hover:bg-ivory-soft hover:text-charcoal">{{ $cat['name'] }}</a>
                @endif
            @endforeach
        </div>

        <a href="{{ route('collections.index') }}" class="block rounded-lg px-3 py-3 text-[15px] font-medium text-charcoal hover:bg-ivory-soft">Collections</a>
        <a href="{{ route('new-arrivals') }}" class="block rounded-lg px-3 py-3 text-[15px] font-medium text-charcoal hover:bg-ivory-soft">New Arrivals</a>
        <a href="{{ route('best-sellers') }}" class="block rounded-lg px-3 py-3 text-[15px] font-medium text-charcoal hover:bg-ivory-soft">Best Sellers</a>

        <div class="my-2 border-t border-line"></div>

        <a href="{{ route('account.dashboard') }}" class="block rounded-lg px-3 py-3 text-sm text-muted hover:bg-ivory-soft">My Account</a>
        <a href="{{ route('account.orders') }}" class="block rounded-lg px-3 py-3 text-sm text-muted hover:bg-ivory-soft">My Orders</a>
        <a href="{{ route('account.wishlist') }}" class="block rounded-lg px-3 py-3 text-sm text-muted hover:bg-ivory-soft">Wishlist</a>
        <a href="{{ route('about') }}" class="block rounded-lg px-3 py-3 text-sm text-muted hover:bg-ivory-soft">Our Story</a>
        <a href="{{ route('careers') }}" class="block rounded-lg px-3 py-3 text-sm text-muted hover:bg-ivory-soft">Careers</a>
        <a href="{{ route('contact') }}" class="block rounded-lg px-3 py-3 text-sm text-muted hover:bg-ivory-soft">Contact Us</a>
    </nav>
</div>

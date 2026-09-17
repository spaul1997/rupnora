@props(['active' => 'dashboard'])

@php
    $navItems = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'url' => route('account.dashboard'), 'icon' => 'M4 11l8-7 8 7v9a1 1 0 01-1 1h-4v-6H9v6H5a1 1 0 01-1-1z'],
        ['key' => 'orders', 'label' => 'My Orders', 'url' => route('account.orders'), 'icon' => 'M3 7h13l1.5 12h-16z M8 7V5.5a3 3 0 016 0V7'],
        ['key' => 'wishlist', 'label' => 'Wishlist', 'url' => route('account.wishlist'), 'icon' => 'M12 20.5s-7.5-4.9-10.1-9.6C.3 7.9 1.6 4.5 4.9 3.6c2-.5 4 .3 5.1 2 .3.4.7.4 1 0 1.1-1.7 3.1-2.5 5.1-2 3.3.9 4.6 4.3 3 7.3-2.6 4.7-10.1 9.6-10.1 9.6z'],
        ['key' => 'addresses', 'label' => 'Saved Addresses', 'url' => route('account.addresses'), 'icon' => 'M12 21s-7-6.5-7-11.5A7 7 0 0112 2a7 7 0 017 7.5C19 14.5 12 21 12 21z'],
        ['key' => 'profile', 'label' => 'Profile', 'url' => route('account.profile'), 'icon' => 'M12 12a4 4 0 100-8 4 4 0 000 8zM4 20c0-4 3.6-7 8-7s8 3 8 7'],
        ['key' => 'change-password', 'label' => 'Change Password', 'url' => route('account.change-password'), 'icon' => 'M12 15v2m-5 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H7a2 2 0 00-2 2v6a2 2 0 002 2zM8 11V8a4 4 0 118 0v3'],
        ['key' => 'notifications', 'label' => 'Notifications', 'url' => route('account.notifications'), 'icon' => 'M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0m6 0H9'],
        ['key' => 'support', 'label' => 'Support', 'url' => route('account.support'), 'icon' => 'M18.4 15.5A8 8 0 105.6 4.6M12 8v4l2.5 2.5'],
    ];
@endphp

<div class="container-luxe py-8 sm:py-10">
    <div class="flex flex-col gap-8 lg:flex-row">
        {{-- Desktop sidebar --}}
        <aside class="hidden w-full max-w-[260px] flex-shrink-0 lg:block">
            <div class="sticky top-28 rounded-2xl border border-line bg-paper p-3">
                <nav class="space-y-1">
                    @foreach ($navItems as $item)
                        <a href="{{ $item['url'] }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-colors {{ $active === $item['key'] ? 'bg-charcoal text-ivory' : 'text-charcoal-soft hover:bg-ivory-soft' }}">
                            <svg class="h-4.5 w-4.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="{{ $item['icon'] }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                    <div class="my-1.5 border-t border-line"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-left text-sm text-error transition-colors hover:bg-error/5">
                            <svg class="h-4.5 w-4.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4m6 14l5-5-5-5m5 5H9" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            Logout
                        </button>
                    </form>
                </nav>
            </div>
        </aside>

        {{-- Mobile nav --}}
        <div class="-mx-5 flex snap-x gap-2 overflow-x-auto px-5 pb-1 lg:hidden">
            @foreach ($navItems as $item)
                <a href="{{ $item['url'] }}" class="flex flex-shrink-0 snap-start items-center gap-2 rounded-full border px-4 py-2 text-xs font-medium transition-colors {{ $active === $item['key'] ? 'border-charcoal bg-charcoal text-ivory' : 'border-line text-charcoal-soft' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
            @auth
                <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="rounded-full border border-line px-4 py-2 text-xs font-medium text-error">Logout</button>
                </form>
            @endauth
        </div>

        <div class="min-w-0 flex-1">
            @foreach (['success' => 'text-success', 'error' => 'text-error', 'warning' => 'text-champagne-dark'] as $key => $color)
                @if (session($key))
                    <div role="status" class="mb-6 rounded-xl border border-line bg-paper p-4 text-sm {{ $color }}">{{ session($key) }}</div>
                @endif
            @endforeach
            @if ($errors->any())
                <div role="alert" class="mb-6 rounded-xl border border-error/30 bg-paper p-4 text-sm text-error">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {{ $slot }}
        </div>
    </div>
</div>

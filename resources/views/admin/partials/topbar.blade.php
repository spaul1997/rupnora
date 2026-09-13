<header class="sticky top-0 z-30 flex h-16 flex-shrink-0 items-center gap-4 border-b border-gray-200 bg-white px-4 sm:px-6">
    <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 lg:hidden">
        <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" /></svg>
    </button>

    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-medium text-gray-500">Welcome back, {{ auth()->user()?->name }}</p>
    </div>

    <div class="flex items-center gap-1 sm:gap-2">
        {{-- Notifications --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="relative flex h-9 w-9 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100" aria-label="Notifications">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0m6 0H9" stroke-linecap="round" stroke-linejoin="round" /></svg>
                @if (count($adminNotifications ?? []) > 0)
                    <span class="absolute right-1.5 top-1.5 flex h-2 w-2 rounded-full bg-error"></span>
                @endif
            </button>
            <div x-cloak x-show="open" @click.outside="open = false" x-transition class="absolute right-0 z-30 mt-2 w-80 rounded-xl border border-gray-200 bg-white py-2 shadow-lg">
                <p class="border-b border-gray-100 px-4 pb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Notifications</p>
                @forelse ($adminNotifications ?? [] as $note)
                    <a href="{{ $note['url'] }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">{{ $note['label'] }}</a>
                @empty
                    <p class="px-4 py-6 text-center text-sm text-gray-400">You're all caught up.</p>
                @endforelse
            </div>
        </div>

        {{-- Profile --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center gap-2 rounded-full py-1 pl-1 pr-2.5 hover:bg-gray-100">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-beige text-sm font-semibold text-champagne-dark">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
                </span>
                <span class="hidden text-sm font-medium text-gray-700 sm:block">{{ auth()->user()?->name }}</span>
                <svg class="hidden h-3.5 w-3.5 text-gray-400 sm:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </button>
            <div x-cloak x-show="open" @click.outside="open = false" x-transition class="absolute right-0 z-30 mt-2 w-52 rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg">
                <p class="truncate px-3 py-2 text-xs text-gray-400">{{ auth()->user()?->email }}</p>
                <div class="my-1 border-t border-gray-100"></div>
                <a href="{{ route('admin.settings.edit') }}" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Admin Profile</a>
                <a href="{{ route('clear-cache') }}" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Clear Cache</a>
                <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50" target="_blank">View Storefront</a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-error hover:bg-red-50">Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>

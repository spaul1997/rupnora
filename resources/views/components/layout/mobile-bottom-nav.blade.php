<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-paper/95 backdrop-blur lg:hidden" style="padding-bottom: env(safe-area-inset-bottom);">
    <div class="grid grid-cols-5">
        <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 py-2.5 {{ request()->routeIs('home') ? 'text-champagne-dark' : 'text-muted' }}">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 11l8-7 8 7v9a1 1 0 01-1 1h-4v-6H9v6H5a1 1 0 01-1-1z" stroke-linejoin="round" /></svg>
            <span class="text-[10px] font-medium">Home</span>
        </a>
        <a href="{{ route('collections.index') }}" class="flex flex-col items-center gap-1 py-2.5 {{ request()->routeIs('collections.*') ? 'text-champagne-dark' : 'text-muted' }}">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="7" height="7" rx="1.5" /><rect x="13" y="4" width="7" height="7" rx="1.5" /><rect x="4" y="13" width="7" height="7" rx="1.5" /><rect x="13" y="13" width="7" height="7" rx="1.5" /></svg>
            <span class="text-[10px] font-medium">Categories</span>
        </a>
        <button @click="$store.ui.searchOpen = true" class="flex flex-col items-center gap-1 py-2.5 text-muted">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" stroke-linecap="round" /></svg>
            <span class="text-[10px] font-medium">Search</span>
        </button>
        <a href="{{ route('account.wishlist') }}" class="relative flex flex-col items-center gap-1 py-2.5 {{ request()->routeIs('account.wishlist') ? 'text-champagne-dark' : 'text-muted' }}">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 20.5s-7.5-4.9-10.1-9.6C.3 7.9 1.6 4.5 4.9 3.6c2-.5 4 .3 5.1 2 .3.4.7.4 1 0 1.1-1.7 3.1-2.5 5.1-2 3.3.9 4.6 4.3 3 7.3-2.6 4.7-10.1 9.6-10.1 9.6z" stroke-linejoin="round" /></svg>
            <span class="text-[10px] font-medium">Wishlist</span>
        </a>
        <a href="{{ route('account.dashboard') }}" class="flex flex-col items-center gap-1 py-2.5 {{ request()->routeIs('account.*') ? 'text-champagne-dark' : 'text-muted' }}">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="4" /><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke-linecap="round" /></svg>
            <span class="text-[10px] font-medium">Account</span>
        </a>
    </div>
</nav>

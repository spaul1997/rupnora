<x-layouts.app :title="$title">
    <x-account.shell active="dashboard">
        <div class="mb-8">
            <p class="text-sm text-muted">Welcome back,</p>
            <h1 class="font-display text-3xl text-charcoal sm:text-4xl">{{ $customer['name'] }}</h1>
        </div>

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Total Orders', 'value' => $totalOrders, 'icon' => 'M3 7h13l1.5 12h-16z'],
                ['label' => 'Active Orders', 'value' => $activeOrders, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'Wishlist Items', 'value' => $wishlistCount, 'icon' => 'M12 20.5s-7.5-4.9-10.1-9.6C.3 7.9 1.6 4.5 4.9 3.6c2-.5 4 .3 5.1 2 .3.4.7.4 1 0 1.1-1.7 3.1-2.5 5.1-2 3.3.9 4.6 4.3 3 7.3-2.6 4.7-10.1 9.6-10.1 9.6z'],
                ['label' => 'Saved Addresses', 'value' => $addressCount, 'icon' => 'M12 21s-7-6.5-7-11.5A7 7 0 0112 2a7 7 0 017 7.5C19 14.5 12 21 12 21z'],
            ] as $card)
                <div class="rounded-2xl border border-line p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-beige text-champagne-dark">
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="{{ $card['icon'] }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </div>
                    <p class="font-display mt-4 text-2xl text-charcoal">{{ $card['value'] }}</p>
                    <p class="mt-1 text-xs text-muted">{{ $card['label'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Profile completion --}}
        <div class="mt-8 rounded-2xl border border-line p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-charcoal">Profile Completion</p>
                <span class="text-sm font-semibold text-champagne-dark">{{ $profileCompletion }}%</span>
            </div>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-beige">
                <div class="h-full rounded-full bg-champagne-dark" style="width: {{ $profileCompletion }}%"></div>
            </div>
            <a href="{{ route('account.profile') }}" class="mt-2 inline-block text-xs text-champagne-dark hover:underline">Keep your contact details, birthday, photo and saved address up to date.</a>
        </div>

        <div class="mt-10 flex items-center justify-between">
            <h2 class="font-display text-xl text-charcoal">Recent Orders</h2>
            <a href="{{ route('account.orders') }}" class="link-underline text-sm font-medium text-charcoal">View All</a>
        </div>
        <div class="mt-5 space-y-5">
            @forelse ($orders as $order)
                <x-ui.order-card :order="$order" />
            @empty
                <p class="text-sm text-muted">You haven't placed any orders yet.</p>
            @endforelse
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div>
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-xl text-charcoal">Saved Address</h2>
                    <a href="{{ route('account.addresses') }}" class="link-underline text-sm font-medium text-charcoal">Manage</a>
                </div>
                <div class="mt-5">
                    @if ($address)
                        <x-ui.address-card :address="$address" />
                    @else
                        <p class="text-sm text-muted">You haven't saved an address yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-10">
            <h2 class="font-display text-xl text-charcoal">Recommended For You</h2>
            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                @foreach ($recommended as $product)
                    <x-ui.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </x-account.shell>
</x-layouts.app>

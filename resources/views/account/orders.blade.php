@php
    $tabs = ['All', 'Processing', 'Shipped', 'Delivered', 'Cancelled', 'Returned'];
@endphp

<x-layouts.app :title="$title">
    <x-account.shell active="orders">
        <div x-data="{ tab: 'All' }">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="font-display text-2xl text-charcoal sm:text-3xl">My Orders</h1>
            </div>

            <div class="-mx-1 flex snap-x gap-2 overflow-x-auto px-1 pb-1">
                @foreach ($tabs as $tab)
                    <button @click="tab = '{{ $tab }}'" class="flex-shrink-0 snap-start rounded-full border px-4 py-2 text-xs font-medium transition-colors" :class="tab === '{{ $tab }}' ? 'border-charcoal bg-charcoal text-ivory' : 'border-line text-charcoal-soft'">
                        {{ $tab }}
                    </button>
                @endforeach
            </div>

            <div class="mt-6 space-y-5">
                @foreach ($orders as $order)
                    <div x-show="tab === 'All' || tab === '{{ $order['status'] }}'">
                        <x-ui.order-card :order="$order" />
                    </div>
                @endforeach
            </div>

            @if (count($orders) === 0)
                <x-ui.empty-state icon="box" title="No orders yet" description="When you place an order, it will appear here." action-label="Start Shopping" :action-url="route('home')" />
            @endif
        </div>
    </x-account.shell>
</x-layouts.app>

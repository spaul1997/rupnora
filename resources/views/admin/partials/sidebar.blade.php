@php
    $nav = [
        ['label' => 'Dashboard', 'icon' => 'M4 11l8-7 8 7v9a1 1 0 01-1 1h-4v-6H9v6H5a1 1 0 01-1-1z', 'url' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
    ];

    $groups = [
        'orders' => [
            'label' => 'Orders',
            'icon' => 'M3 7h13l1.5 12h-16z M8 7V5.5a3 3 0 016 0V7',
            'active' => request()->routeIs('admin.orders.*'),
            'items' => [
                ['label' => 'All Orders', 'url' => route('admin.orders.index')],
                ['label' => 'Pending Orders', 'url' => route('admin.orders.index', ['status' => 'pending'])],
                ['label' => 'Processing', 'url' => route('admin.orders.index', ['status' => 'processing'])],
                ['label' => 'Shipped', 'url' => route('admin.orders.index', ['status' => 'shipped'])],
                ['label' => 'Delivered', 'url' => route('admin.orders.index', ['status' => 'delivered'])],
                ['label' => 'Cancelled', 'url' => route('admin.orders.index', ['status' => 'cancelled'])],
                ['label' => 'Returns', 'url' => route('admin.orders.index', ['status' => 'returned'])],
            ],
        ],
        'products' => [
            'label' => 'Products',
            'icon' => 'M12 2l2.6 5.6 6.1.6-4.6 4.2 1.3 6.1L12 15l-5.4 3 1.3-6.1L3.3 8.2l6.1-.6L12 2z',
            'active' => request()->routeIs('admin.products.*') || request()->routeIs('admin.categories.*') || request()->routeIs('admin.jewellery-types.*') || request()->routeIs('admin.jewellery-collections.*') || request()->routeIs('admin.inventory.*'),
            'items' => [
                ['label' => 'All Products', 'url' => route('admin.products.index')],
                ['label' => 'Add Product', 'url' => route('admin.products.create')],
                ['label' => 'Categories', 'url' => route('admin.categories.index')],
                ['label' => 'Jewellery Types', 'url' => route('admin.jewellery-types.index')],
                ['label' => 'Collections', 'url' => route('admin.jewellery-collections.index')],
                ['label' => 'Inventory', 'url' => route('admin.inventory.index')],
            ],
        ],
    ];

    $simple = [
        ['label' => 'Customers', 'icon' => 'M12 12a4 4 0 100-8 4 4 0 000 8zM4 20c0-4 3.6-7 8-7s8 3 8 7', 'url' => route('admin.customers.index'), 'active' => request()->routeIs('admin.customers.*')],
    ];

    $groups['feedback'] = [
        'label' => 'Feedback',
        'icon' => 'M12 20.5s-7.5-4.9-10.1-9.6C.3 7.9 1.6 4.5 4.9 3.6c2-.5 4 .3 5.1 2 .3.4.7.4 1 0 1.1-1.7 3.1-2.5 5.1-2 3.3.9 4.6 4.3 3 7.3-2.6 4.7-10.1 9.6-10.1 9.6z',
        'active' => request()->routeIs('admin.reviews.*') || request()->routeIs('admin.feedbacks.*'),
        'items' => [
            ['label' => 'Product Reviews', 'url' => route('admin.reviews.index')],
            ['label' => 'Customer Feedback', 'url' => route('admin.feedbacks.index')],
        ],
    ];

    $groups['support'] = [
        'label' => 'Support',
        'icon' => 'M18.4 15.5A8 8 0 105.6 4.6M12 8v4l2.5 2.5',
        'active' => request()->routeIs('admin.contacts.*') || request()->routeIs('admin.faqs.*'),
        'items' => [
            ['label' => 'Contact Requests', 'url' => route('admin.contacts.index')],
            ['label' => 'FAQ', 'url' => route('admin.faqs.index')],
        ],
    ];

    $groups['marketing'] = [
        'label' => 'Marketing',
        'icon' => 'M3 7h13l1.5 12h-16z M16 10h3l2 3v4h-5z',
        'active' => request()->routeIs('admin.coupons.*') || request()->routeIs('admin.home-banners.*'),
        'items' => [
            ['label' => 'Home Banners', 'url' => route('admin.home-banners.index')],
            ['label' => 'Coupons', 'url' => route('admin.coupons.index')],
        ],
    ];

    $simple[] = ['label' => 'Reports', 'icon' => 'M9 17V9m4 8V5m4 12v-6M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z', 'url' => route('admin.reports.index'), 'active' => request()->routeIs('admin.reports.*')];
    $simple[] = ['label' => 'Settings', 'icon' => 'M12 15v2m-5 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H7a2 2 0 00-2 2v6a2 2 0 002 2zM8 11V8a4 4 0 118 0v3', 'url' => route('admin.settings.edit'), 'active' => request()->routeIs('admin.settings.*')];

    $openGroup = collect($groups)->filter(fn ($g) => $g['active'])->keys()->first();
@endphp

<div x-data="{ open: {{ $openGroup ? "'".$openGroup."'" : 'null' }} }" class="flex h-full flex-col bg-charcoal">
    <div class="flex h-16 flex-shrink-0 items-center gap-2 px-5">
        <span class="font-display text-xl text-ivory">Aurelle</span>
        <span class="rounded bg-champagne-dark/20 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-champagne-light">Admin</span>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-4">
        @foreach ($nav as $item)
            <a href="{{ $item['url'] }}" class="admin-sidebar-link {{ $item['active'] ? 'active' : '' }}">
                <svg class="h-4.5 w-4.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="{{ $item['icon'] }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
                {{ $item['label'] }}
            </a>
        @endforeach

        @foreach ($groups as $key => $group)
            <div>
                <button @click="open = open === '{{ $key }}' ? null : '{{ $key }}'" class="admin-sidebar-link w-full justify-between {{ $group['active'] && $openGroup !== $key ? 'active' : '' }}">
                    <span class="flex items-center gap-3">
                        <svg class="h-4.5 w-4.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="{{ $group['icon'] }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        {{ $group['label'] }}
                    </span>
                    <svg class="h-3.5 w-3.5 flex-shrink-0 transition-transform" :class="open === '{{ $key }}' && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </button>
                <div x-cloak x-show="open === '{{ $key }}'" x-collapse class="mt-1 space-y-0.5 pl-11">
                    @foreach ($group['items'] as $item)
                        <a href="{{ $item['url'] }}" class="block rounded-lg px-3 py-2 text-[13px] text-ivory/55 transition-colors hover:bg-ivory/5 hover:text-ivory {{ request()->fullUrlIs($item['url']) || (request()->url() === $item['url'] && ! request()->query()) ? 'text-champagne-light' : '' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach

        @foreach ($simple as $item)
            <a href="{{ $item['url'] }}" class="admin-sidebar-link {{ $item['active'] ? 'active' : '' }}">
                <svg class="h-4.5 w-4.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="{{ $item['icon'] }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="flex-shrink-0 space-y-1 border-t border-ivory/10 p-3">
        <a href="{{ route('admin.settings.edit') }}" class="admin-sidebar-link">
            <svg class="h-4.5 w-4.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="4" /><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke-linecap="round" /></svg>
            Admin Profile
        </a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="admin-sidebar-link w-full text-left text-red-300 hover:bg-red-500/10 hover:text-red-200">
                <svg class="h-4.5 w-4.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4m6 14l5-5-5-5m5 5H9" stroke-linecap="round" stroke-linejoin="round" /></svg>
                Logout
            </button>
        </form>
    </div>
</div>

@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-admin.page-header title="Dashboard" description="Overview of your store's performance." />

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-admin.stat-card label="Total Sales" value="₹{{ number_format($stats['total_sales'], 2) }}" tone="success" icon="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6l8-4z" />
        <x-admin.stat-card label="Total Orders" :value="$stats['total_orders']" icon="M3 7h13l1.5 12h-16z" :href="route('admin.orders.index')" />
        <x-admin.stat-card label="Pending Orders" :value="$stats['pending_orders']" tone="warning" icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" :href="route('admin.orders.index', ['status' => 'pending'])" />
        <x-admin.stat-card label="Delivered Orders" :value="$stats['delivered_orders']" tone="success" icon="M5 13l4 4L19 7" :href="route('admin.orders.index', ['status' => 'delivered'])" />
        <x-admin.stat-card label="Total Products" :value="$stats['total_products']" icon="M12 2l2.6 5.6 6.1.6-4.6 4.2 1.3 6.1L12 15l-5.4 3 1.3-6.1L3.3 8.2l6.1-.6L12 2z" :href="route('admin.products.index')" />
        <x-admin.stat-card label="Low Stock Products" :value="$stats['low_stock_products']" tone="error" icon="M10.3 3.9L2.5 17a1 1 0 00.9 1.5h17.2a1 1 0 00.9-1.5L13.7 3.9a1 1 0 00-1.4 0z" :href="route('admin.inventory.index', ['stock_status' => 'low_stock'])" />
        <x-admin.stat-card label="Total Customers" :value="$stats['total_customers']" icon="M12 12a4 4 0 100-8 4 4 0 000 8zM4 20c0-4 3.6-7 8-7s8 3 8 7" :href="route('admin.customers.index')" />
        <x-admin.stat-card label="Pending Reviews" :value="$stats['pending_reviews']" tone="warning" icon="M12 20.5s-7.5-4.9-10.1-9.6" :href="route('admin.reviews.index', ['status' => 'pending'])" />
    </div>

    {{-- Sales overview --}}
    <div class="admin-card mb-6 p-6">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Sales Overview</h3>
            <a href="{{ route('admin.reports.sales') }}" class="text-sm font-medium text-champagne-dark hover:underline">View Full Report</a>
        </div>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div><p class="text-xs text-gray-400">Today</p><p class="mt-1 text-lg font-semibold text-gray-900">₹{{ number_format($salesOverview['today'], 2) }}</p></div>
            <div><p class="text-xs text-gray-400">Last 7 Days</p><p class="mt-1 text-lg font-semibold text-gray-900">₹{{ number_format($salesOverview['last_7_days'], 2) }}</p></div>
            <div><p class="text-xs text-gray-400">This Month</p><p class="mt-1 text-lg font-semibold text-gray-900">₹{{ number_format($salesOverview['this_month'], 2) }}</p></div>
            <div><p class="text-xs text-gray-400">This Year</p><p class="mt-1 text-lg font-semibold text-gray-900">₹{{ number_format($salesOverview['this_year'], 2) }}</p></div>
        </div>

        {{-- Simple bar chart, no external chart library needed --}}
        <div class="mt-6 flex h-32 items-end gap-1.5" x-data="{ max: Math.max(...{{ Illuminate\Support\Js::from($chartData['values']) }}, 1) }">
            @foreach ($chartData['labels'] as $i => $label)
                <div class="group relative flex-1">
                    <div class="mx-auto rounded-t bg-champagne-light transition-colors hover:bg-champagne-dark" :style="`height: ${Math.max(4, ({{ $chartData['values'][$i] }} / max) * 100)}px`" style="width: 100%; height: 4px;"></div>
                    <span class="absolute -top-6 left-1/2 hidden -translate-x-1/2 whitespace-nowrap rounded bg-charcoal px-1.5 py-0.5 text-[10px] text-white group-hover:block">₹{{ number_format($chartData['values'][$i]) }}</span>
                </div>
            @endforeach
        </div>
        <div class="mt-2 flex gap-1.5">
            @foreach ($chartData['labels'] as $label)
                <span class="flex-1 text-center text-[10px] text-gray-400">{{ $label }}</span>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Recent orders --}}
        <div class="admin-card p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Recent Orders</h3>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-champagne-dark hover:underline">View All</a>
            </div>
            <div class="space-y-3">
                @forelse ($recentOrders as $order)
                    <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between rounded-lg px-2 py-2 hover:bg-gray-50">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-400">{{ $order->user?->name ?? $order->customer_name }} &middot; {{ $order->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">₹{{ number_format($order->grand_total) }}</p>
                            <x-admin.status-badge :status="$order->status" />
                        </div>
                    </a>
                @empty
                    <x-admin.empty-state title="No orders yet" />
                @endforelse
            </div>
        </div>

        {{-- Top selling products --}}
        <div class="admin-card p-6">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Top Selling Products</h3>
            <div class="space-y-3">
                @forelse ($topSellingProducts as $product)
                    <div class="flex items-center justify-between px-2 py-2">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                            <p class="text-xs text-gray-400">{{ $product->sku }} &middot; {{ $product->units_sold }} sold</p>
                        </div>
                        <p class="text-sm font-medium text-gray-900">₹{{ number_format($product->revenue) }}</p>
                    </div>
                @empty
                    <x-admin.empty-state title="No sales data yet" />
                @endforelse
            </div>
        </div>

        {{-- Low stock products --}}
        <div class="admin-card p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Low Stock Products</h3>
                <a href="{{ route('admin.inventory.index') }}" class="text-sm font-medium text-champagne-dark hover:underline">View All</a>
            </div>
            <div class="space-y-3">
                @forelse ($lowStockProducts as $product)
                    <div class="flex items-center justify-between px-2 py-2">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                            <p class="text-xs text-gray-400">{{ $product->category?->name }}</p>
                        </div>
                        <x-admin.status-badge :status="$product->stock_status" />
                    </div>
                @empty
                    <x-admin.empty-state title="No low stock products" />
                @endforelse
            </div>
        </div>

        {{-- Recent reviews & contacts --}}
        <div class="admin-card p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Recent Reviews</h3>
                <a href="{{ route('admin.reviews.index') }}" class="text-sm font-medium text-champagne-dark hover:underline">View All</a>
            </div>
            <div class="space-y-3">
                @forelse ($recentReviews as $review)
                    <div class="px-2 py-2">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900">{{ $review->customer?->name }}</p>
                            <x-admin.status-badge :status="$review->status" />
                        </div>
                        <p class="mt-0.5 truncate text-xs text-gray-500">{{ $review->review }}</p>
                    </div>
                @empty
                    <x-admin.empty-state title="No reviews yet" />
                @endforelse
            </div>
        </div>
    </div>
@endsection

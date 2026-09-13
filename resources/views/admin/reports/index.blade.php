@extends('admin.layouts.app')

@section('title', 'Reports')

@section('content')
    <x-admin.page-header title="Reports" description="Analyze sales, inventory and customer performance." />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            ['label' => 'Sales Report', 'desc' => 'Revenue trends over time.', 'route' => 'admin.reports.sales', 'icon' => 'M9 17V9m4 8V5m4 12v-6M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z'],
            ['label' => 'Order Report', 'desc' => 'All orders with status breakdown.', 'route' => 'admin.reports.orders', 'icon' => 'M3 7h13l1.5 12h-16z'],
            ['label' => 'Product Sales Report', 'desc' => 'Best performing products.', 'route' => 'admin.reports.products', 'icon' => 'M12 2l2.6 5.6 6.1.6-4.6 4.2 1.3 6.1L12 15l-5.4 3 1.3-6.1L3.3 8.2l6.1-.6L12 2z'],
            ['label' => 'Category Sales Report', 'desc' => 'Revenue by category.', 'route' => 'admin.reports.categories', 'icon' => 'M4 4h16v16H4z'],
            ['label' => 'Customer Report', 'desc' => 'Top customers by spend.', 'route' => 'admin.reports.customers', 'icon' => 'M12 12a4 4 0 100-8 4 4 0 000 8zM4 20c0-4 3.6-7 8-7s8 3 8 7'],
            ['label' => 'Inventory Report', 'desc' => 'Full stock overview.', 'route' => 'admin.reports.inventory', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ['label' => 'Low Stock Report', 'desc' => 'Products needing restock.', 'route' => 'admin.reports.low-stock', 'icon' => 'M12 9v4m0 4h.01M10.3 3.9L2.5 17a1 1 0 00.9 1.5h17.2a1 1 0 00.9-1.5L13.7 3.9a1 1 0 00-1.4 0z'],
            ['label' => 'Payment Report', 'desc' => 'Payments by method and status.', 'route' => 'admin.reports.payments', 'icon' => 'M3 7h13l1.5 12h-16z M8 7V5.5a3 3 0 016 0V7'],
            ['label' => 'Refund Report', 'desc' => 'Refunded and returned orders.', 'route' => 'admin.reports.refunds', 'icon' => 'M4 4v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V4'],
        ] as $report)
            <a href="{{ route($report['route']) }}" class="admin-card flex items-start gap-4 p-5 transition-shadow hover:shadow-md">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-beige text-champagne-dark">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="{{ $report['icon'] }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ $report['label'] }}</p>
                    <p class="mt-0.5 text-sm text-gray-500">{{ $report['desc'] }}</p>
                </div>
            </a>
        @endforeach
    </div>
@endsection

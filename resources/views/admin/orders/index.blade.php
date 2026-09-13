@extends('admin.layouts.app')

@section('title', 'Orders')

@section('content')
    <x-admin.page-header title="Orders" description="Manage customer orders and fulfillment.">
        <x-slot:actions>
            <a href="{{ route('admin.orders.export') }}" class="admin-btn-secondary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                Export CSV
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="admin-card mb-5 grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <input type="text" name="order_number" value="{{ request('order_number') }}" placeholder="Order number..." class="admin-input">
        <input type="text" name="customer" value="{{ request('customer') }}" placeholder="Customer name or email..." class="admin-input">
        <input type="text" name="mobile" value="{{ request('mobile') }}" placeholder="Mobile number..." class="admin-input">
        <select name="status" class="admin-select">
            <option value="">All Statuses</option>
            @foreach (\App\Models\Order::STATUSES as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <select name="payment_status" class="admin-select">
            <option value="">All Payment Statuses</option>
            @foreach (\App\Models\Order::PAYMENT_STATUSES as $status)
                <option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <input type="text" name="payment_method" value="{{ request('payment_method') }}" placeholder="Payment method..." class="admin-input">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="admin-input">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="admin-input">
        <div class="flex gap-2 sm:col-span-2 lg:col-span-4">
            <button type="submit" class="admin-btn-secondary">Apply Filters</button>
            @if (request()->hasAny(['order_number', 'customer', 'mobile', 'status', 'payment_status', 'payment_method', 'date_from', 'date_to']))
                <a href="{{ route('admin.orders.index') }}" class="admin-btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    @if ($orders->isEmpty())
        <x-admin.empty-state title="No orders found" description="Orders will appear here once customers start purchasing." />
    @else
        <x-admin.table :headers="['Order ID', 'Date', 'Customer', 'Mobile', 'Items', 'Grand Total', 'Payment', 'Payment Status', 'Order Status', '!Actions']">
            @foreach ($orders as $order)
                <tr>
                    <td class="font-medium text-gray-900">{{ $order->order_number }}</td>
                    <td class="text-gray-500">{{ $order->created_at->format('d M Y') }}</td>
                    <td>{{ $order->customer_name }}</td>
                    <td class="text-gray-500">{{ $order->customer_phone }}</td>
                    <td>{{ $order->items_count }}</td>
                    <td class="font-medium text-gray-900">₹{{ number_format($order->grand_total, 2) }}</td>
                    <td>{{ $order->payment_method }}</td>
                    <td><x-admin.status-badge :status="$order->payment_status" /></td>
                    <td><x-admin.status-badge :status="$order->status" /></td>
                    <td class="text-right">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-sm font-medium text-champagne-dark hover:underline">View</a>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$orders" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

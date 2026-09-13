@extends('admin.layouts.app')

@section('title', 'Order Report')

@section('content')
    <x-admin.page-header title="Order Report" :breadcrumb="[['label' => 'Reports', 'url' => route('admin.reports.index')], ['label' => 'Orders']]" />

    @php($exportType = 'orders')
    @include('admin.reports._period-filter')

    <div class="mb-6 flex flex-wrap gap-2">
        @foreach ($statusCounts as $status => $count)
            <span class="admin-badge bg-gray-100 text-gray-700">{{ ucwords(str_replace('_',' ',$status)) }}: {{ $count }}</span>
        @endforeach
    </div>

    @if ($orders->isEmpty())
        <x-admin.empty-state title="No orders in this period" />
    @else
        <x-admin.table :headers="['Order ID', 'Date', 'Customer', 'Status', 'Payment', '!Total']">
            @foreach ($orders as $order)
                <tr>
                    <td><a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-champagne-dark hover:underline">{{ $order->order_number }}</a></td>
                    <td class="text-gray-500">{{ $order->created_at->format('d M Y') }}</td>
                    <td>{{ $order->customer_name }}</td>
                    <td><x-admin.status-badge :status="$order->status" /></td>
                    <td><x-admin.status-badge :status="$order->payment_status" /></td>
                    <td class="text-right font-medium text-gray-900">₹{{ number_format($order->grand_total, 2) }}</td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$orders" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

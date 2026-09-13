@extends('admin.layouts.app')

@section('title', 'Payment Report')

@section('content')
    <x-admin.page-header title="Payment Report" :breadcrumb="[['label' => 'Reports', 'url' => route('admin.reports.index')], ['label' => 'Payments']]" />

    @php($exportType = 'orders')
    @include('admin.reports._period-filter')

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach ($byMethod as $method)
            <x-admin.stat-card :label="$method->payment_method ?? 'Unknown'" value="₹{{ number_format($method->total, 2) }}" :trend="$method->orders.' orders'" />
        @endforeach
    </div>

    @if ($orders->isEmpty())
        <x-admin.empty-state title="No orders in this period" />
    @else
        <x-admin.table :headers="['Order ID', 'Date', 'Method', 'Payment Status', '!Amount']">
            @foreach ($orders as $order)
                <tr>
                    <td><a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-champagne-dark hover:underline">{{ $order->order_number }}</a></td>
                    <td class="text-gray-500">{{ $order->created_at->format('d M Y') }}</td>
                    <td>{{ $order->payment_method }}</td>
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

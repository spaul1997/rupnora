@extends('admin.layouts.app')

@section('title', 'Refund Report')

@section('content')
    <x-admin.page-header title="Refund Report" :breadcrumb="[['label' => 'Reports', 'url' => route('admin.reports.index')], ['label' => 'Refunds']]" />

    @php($exportType = 'orders')
    @include('admin.reports._period-filter')

    <div class="mb-6">
        <x-admin.stat-card label="Total Refunded" value="₹{{ number_format($totalRefunded, 2) }}" tone="error" icon="M4 4v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V4" />
    </div>

    @if ($orders->isEmpty())
        <x-admin.empty-state title="No refunds in this period" />
    @else
        <x-admin.table :headers="['Order ID', 'Date', 'Customer', 'Payment Status', '!Refund Amount']">
            @foreach ($orders as $order)
                <tr>
                    <td><a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-champagne-dark hover:underline">{{ $order->order_number }}</a></td>
                    <td class="text-gray-500">{{ $order->created_at->format('d M Y') }}</td>
                    <td>{{ $order->customer_name }}</td>
                    <td><x-admin.status-badge :status="$order->payment_status" /></td>
                    <td class="text-right font-medium text-error">₹{{ number_format($order->refund_amount, 2) }}</td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$orders" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

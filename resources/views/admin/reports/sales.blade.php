@extends('admin.layouts.app')

@section('title', 'Sales Report')

@section('content')
    <x-admin.page-header title="Sales Report" :breadcrumb="[['label' => 'Reports', 'url' => route('admin.reports.index')], ['label' => 'Sales']]" />

    @php($exportType = 'sales')
    @include('admin.reports._period-filter')

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-admin.stat-card label="Total Sales" value="₹{{ number_format($summary['total_sales'], 2) }}" tone="success" icon="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6l8-4z" />
        <x-admin.stat-card label="Orders" :value="$summary['order_count']" icon="M3 7h13l1.5 12h-16z" />
        <x-admin.stat-card label="Avg. Order Value" value="₹{{ number_format($summary['avg_order_value'], 2) }}" icon="M9 17V9m4 8V5m4 12v-6" />
    </div>

    @if ($dailySales->isEmpty())
        <x-admin.empty-state title="No paid orders in this period" />
    @else
        <x-admin.table :headers="['Date', 'Orders', '!Revenue']">
            @foreach ($dailySales as $day)
                <tr>
                    <td class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($day->day)->format('d M Y') }}</td>
                    <td>{{ $day->orders }}</td>
                    <td class="text-right font-medium text-gray-900">₹{{ number_format($day->total, 2) }}</td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
@endsection

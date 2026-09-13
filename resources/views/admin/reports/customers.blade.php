@extends('admin.layouts.app')

@section('title', 'Customer Report')

@section('content')
    <x-admin.page-header title="Customer Report" :breadcrumb="[['label' => 'Reports', 'url' => route('admin.reports.index')], ['label' => 'Customers']]" />

    @include('admin.reports._period-filter')

    @if ($customers->isEmpty())
        <x-admin.empty-state title="No customer activity in this period" />
    @else
        <x-admin.table :headers="['Customer', 'Email', 'Orders in Period', '!Spend in Period']">
            @foreach ($customers as $customer)
                <tr>
                    <td><a href="{{ route('admin.customers.show', $customer) }}" class="font-medium text-champagne-dark hover:underline">{{ $customer->name }}</a></td>
                    <td class="text-gray-500">{{ $customer->email }}</td>
                    <td>{{ $customer->orders_count }}</td>
                    <td class="text-right font-medium text-gray-900">₹{{ number_format($customer->period_spend ?? 0, 2) }}</td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$customers" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

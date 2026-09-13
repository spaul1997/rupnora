@extends('admin.layouts.app')

@section('title', 'Product Sales Report')

@section('content')
    <x-admin.page-header title="Product Sales Report" :breadcrumb="[['label' => 'Reports', 'url' => route('admin.reports.index')], ['label' => 'Products']]" />

    @include('admin.reports._period-filter')

    @if ($products->isEmpty())
        <x-admin.empty-state title="No product sales in this period" />
    @else
        <x-admin.table :headers="['Product', 'SKU', 'Units Sold', '!Revenue']">
            @foreach ($products as $product)
                <tr>
                    <td class="font-medium text-gray-900">{{ $product->name }}</td>
                    <td class="text-gray-500">{{ $product->sku }}</td>
                    <td>{{ $product->units_sold }}</td>
                    <td class="text-right font-medium text-gray-900">₹{{ number_format($product->revenue, 2) }}</td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$products" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

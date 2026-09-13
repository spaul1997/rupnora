@extends('admin.layouts.app')

@section('title', 'Low Stock Report')

@section('content')
    <x-admin.page-header title="Low Stock Report" :breadcrumb="[['label' => 'Reports', 'url' => route('admin.reports.index')], ['label' => 'Low Stock']]">
        <x-slot:actions>
            <a href="{{ route('admin.reports.export', ['type' => 'low-stock', 'format' => 'csv']) }}" class="admin-btn-secondary">Export CSV</a>
            <button type="button" onclick="window.print()" class="admin-btn-secondary">Print / PDF</button>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($products->isEmpty())
        <x-admin.empty-state title="All products are well stocked" description="No products are currently at or below their minimum stock level." />
    @else
        <x-admin.table :headers="['Product', 'SKU', 'Category', 'Stock', 'Minimum', '!Status']">
            @foreach ($products as $product)
                <tr>
                    <td class="font-medium text-gray-900">{{ $product->name }}</td>
                    <td class="text-gray-500">{{ $product->sku }}</td>
                    <td>{{ $product->category?->name }}</td>
                    <td class="font-medium text-error">{{ $product->stock_quantity }}</td>
                    <td>{{ $product->minimum_stock }}</td>
                    <td class="text-right"><x-admin.status-badge :status="$product->stock_status" /></td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$products" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

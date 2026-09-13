@extends('admin.layouts.app')

@section('title', 'Category Sales Report')

@section('content')
    <x-admin.page-header title="Category Sales Report" :breadcrumb="[['label' => 'Reports', 'url' => route('admin.reports.index')], ['label' => 'Categories']]" />

    @include('admin.reports._period-filter')

    @if ($categories->isEmpty())
        <x-admin.empty-state title="No category sales in this period" />
    @else
        <x-admin.table :headers="['Category', 'Units Sold', '!Revenue']">
            @foreach ($categories as $category)
                <tr>
                    <td class="font-medium text-gray-900">{{ $category->name }}</td>
                    <td>{{ $category->units_sold }}</td>
                    <td class="text-right font-medium text-gray-900">₹{{ number_format($category->revenue, 2) }}</td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
@endsection

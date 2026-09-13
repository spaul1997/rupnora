@extends('admin.layouts.app')

@section('title', 'Stock History')

@section('content')
    <x-admin.page-header :title="'Stock History — ' . $product->name" :breadcrumb="[['label' => 'Inventory', 'url' => route('admin.inventory.index')], ['label' => $product->name]]" />

    @if ($adjustments->isEmpty())
        <x-admin.empty-state title="No stock adjustments yet" description="Adjustments made from the inventory page will appear here." />
    @else
        <x-admin.table :headers="['Date', 'Type', 'Variant', 'Quantity', 'Previous', 'New', 'Reason', 'By']">
            @foreach ($adjustments as $adj)
                <tr>
                    <td class="text-gray-500">{{ $adj->created_at->format('d M Y, h:i A') }}</td>
                    <td><x-admin.status-badge :status="$adj->type === 'add' ? 'in_stock' : ($adj->type === 'remove' ? 'out_of_stock' : 'low_stock')" /> <span class="ml-1 text-xs text-gray-400">{{ ucfirst($adj->type) }}</span></td>
                    <td>{{ $adj->variant?->size ?? '—' }}</td>
                    <td>{{ $adj->quantity }}</td>
                    <td>{{ $adj->previous_stock }}</td>
                    <td class="font-medium text-gray-900">{{ $adj->new_stock }}</td>
                    <td>{{ $adj->reason ?? '—' }}</td>
                    <td>{{ $adj->createdBy?->name ?? 'System' }}</td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$adjustments" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

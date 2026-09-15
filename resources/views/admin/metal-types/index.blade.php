@extends('admin.layouts.app')

@section('title', 'Metal Types')

@section('content')
    <x-admin.page-header title="Metal Types" description="Manage the metal options available for products." :breadcrumb="[['label' => 'Products', 'url' => route('admin.products.index')], ['label' => 'Metal Types']]">
        <x-slot:actions>
            <a href="{{ route('admin.metal-types.create') }}" class="admin-btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                Add Metal Type
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="admin-card mb-5 flex flex-wrap items-center gap-3 p-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search metal types..." class="admin-input max-w-xs">
        <button type="submit" class="admin-btn-secondary">Filter</button>
        @if (request()->has('search'))
            <a href="{{ route('admin.metal-types.index') }}" class="admin-btn-ghost">Clear</a>
        @endif
    </form>

    @if ($types->isEmpty())
        <x-admin.empty-state title="No metal types found" description="Create metal options for product entry.">
            <x-slot:actions>
                <a href="{{ route('admin.metal-types.create') }}" class="admin-btn-primary">Add Metal Type</a>
            </x-slot:actions>
        </x-admin.empty-state>
    @else
        <x-admin.table :headers="['Metal Type', 'Products', 'Sort Order', 'Status', '!Actions']">
            @foreach ($types as $type)
                <tr>
                    <td>
                        <p class="font-medium text-gray-900">{{ $type->name }}</p>
                        <p class="text-xs text-gray-400">{{ $type->slug }}</p>
                    </td>
                    <td>{{ $type->products_count }}</td>
                    <td>{{ $type->sort_order }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.metal-types.toggle-active', $type) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit"><x-admin.status-badge :status="$type->is_active ? 'active' : 'inactive'" /></button>
                        </form>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.metal-types.edit', $type) }}" class="text-sm font-medium text-champagne-dark hover:underline">Edit</a>
                            <x-admin.confirm-modal :action="route('admin.metal-types.destroy', $type)" title="Delete Metal Type" message="Are you sure you want to delete '{{ $type->name }}'?" />
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$types" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

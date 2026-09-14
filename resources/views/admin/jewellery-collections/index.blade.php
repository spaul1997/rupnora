@extends('admin.layouts.app')

@section('title', 'Collections')

@section('content')
    <x-admin.page-header title="Collections" description="Manage category-wise collection master data." :breadcrumb="[['label' => 'Products', 'url' => route('admin.products.index')], ['label' => 'Collections']]">
        <x-slot:actions>
            <a href="{{ route('admin.jewellery-collections.create') }}" class="admin-btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                Add Collection
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="admin-card mb-5 flex flex-wrap items-center gap-3 p-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search collections..." class="admin-input max-w-xs">
        <button type="submit" class="admin-btn-secondary">Filter</button>
        @if (request()->has('search'))
            <a href="{{ route('admin.jewellery-collections.index') }}" class="admin-btn-ghost">Clear</a>
        @endif
    </form>

    @if ($collections->isEmpty())
        <x-admin.empty-state title="No collections found" description="Create category-wise collection options for product entry.">
            <x-slot:actions>
                <a href="{{ route('admin.jewellery-collections.create') }}" class="admin-btn-primary">Add Collection</a>
            </x-slot:actions>
        </x-admin.empty-state>
    @else
        <x-admin.table :headers="['Collection', 'Products', 'Sort Order', 'Status', '!Actions']">
            @foreach ($collections as $collection)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            @if ($collection->logo)
                                <x-ui.optimized-image :src="asset('storage/'.$collection->logo)" alt="" sizes="36px" class="h-9 w-9 rounded-lg border border-gray-200 object-contain p-1" />
                            @else
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-beige text-champagne-dark">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="4" y="4" width="16" height="16" rx="2" /></svg>
                                </span>
                            @endif
                            <div>
                                <p class="font-medium text-gray-900">{{ $collection->name }}</p>
                                <p class="text-xs text-gray-400">{{ $collection->slug }}</p>
                            </div>
                        </div>
                    </td>
                    <td>{{ $collection->products_count }}</td>
                    <td>{{ $collection->sort_order }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.jewellery-collections.toggle-active', $collection) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit"><x-admin.status-badge :status="$collection->is_active ? 'active' : 'inactive'" /></button>
                        </form>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.jewellery-collections.edit', $collection) }}" class="text-sm font-medium text-champagne-dark hover:underline">Edit</a>
                            <x-admin.confirm-modal :action="route('admin.jewellery-collections.destroy', $collection)" title="Delete Collection" message="Are you sure you want to delete '{{ $collection->name }}'?" />
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$collections" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

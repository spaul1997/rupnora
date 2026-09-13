@extends('admin.layouts.app')

@section('title', 'Categories')

@section('content')
    <x-admin.page-header title="Categories" description="Manage jewellery categories and subcategories." :breadcrumb="[['label' => 'Products', 'url' => route('admin.products.index')], ['label' => 'Categories']]">
        <x-slot:actions>
            <a href="{{ route('admin.categories.create') }}" class="admin-btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                Add Category
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="admin-card mb-5 flex flex-wrap items-center gap-3 p-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search categories..." class="admin-input max-w-xs">
        <select name="parent_id" class="admin-select max-w-xs">
            <option value="">All Categories</option>
            <option value="parents" @selected(request('parent_id') === 'parents')>All Parent Categories</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected(request('parent_id') == $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="admin-btn-secondary">Filter</button>
        @if (request()->hasAny(['search', 'parent_id']))
            <a href="{{ route('admin.categories.index') }}" class="admin-btn-ghost">Clear</a>
        @endif
    </form>

    @if ($categories->isEmpty())
        <x-admin.empty-state title="No categories found" description="Create your first category to start organizing products.">
            <x-slot:actions>
                <a href="{{ route('admin.categories.create') }}" class="admin-btn-primary">Add Category</a>
            </x-slot:actions>
        </x-admin.empty-state>
    @else
        <x-admin.table :headers="['Category', 'Parent', 'Products', 'Sort Order', 'Status', '!Actions']">
            @foreach ($categories as $category)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            @if ($category->image)
                                <x-ui.optimized-image :src="asset('storage/'.$category->image)" alt="" sizes="36px" class="h-9 w-9 rounded-lg object-cover" />
                            @else
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-beige text-champagne-dark">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="4" y="4" width="16" height="16" rx="2" /></svg>
                                </span>
                            @endif
                            <div>
                                <p class="font-medium text-gray-900">{{ $category->name }}</p>
                                <p class="text-xs text-gray-400">{{ $category->slug }}</p>
                            </div>
                        </div>
                    </td>
                    <td>{{ $category->parent?->name ?? '—' }}</td>
                    <td>{{ $category->products_count }}</td>
                    <td>{{ $category->sort_order }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.categories.toggle-active', $category) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit"><x-admin.status-badge :status="$category->is_active ? 'active' : 'inactive'" /></button>
                        </form>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="text-sm font-medium text-champagne-dark hover:underline">Edit</a>
                            <x-admin.confirm-modal :action="route('admin.categories.destroy', $category)" title="Delete Category" message="Are you sure you want to delete '{{ $category->name }}'? This action cannot be undone." />
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$categories" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

@extends('admin.layouts.app')

@section('title', 'FAQ')

@section('content')
    <x-admin.page-header title="FAQ" description="Manage frequently asked questions shown on the storefront.">
        <x-slot:actions>
            <a href="{{ route('admin.faqs.create') }}" class="admin-btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                Add FAQ
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="admin-card mb-5 flex flex-wrap items-center gap-3 p-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search questions..." class="admin-input max-w-xs">
        <select name="category" class="admin-select max-w-[180px]">
            <option value="">All Categories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ ucwords(str_replace('_',' ',$cat)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="admin-btn-secondary">Filter</button>
        @if (request()->hasAny(['search', 'category']))
            <a href="{{ route('admin.faqs.index') }}" class="admin-btn-ghost">Clear</a>
        @endif
    </form>

    @if ($faqs->isEmpty())
        <x-admin.empty-state title="No FAQs found">
            <x-slot:actions><a href="{{ route('admin.faqs.create') }}" class="admin-btn-primary">Add FAQ</a></x-slot:actions>
        </x-admin.empty-state>
    @else
        <x-admin.table :headers="['Question', 'Category', 'Sort Order', 'Status', '!Actions']">
            @foreach ($faqs as $faq)
                <tr>
                    <td class="max-w-md font-medium text-gray-900">{{ $faq->question }}</td>
                    <td>{{ ucwords(str_replace('_',' ',$faq->category)) }}</td>
                    <td>{{ $faq->sort_order }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.faqs.toggle-active', $faq) }}">
                            @csrf @method('PATCH')
                            <button type="submit"><x-admin.status-badge :status="$faq->is_active ? 'active' : 'inactive'" /></button>
                        </form>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.faqs.edit', $faq) }}" class="text-sm font-medium text-champagne-dark hover:underline">Edit</a>
                            <x-admin.confirm-modal :action="route('admin.faqs.destroy', $faq)" title="Delete FAQ" message="Are you sure you want to delete this FAQ?" />
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$faqs" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

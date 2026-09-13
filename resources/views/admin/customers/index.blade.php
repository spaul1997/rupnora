@extends('admin.layouts.app')

@section('title', 'Customers')

@section('content')
    <x-admin.page-header title="Customers" description="View and manage registered customers." />

    <form method="GET" class="admin-card mb-5 flex flex-wrap items-center gap-3 p-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email or phone..." class="admin-input max-w-xs">
        <select name="is_active" class="admin-select max-w-[150px]">
            <option value="">All Status</option>
            <option value="1" @selected(request('is_active') === '1')>Active</option>
            <option value="0" @selected(request('is_active') === '0')>Inactive</option>
        </select>
        <button type="submit" class="admin-btn-secondary">Filter</button>
        @if (request()->hasAny(['search', 'is_active']))
            <a href="{{ route('admin.customers.index') }}" class="admin-btn-ghost">Clear</a>
        @endif
    </form>

    @if ($customers->isEmpty())
        <x-admin.empty-state title="No customers found" />
    @else
        <x-admin.table :headers="['Name', 'Email', 'Phone', 'Registered', 'Orders', 'Total Spend', 'Status', '!Actions']">
            @foreach ($customers as $customer)
                <tr>
                    <td class="font-medium text-gray-900">{{ $customer->name }}</td>
                    <td class="text-gray-500">{{ $customer->email }}</td>
                    <td>{{ $customer->phone ?? '—' }}</td>
                    <td class="text-gray-400">{{ $customer->created_at->format('d M Y') }}</td>
                    <td>{{ $customer->orders_count }}</td>
                    <td class="font-medium text-gray-900">₹{{ number_format($customer->total_spend ?? 0) }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.customers.toggle-active', $customer) }}">
                            @csrf @method('PATCH')
                            <button type="submit"><x-admin.status-badge :status="$customer->is_active ? 'active' : 'inactive'" /></button>
                        </form>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="text-sm font-medium text-gray-500 hover:text-gray-800">View</a>
                            <a href="{{ route('admin.customers.edit', $customer) }}" class="text-sm font-medium text-champagne-dark hover:underline">Edit</a>
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$customers" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

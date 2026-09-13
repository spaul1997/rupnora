@extends('admin.layouts.app')

@section('title', 'Customer Feedback')

@section('content')
    <x-admin.page-header title="Customer Feedback" description="Review and respond to customer feedback." />

    <form method="GET" class="admin-card mb-5 flex flex-wrap items-center gap-3 p-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search feedback..." class="admin-input max-w-xs">
        <select name="type" class="admin-select max-w-[160px]">
            <option value="">All Types</option>
            @foreach (['general', 'website', 'product', 'delivery', 'payment', 'complaint', 'suggestion', 'other'] as $type)
                <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        <select name="status" class="admin-select max-w-[150px]">
            <option value="">All Statuses</option>
            @foreach (['new', 'in_progress', 'resolved', 'closed'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="admin-btn-secondary">Filter</button>
        @if (request()->hasAny(['search', 'type', 'status']))
            <a href="{{ route('admin.feedbacks.index') }}" class="admin-btn-ghost">Clear</a>
        @endif
    </form>

    @if ($feedbacks->isEmpty())
        <x-admin.empty-state title="No feedback found" />
    @else
        <x-admin.table :headers="['Customer', 'Type', 'Subject', 'Assigned To', 'Date', 'Status', '!Actions']">
            @foreach ($feedbacks as $feedback)
                <tr>
                    <td>
                        <p class="font-medium text-gray-900">{{ $feedback->name }}</p>
                        <p class="text-xs text-gray-400">{{ $feedback->email }}</p>
                    </td>
                    <td class="capitalize">{{ $feedback->type }}</td>
                    <td class="max-w-xs truncate">{{ $feedback->subject }}</td>
                    <td>{{ $feedback->assignedTo?->name ?? '—' }}</td>
                    <td class="text-gray-400">{{ $feedback->created_at->format('d M Y') }}</td>
                    <td><x-admin.status-badge :status="$feedback->status" /></td>
                    <td class="text-right">
                        <a href="{{ route('admin.feedbacks.show', $feedback) }}" class="text-sm font-medium text-champagne-dark hover:underline">View</a>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$feedbacks" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

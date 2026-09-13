@extends('admin.layouts.app')

@section('title', 'Contact Requests')

@section('content')
    <x-admin.page-header title="Contact Requests" description="Manage customer support tickets." />

    <form method="GET" class="admin-card mb-5 flex flex-wrap items-center gap-3 p-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by ticket, name, email..." class="admin-input max-w-xs">
        <select name="priority" class="admin-select max-w-[140px]">
            <option value="">All Priority</option>
            @foreach (['low', 'normal', 'high', 'urgent'] as $priority)
                <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ ucfirst($priority) }}</option>
            @endforeach
        </select>
        <select name="status" class="admin-select max-w-[170px]">
            <option value="">All Statuses</option>
            @foreach (['new', 'open', 'in_progress', 'waiting_customer', 'resolved', 'closed'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="admin-btn-secondary">Filter</button>
        @if (request()->hasAny(['search', 'priority', 'status']))
            <a href="{{ route('admin.contacts.index') }}" class="admin-btn-ghost">Clear</a>
        @endif
    </form>

    @if ($contacts->isEmpty())
        <x-admin.empty-state title="No contact requests found" />
    @else
        <x-admin.table :headers="['Ticket No', 'Name', 'Subject', 'Priority', 'Assigned To', 'Date', 'Status', '!Actions']">
            @foreach ($contacts as $contact)
                <tr>
                    <td class="font-medium text-gray-900">{{ $contact->ticket_no }}</td>
                    <td>
                        <p>{{ $contact->name }}</p>
                        <p class="text-xs text-gray-400">{{ $contact->email }}</p>
                    </td>
                    <td class="max-w-xs truncate">{{ $contact->subject }}</td>
                    <td><x-admin.status-badge :status="$contact->priority" /></td>
                    <td>{{ $contact->assignedTo?->name ?? '—' }}</td>
                    <td class="text-gray-400">{{ $contact->created_at->format('d M Y') }}</td>
                    <td><x-admin.status-badge :status="$contact->status" /></td>
                    <td class="text-right">
                        <a href="{{ route('admin.contacts.show', $contact) }}" class="text-sm font-medium text-champagne-dark hover:underline">View</a>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$contacts" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

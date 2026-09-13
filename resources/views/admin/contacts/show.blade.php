@extends('admin.layouts.app')

@section('title', $contact->ticket_no)

@section('content')
    <x-admin.page-header :title="$contact->ticket_no" :breadcrumb="[['label' => 'Contact Requests', 'url' => route('admin.contacts.index')], ['label' => $contact->ticket_no]]" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="admin-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">{{ $contact->subject }}</p>
                        <p class="text-sm text-gray-500">{{ $contact->name }} &middot; {{ $contact->email }} @if($contact->phone) &middot; {{ $contact->phone }} @endif</p>
                    </div>
                    <div class="flex gap-1.5">
                        <x-admin.status-badge :status="$contact->priority" />
                        <x-admin.status-badge :status="$contact->status" />
                    </div>
                </div>
                <p class="mt-4 text-sm leading-relaxed text-gray-600">{{ $contact->message }}</p>
                <p class="mt-3 text-xs text-gray-400">Submitted {{ $contact->created_at->format('d M Y, h:i A') }}</p>
            </div>

            @if ($contact->admin_reply)
                <div class="admin-card border-champagne-light bg-ivory-soft p-6">
                    <p class="text-xs font-semibold uppercase text-champagne-dark">Reply Sent</p>
                    <p class="mt-1.5 text-sm text-gray-700">{{ $contact->admin_reply }}</p>
                    <p class="mt-2 text-xs text-gray-400">{{ $contact->replied_at?->format('d M Y, h:i A') }}</p>
                </div>
            @endif

            <div class="admin-card p-6">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Reply to Customer</h3>
                <form method="POST" action="{{ route('admin.contacts.reply', $contact) }}" class="space-y-3">
                    @csrf
                    <textarea name="admin_reply" rows="4" class="admin-textarea resize-none" placeholder="Write your reply...">{{ old('admin_reply', $contact->admin_reply) }}</textarea>
                    <button type="submit" class="admin-btn-primary">Send Reply</button>
                </form>
            </div>

            @if ($contact->admin_note)
                <div class="admin-card bg-gray-50 p-6">
                    <p class="text-xs font-semibold uppercase text-gray-500">Internal Note</p>
                    <p class="mt-1.5 text-sm text-gray-700">{{ $contact->admin_note }}</p>
                </div>
            @endif

            <div class="admin-card p-6">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Add Internal Note</h3>
                <form method="POST" action="{{ route('admin.contacts.update', $contact) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <textarea name="admin_note" rows="3" class="admin-textarea resize-none" placeholder="Internal note (not visible to customer)...">{{ old('admin_note', $contact->admin_note) }}</textarea>
                    <button type="submit" class="admin-btn-secondary">Save Note</button>
                </form>
            </div>
        </div>

        <div class="space-y-4">
            <div class="admin-card space-y-4 p-6">
                <h3 class="text-sm font-semibold text-gray-900">Assign Staff</h3>
                <form method="POST" action="{{ route('admin.contacts.assign', $contact) }}" class="space-y-3">
                    @csrf @method('PATCH')
                    <x-admin.form.select name="assigned_to" :value="$contact->assigned_to" :options="$admins->pluck('name', 'id')" placeholder="Select staff" />
                    <button type="submit" class="admin-btn-secondary w-full">Assign</button>
                </form>
            </div>

            <div class="admin-card space-y-4 p-6">
                <h3 class="text-sm font-semibold text-gray-900">Priority</h3>
                <form method="POST" action="{{ route('admin.contacts.update-priority', $contact) }}" class="space-y-3">
                    @csrf @method('PATCH')
                    <x-admin.form.select name="priority" :value="$contact->priority" :options="['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent']" :placeholder="null" />
                    <button type="submit" class="admin-btn-secondary w-full">Update Priority</button>
                </form>
            </div>

            <div class="admin-card space-y-4 p-6">
                <h3 class="text-sm font-semibold text-gray-900">Status</h3>
                <form method="POST" action="{{ route('admin.contacts.update-status', $contact) }}" class="space-y-3">
                    @csrf @method('PATCH')
                    <x-admin.form.select name="status" :value="$contact->status" :options="['new' => 'New', 'open' => 'Open', 'in_progress' => 'In Progress', 'waiting_customer' => 'Waiting on Customer', 'resolved' => 'Resolved', 'closed' => 'Closed']" :placeholder="null" />
                    <button type="submit" class="admin-btn-secondary w-full">Update Status</button>
                </form>
                <form method="POST" action="{{ route('admin.contacts.close', $contact) }}"><button type="submit" class="admin-btn-danger w-full">@csrf @method('PATCH') Close Ticket</button></form>
            </div>
        </div>
    </div>
@endsection

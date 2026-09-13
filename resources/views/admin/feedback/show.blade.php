@extends('admin.layouts.app')

@section('title', 'Feedback Details')

@section('content')
    <x-admin.page-header title="Feedback Details" :breadcrumb="[['label' => 'Feedback', 'url' => route('admin.feedbacks.index')], ['label' => '#'.$feedback->id]]" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="admin-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">{{ $feedback->subject }}</p>
                        <p class="text-sm text-gray-500">{{ $feedback->name }} &middot; {{ $feedback->email }} @if($feedback->phone) &middot; {{ $feedback->phone }} @endif</p>
                    </div>
                    <x-admin.status-badge :status="$feedback->status" />
                </div>
                <span class="admin-badge mt-3 inline-block bg-gray-100 text-gray-600 capitalize">{{ $feedback->type }}</span>
                <p class="mt-4 text-sm leading-relaxed text-gray-600">{{ $feedback->message }}</p>
                <p class="mt-3 text-xs text-gray-400">Submitted {{ $feedback->created_at->format('d M Y, h:i A') }}</p>
            </div>

            @if ($feedback->admin_note)
                <div class="admin-card border-champagne-light bg-ivory-soft p-6">
                    <p class="text-xs font-semibold uppercase text-champagne-dark">Internal Note</p>
                    <p class="mt-1.5 text-sm text-gray-700">{{ $feedback->admin_note }}</p>
                </div>
            @endif

            <div class="admin-card p-6">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Add Internal Note</h3>
                <form method="POST" action="{{ route('admin.feedbacks.note', $feedback) }}" class="space-y-3">
                    @csrf
                    <textarea name="admin_note" rows="3" class="admin-textarea resize-none" placeholder="Internal note (not visible to customer)...">{{ old('admin_note', $feedback->admin_note) }}</textarea>
                    <button type="submit" class="admin-btn-primary">Save Note</button>
                </form>
            </div>
        </div>

        <div class="space-y-4">
            <div class="admin-card space-y-4 p-6">
                <h3 class="text-sm font-semibold text-gray-900">Assign</h3>
                <form method="POST" action="{{ route('admin.feedbacks.assign', $feedback) }}" class="space-y-3">
                    @csrf @method('PATCH')
                    <x-admin.form.select name="assigned_to" :value="$feedback->assigned_to" :options="$admins->pluck('name', 'id')" placeholder="Select staff" />
                    <button type="submit" class="admin-btn-secondary w-full">Assign</button>
                </form>
            </div>

            <div class="admin-card space-y-3 p-6">
                <h3 class="text-sm font-semibold text-gray-900">Status</h3>
                <form method="POST" action="{{ route('admin.feedbacks.resolve', $feedback) }}"><button type="submit" class="admin-btn-secondary w-full">@csrf @method('PATCH') Mark Resolved</button></form>
                <form method="POST" action="{{ route('admin.feedbacks.close', $feedback) }}"><button type="submit" class="admin-btn-secondary w-full">@csrf @method('PATCH') Close</button></form>
                <x-admin.confirm-modal :action="route('admin.feedbacks.destroy', $feedback)" title="Delete Feedback" message="Are you sure you want to delete this feedback entry?" trigger-class="admin-btn-danger w-full" trigger-label="Delete" />
            </div>
        </div>
    </div>
@endsection

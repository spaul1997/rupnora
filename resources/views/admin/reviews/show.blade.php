@extends('admin.layouts.app')

@section('title', 'Review Details')

@section('content')
    <x-admin.page-header title="Review Details" :breadcrumb="[['label' => 'Reviews', 'url' => route('admin.reviews.index')], ['label' => '#'.$review->id]]" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="admin-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">{{ $review->customer?->name }}</p>
                        <p class="text-sm text-gray-500">reviewing {{ $review->product?->name }}</p>
                    </div>
                    <x-admin.status-badge :status="$review->status" />
                </div>
                <div class="mt-3 flex items-center gap-0.5 text-amber-400">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="h-4 w-4 {{ $i <= $review->rating ? '' : 'text-gray-200' }}" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.6 6.1.6-4.6 4.2 1.3 6.1L10 15l-5.4 3 1.3-6.1L1.3 7.7l6.1-.6L10 1.5z" /></svg>
                    @endfor
                </div>
                @if ($review->title)
                    <p class="mt-3 font-medium text-gray-900">{{ $review->title }}</p>
                @endif
                <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $review->review }}</p>
                <p class="mt-3 text-xs text-gray-400">Submitted {{ $review->created_at->format('d M Y, h:i A') }}</p>
            </div>

            @if ($review->admin_reply)
                <div class="admin-card border-champagne-light bg-ivory-soft p-6">
                    <p class="text-xs font-semibold uppercase text-champagne-dark">Admin Reply</p>
                    <p class="mt-1.5 text-sm text-gray-700">{{ $review->admin_reply }}</p>
                </div>
            @endif

            <div class="admin-card p-6">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Reply to this Review</h3>
                <form method="POST" action="{{ route('admin.reviews.reply', $review) }}" class="space-y-3">
                    @csrf
                    <textarea name="admin_reply" rows="3" class="admin-textarea resize-none" placeholder="Write a public reply...">{{ old('admin_reply', $review->admin_reply) }}</textarea>
                    <button type="submit" class="admin-btn-primary">Post Reply</button>
                </form>
            </div>
        </div>

        <div class="space-y-4">
            <div class="admin-card space-y-3 p-6">
                <h3 class="text-sm font-semibold text-gray-900">Actions</h3>
                <form method="POST" action="{{ route('admin.reviews.approve', $review) }}"><button type="submit" class="admin-btn-secondary w-full">@csrf Approve</button></form>
                <form method="POST" action="{{ route('admin.reviews.reject', $review) }}"><button type="submit" class="admin-btn-secondary w-full">@csrf Reject</button></form>
                <form method="POST" action="{{ route('admin.reviews.hide', $review) }}"><button type="submit" class="admin-btn-secondary w-full">@csrf Hide</button></form>
                <x-admin.confirm-modal :action="route('admin.reviews.destroy', $review)" title="Delete Review" message="Are you sure you want to delete this review?" trigger-class="admin-btn-danger w-full" trigger-label="Delete Review" />
            </div>
            @if ($review->order)
                <div class="admin-card p-6">
                    <h3 class="mb-2 text-sm font-semibold text-gray-900">Related Order</h3>
                    <a href="{{ route('admin.orders.show', $review->order) }}" class="text-sm font-medium text-champagne-dark hover:underline">{{ $review->order->order_number }}</a>
                </div>
            @endif
        </div>
    </div>
@endsection

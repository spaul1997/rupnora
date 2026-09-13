@extends('admin.layouts.app')

@section('title', 'Product Reviews')

@section('content')
    <x-admin.page-header title="Product Reviews" description="Moderate customer reviews before they appear on the storefront." />

    <form method="GET" class="admin-card mb-5 flex flex-wrap items-center gap-3 p-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by product or customer..." class="admin-input max-w-xs">
        <select name="status" class="admin-select max-w-[160px]">
            <option value="">All Statuses</option>
            @foreach (['pending', 'approved', 'rejected', 'hidden'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <select name="rating" class="admin-select max-w-[130px]">
            <option value="">All Ratings</option>
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}" @selected(request('rating') == $i)>{{ $i }} Star</option>
            @endfor
        </select>
        <button type="submit" class="admin-btn-secondary">Filter</button>
        @if (request()->hasAny(['search', 'status', 'rating']))
            <a href="{{ route('admin.reviews.index') }}" class="admin-btn-ghost">Clear</a>
        @endif
    </form>

    @if ($reviews->isEmpty())
        <x-admin.empty-state title="No reviews found" />
    @else
        <x-admin.table :headers="['Customer', 'Product', 'Rating', 'Review', 'Date', 'Status', '!Actions']">
            @foreach ($reviews as $review)
                <tr>
                    <td class="font-medium text-gray-900">{{ $review->customer?->name }}</td>
                    <td>{{ $review->product?->name }}</td>
                    <td>
                        <span class="flex items-center gap-0.5 text-amber-400">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="h-3.5 w-3.5 {{ $i <= $review->rating ? '' : 'text-gray-200' }}" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.6 6.1.6-4.6 4.2 1.3 6.1L10 15l-5.4 3 1.3-6.1L1.3 7.7l6.1-.6L10 1.5z" /></svg>
                            @endfor
                        </span>
                    </td>
                    <td class="max-w-xs truncate">{{ $review->review }}</td>
                    <td class="text-gray-400">{{ $review->created_at->format('d M Y') }}</td>
                    <td><x-admin.status-badge :status="$review->status" /></td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2.5">
                            <a href="{{ route('admin.reviews.show', $review) }}" class="text-sm font-medium text-gray-500 hover:text-gray-800">View</a>
                            @if ($review->status !== 'approved')
                                <form method="POST" action="{{ route('admin.reviews.approve', $review) }}"><button type="submit" class="text-sm font-medium text-success hover:underline">@csrf Approve</button></form>
                            @endif
                            @if ($review->status !== 'rejected')
                                <form method="POST" action="{{ route('admin.reviews.reject', $review) }}"><button type="submit" class="text-sm font-medium text-error hover:underline">@csrf Reject</button></form>
                            @endif
                            <x-admin.confirm-modal :action="route('admin.reviews.destroy', $review)" title="Delete Review" message="Are you sure you want to delete this review?" />
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$reviews" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

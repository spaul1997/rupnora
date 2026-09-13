@extends('admin.layouts.app')

@section('title', $customer->name)

@section('content')
    <x-admin.page-header :title="$customer->name" :breadcrumb="[['label' => 'Customers', 'url' => route('admin.customers.index')], ['label' => $customer->name]]">
        <x-slot:actions>
            <a href="{{ route('admin.customers.edit', $customer) }}" class="admin-btn-primary">Edit Customer</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="admin-card p-6">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Profile</h3>
                <dl class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div><dt class="text-xs text-gray-400">Email</dt><dd class="font-medium text-gray-800">{{ $customer->email }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Phone</dt><dd class="font-medium text-gray-800">{{ $customer->phone ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Registered</dt><dd class="font-medium text-gray-800">{{ $customer->created_at->format('d M Y') }}</dd></div>
                </dl>
            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Recent Orders</h3>
                @if ($orders->isEmpty())
                    <x-admin.empty-state title="No orders yet" />
                @else
                    <x-admin.table :headers="['Order ID', 'Date', 'Status', 'Payment', '!Total']">
                        @foreach ($orders as $order)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-champagne-dark hover:underline">{{ $order->order_number }}</a></td>
                                <td class="text-gray-500">{{ $order->created_at->format('d M Y') }}</td>
                                <td><x-admin.status-badge :status="$order->status" /></td>
                                <td><x-admin.status-badge :status="$order->payment_status" /></td>
                                <td class="text-right font-medium text-gray-900">₹{{ number_format($order->grand_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </x-admin.table>
                @endif
            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Reviews</h3>
                @if ($reviews->isEmpty())
                    <x-admin.empty-state title="No reviews yet" />
                @else
                    <div class="space-y-3">
                        @foreach ($reviews as $review)
                            <div class="admin-card p-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-800">{{ $review->product?->name }}</p>
                                    <x-admin.status-badge :status="$review->status" />
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ $review->review }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Feedback</h3>
                @if ($feedbacks->isEmpty())
                    <x-admin.empty-state title="No feedback submitted" />
                @else
                    <div class="space-y-3">
                        @foreach ($feedbacks as $feedback)
                            <div class="admin-card p-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-800">{{ $feedback->subject }}</p>
                                    <x-admin.status-badge :status="$feedback->status" />
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ $feedback->message }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <x-admin.stat-card label="Total Orders" :value="$customer->orders_count" icon="M3 7h13l1.5 12h-16z" />
            <x-admin.stat-card label="Total Spend" value="₹{{ number_format($totalSpend, 2) }}" icon="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6l8-4z" tone="success" />
            <div class="admin-card p-5">
                <form method="POST" action="{{ route('admin.customers.toggle-active', $customer) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="admin-btn-secondary w-full">{{ $customer->is_active ? 'Deactivate Customer' : 'Activate Customer' }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection

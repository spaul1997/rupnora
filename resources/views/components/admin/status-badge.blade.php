@props(['status'])

@php
    $map = [
        // order status
        'pending' => 'bg-amber-100 text-amber-700',
        'confirmed' => 'bg-blue-100 text-blue-700',
        'processing' => 'bg-blue-100 text-blue-700',
        'packed' => 'bg-indigo-100 text-indigo-700',
        'shipped' => 'bg-indigo-100 text-indigo-700',
        'out_for_delivery' => 'bg-purple-100 text-purple-700',
        'delivered' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-red-100 text-red-700',
        'return_requested' => 'bg-orange-100 text-orange-700',
        'returned' => 'bg-orange-100 text-orange-700',
        'refunded' => 'bg-red-100 text-red-700',

        // payment status
        'paid' => 'bg-green-100 text-green-700',
        'failed' => 'bg-red-100 text-red-700',
        'partial_refund' => 'bg-orange-100 text-orange-700',
        'cod' => 'bg-gray-100 text-gray-700',

        // review / feedback / contact status
        'approved' => 'bg-green-100 text-green-700',
        'rejected' => 'bg-red-100 text-red-700',
        'hidden' => 'bg-gray-100 text-gray-600',
        'new' => 'bg-blue-100 text-blue-700',
        'in_progress' => 'bg-amber-100 text-amber-700',
        'resolved' => 'bg-green-100 text-green-700',
        'closed' => 'bg-gray-100 text-gray-600',
        'open' => 'bg-blue-100 text-blue-700',
        'waiting_customer' => 'bg-purple-100 text-purple-700',

        // career application status
        'reviewing' => 'bg-amber-100 text-amber-700',
        'shortlisted' => 'bg-purple-100 text-purple-700',
        'hired' => 'bg-green-100 text-green-700',

        // priority
        'low' => 'bg-gray-100 text-gray-600',
        'normal' => 'bg-blue-100 text-blue-700',
        'high' => 'bg-orange-100 text-orange-700',
        'urgent' => 'bg-red-100 text-red-700',

        // stock / generic
        'in_stock' => 'bg-green-100 text-green-700',
        'low_stock' => 'bg-amber-100 text-amber-700',
        'out_of_stock' => 'bg-red-100 text-red-700',
        'active' => 'bg-green-100 text-green-700',
        'inactive' => 'bg-gray-100 text-gray-600',
    ];

    $tone = $map[$status] ?? 'bg-gray-100 text-gray-600';
    $label = ucwords(str_replace('_', ' ', $status));
@endphp

<span class="admin-badge {{ $tone }}">{{ $label }}</span>

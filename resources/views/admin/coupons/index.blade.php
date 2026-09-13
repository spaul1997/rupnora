@extends('admin.layouts.app')

@section('title', 'Coupons')

@section('content')
    <x-admin.page-header title="Coupons" description="Manage discount coupons and promotions.">
        <x-slot:actions>
            <a href="{{ route('admin.coupons.create') }}" class="admin-btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                Add Coupon
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($coupons->isEmpty())
        <x-admin.empty-state title="No coupons found">
            <x-slot:actions><a href="{{ route('admin.coupons.create') }}" class="admin-btn-primary">Add Coupon</a></x-slot:actions>
        </x-admin.empty-state>
    @else
        <x-admin.table :headers="['Code', 'Discount', 'Min. Order', 'Validity', 'Usage', 'Status', '!Actions']">
            @foreach ($coupons as $coupon)
                <tr>
                    <td class="font-mono font-semibold text-gray-900">{{ $coupon->code }}</td>
                    <td>{{ $coupon->discount_type === 'percentage' ? $coupon->discount_value.'%' : '₹'.number_format($coupon->discount_value) }}</td>
                    <td>{{ $coupon->minimum_order ? '₹'.number_format($coupon->minimum_order) : '—' }}</td>
                    <td class="text-gray-500">{{ $coupon->start_date->format('d M Y') }} – {{ $coupon->end_date->format('d M Y') }}</td>
                    <td>{{ $coupon->used_count }}@if($coupon->usage_limit) / {{ $coupon->usage_limit }}@endif</td>
                    <td>
                        <form method="POST" action="{{ route('admin.coupons.toggle-active', $coupon) }}">
                            @csrf @method('PATCH')
                            <button type="submit"><x-admin.status-badge :status="$coupon->is_active && ! $coupon->is_expired ? 'active' : 'inactive'" /></button>
                        </form>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-sm font-medium text-champagne-dark hover:underline">Edit</a>
                            <x-admin.confirm-modal :action="route('admin.coupons.destroy', $coupon)" title="Delete Coupon" :message="'Are you sure you want to delete \''.$coupon->code.'\'?'" />
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$coupons" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

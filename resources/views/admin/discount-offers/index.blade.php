@extends('admin.layouts.app')

@section('title', 'Discount & Offers')

@section('content')
    <x-admin.page-header title="Discount & Offers" description="Manage category-wise and product-wise discount campaigns.">
        <x-slot:actions>
            <a href="{{ route('admin.discount-offers.create') }}" class="admin-btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                Create Offer
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="admin-card mb-5 flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search offer reason..." class="admin-input sm:max-w-xs">
        <select name="scope_type" class="admin-select sm:max-w-48">
            <option value="">All scopes</option>
            <option value="category" @selected(request('scope_type') === 'category')>Category wise</option>
            <option value="product" @selected(request('scope_type') === 'product')>Product wise</option>
        </select>
        <select name="status" class="admin-select sm:max-w-40">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="upcoming" @selected(request('status') === 'upcoming')>Upcoming</option>
            <option value="expired" @selected(request('status') === 'expired')>Expired</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
        <div class="flex gap-2">
            <button type="submit" class="admin-btn-primary">Filter</button>
            @if (request()->hasAny(['search', 'scope_type', 'status']))
                <a href="{{ route('admin.discount-offers.index') }}" class="admin-btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    @if ($offers->isEmpty())
        <x-admin.empty-state title="No offers found" description="Create a category-wise or product-wise discount offer.">
            <x-slot:actions><a href="{{ route('admin.discount-offers.create') }}" class="admin-btn-primary">Create Offer</a></x-slot:actions>
        </x-admin.empty-state>
    @else
        <x-admin.table :headers="['Offer Reason', 'Discount', 'Scope', 'Products', 'Duration', 'Status', '!Actions']">
            @foreach ($offers as $offer)
                @php
                    $discountTargets = $offer->scope_type === 'category' ? $offer->categories : $offer->includedProducts;
                    $discountLabels = $discountTargets->map(function ($target) {
                        $type = $target->pivot->discount_type;
                        $value = rtrim(rtrim(number_format((float) $target->pivot->discount_value, 2), '0'), '.');

                        return $type === 'percentage' ? $value.'%' : '₹'.number_format((float) $target->pivot->discount_value, 2);
                    })->unique()->values();
                @endphp
                <tr>
                    <td>
                        <span class="font-medium text-gray-900">{{ $offer->reason }}</span>
                        <span class="mt-1 block text-xs text-gray-400">Created {{ $offer->created_at->format('d M Y') }}</span>
                    </td>
                    <td class="whitespace-nowrap font-medium text-gray-900">
                        {{ $discountLabels->count() === 1 ? $discountLabels->first() : $discountLabels->count().' different discounts' }}
                        <span class="mt-1 block text-xs font-normal text-gray-400">Per {{ $offer->scope_type }}</span>
                    </td>
                    <td>
                        <span class="capitalize">{{ $offer->scope_type }} wise</span>
                        @if ($offer->scope_type === 'category')
                            <span class="mt-1 block max-w-52 truncate text-xs text-gray-400" title="{{ $offer->categories->pluck('name')->join(', ') }}">{{ $offer->categories->pluck('name')->join(', ') }}</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap">
                        {{ $offer->target_products_count }}
                        @if ($offer->scope_type === 'category' && $offer->excludedProducts->isNotEmpty())
                            <span class="mt-1 block text-xs text-gray-400">{{ $offer->excludedProducts->count() }} excluded</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap text-sm text-gray-600">
                        {{ $offer->start_date->format('d M Y') }}
                        <span class="block text-xs text-gray-400">to {{ $offer->end_date->format('d M Y') }}</span>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.discount-offers.toggle-active', $offer) }}">
                            @csrf
                            @method('PATCH')
                            <button
                                type="submit"
                                class="cursor-pointer rounded-full focus:outline-none focus:ring-2 focus:ring-champagne-dark/40 focus:ring-offset-2"
                                title="Click to {{ $offer->is_active ? 'deactivate' : 'activate' }} this offer"
                                aria-label="{{ $offer->is_active ? 'Deactivate' : 'Activate' }} {{ $offer->reason }}"
                            >
                                <x-admin.status-badge :status="$offer->status()" />
                            </button>
                        </form>
                    </td>
                    <td class="text-right">
                        <a href="{{ route('admin.discount-offers.edit', $offer) }}" class="text-sm font-medium text-champagne-dark hover:underline">Edit</a>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$offers" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

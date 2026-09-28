@extends('admin.layouts.app')

@section('title', 'Influencers')

@section('content')
    <x-admin.page-header title="Influencers" description="Review creator applications and manage approved influencer partners.">
        <x-slot:actions>
            <a href="{{ route('admin.influencers.create') }}" class="admin-btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                Add Influencer
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Total', 'value' => $counts['total']],
            ['label' => 'New Applications', 'value' => $counts['new']],
            ['label' => 'Approved', 'value' => $counts['approved']],
            ['label' => 'Active Partners', 'value' => $counts['active']],
        ] as $stat)
            <div class="admin-card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $stat['label'] }}</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($stat['value']) }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" class="admin-card mb-5 flex flex-wrap items-center gap-3 p-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, handle, coupon..." class="admin-input max-w-sm">
        <select name="status" class="admin-select max-w-[180px]">
            <option value="">All Statuses</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="platform" class="admin-select max-w-[180px]">
            <option value="">All Platforms</option>
            @foreach ($platforms as $value => $label)
                <option value="{{ $value }}" @selected(request('platform') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="partner" class="admin-select max-w-[180px]">
            <option value="">All Partners</option>
            <option value="active" @selected(request('partner') === 'active')>Active Partners</option>
            <option value="inactive" @selected(request('partner') === 'inactive')>Inactive Partners</option>
        </select>
        <button type="submit" class="admin-btn-secondary">Filter</button>
        @if (request()->hasAny(['search', 'status', 'platform', 'partner']))
            <a href="{{ route('admin.influencers.index') }}" class="admin-btn-ghost">Clear</a>
        @endif
    </form>

    @if ($influencers->isEmpty())
        <x-admin.empty-state title="No influencers found" description="Applications submitted from the Influencer Program page will appear here.">
            <x-slot:actions>
                <a href="{{ route('admin.influencers.create') }}" class="admin-btn-primary">Add Influencer</a>
            </x-slot:actions>
        </x-admin.empty-state>
    @else
        <x-admin.table :headers="['Creator', 'Platform', 'Audience', 'Status', 'Partner', 'Applied', '!Actions']">
            @foreach ($influencers as $influencer)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            @if ($influencer->profile_image_url)
                                <x-ui.optimized-image :src="$influencer->profile_image_url" :alt="$influencer->full_name" sizes="40px" class="h-10 w-10 rounded-full object-cover" />
                            @else
                                <span class="flex h-10 w-10 flex-none items-center justify-center rounded-full bg-champagne-light text-sm font-semibold text-champagne-dark">{{ str($influencer->full_name)->substr(0, 1)->upper() }}</span>
                            @endif
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900">{{ $influencer->full_name }}</p>
                                <p class="max-w-56 truncate text-xs text-gray-400">{{ $influencer->email }}</p>
                                <p class="text-[11px] text-gray-400">{{ $influencer->reference_no }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="text-gray-700">{{ $platforms[$influencer->primary_platform] ?? str($influencer->primary_platform)->headline() }}</p>
                        <p class="text-xs text-gray-400">{{ $influencer->social_handle }}</p>
                    </td>
                    <td class="whitespace-nowrap">{{ number_format($influencer->followers_count) }}</td>
                    <td><x-admin.status-badge :status="$influencer->status" /></td>
                    <td><x-admin.status-badge :status="$influencer->is_active ? 'active' : 'inactive'" /></td>
                    <td class="whitespace-nowrap text-gray-400">{{ $influencer->created_at->format('d M Y') }}</td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.influencers.show', $influencer) }}" class="text-sm font-medium text-champagne-dark hover:underline">View</a>
                            <a href="{{ route('admin.influencers.edit', $influencer) }}" class="text-sm font-medium text-gray-600 hover:underline">Edit</a>
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$influencers" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

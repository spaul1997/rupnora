@extends('admin.layouts.app')

@section('title', 'Email Marketing')

@section('content')
    <x-admin.page-header title="Email Marketing" description="Create branded campaigns and monitor queued email delivery.">
        <x-slot:actions>
            <a href="{{ route('admin.email-campaigns.create') }}" class="admin-btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                New Campaign
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-card mb-5 flex flex-col gap-3 border-l-4 border-l-champagne-dark p-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-medium text-gray-800">Queue-powered delivery</p>
            <p class="mt-1 text-xs text-gray-500">Every recipient receives a separate email. All campaign emails CC {{ config('marketing.cc_email') }}.</p>
        </div>
        <span class="admin-badge bg-green-100 text-green-700">Database Queue</span>
    </div>

    <form method="GET" class="admin-card mb-5 flex flex-wrap items-center gap-3 p-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search campaigns..." class="admin-input max-w-sm">
        <select name="template" class="admin-select max-w-[260px]">
            <option value="">All Templates</option>
            @foreach ($templates as $value => $label)
                <option value="{{ $value }}" @selected(request('template') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="admin-select max-w-[180px]">
            <option value="">All Statuses</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="admin-btn-secondary">Filter</button>
        @if (request()->hasAny(['search', 'template', 'status']))
            <a href="{{ route('admin.email-campaigns.index') }}" class="admin-btn-ghost">Clear</a>
        @endif
    </form>

    @if ($campaigns->isEmpty())
        <x-admin.empty-state title="No email campaigns found" description="Choose one of the branded templates and queue your first campaign.">
            <x-slot:actions>
                <a href="{{ route('admin.email-campaigns.create') }}" class="admin-btn-primary">Create Campaign</a>
            </x-slot:actions>
        </x-admin.empty-state>
    @else
        <x-admin.table :headers="['Campaign', 'Template', 'Delivery', 'Status', 'Created', '!Actions']">
            @foreach ($campaigns as $campaign)
                @php($progress = $campaign->total_recipient_count > 0 ? round(($campaign->sent_count / $campaign->total_recipient_count) * 100) : 0)
                <tr>
                    <td>
                        <p class="font-medium text-gray-900">{{ $campaign->name }}</p>
                        <p class="max-w-sm truncate text-xs text-gray-400">{{ $campaign->subject }}</p>
                    </td>
                    <td class="whitespace-nowrap text-sm text-gray-600">{{ $templates[$campaign->template_type] ?? str($campaign->template_type)->headline() }}</td>
                    <td class="min-w-48">
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>{{ $campaign->sent_count }} / {{ $campaign->total_recipient_count }} sent</span>
                            @if ($campaign->failed_count > 0)<span class="text-error">{{ $campaign->failed_count }} failed</span>@endif
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-champagne-dark" style="width: {{ $progress }}%"></div>
                        </div>
                    </td>
                    <td><x-admin.status-badge :status="$campaign->status" /></td>
                    <td class="whitespace-nowrap text-gray-400">
                        {{ $campaign->created_at->format('d M Y, h:i A') }}
                        <span class="block text-xs">{{ $campaign->creator?->name ?: 'System' }}</span>
                    </td>
                    <td class="text-right">
                        <a href="{{ route('admin.email-campaigns.show', $campaign) }}" class="text-sm font-medium text-champagne-dark hover:underline">View</a>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$campaigns" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

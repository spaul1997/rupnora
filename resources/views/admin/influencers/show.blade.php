@extends('admin.layouts.app')

@section('title', $influencer->full_name)

@section('content')
    <x-admin.page-header
        :title="$influencer->full_name"
        :description="$influencer->reference_no"
        :breadcrumb="[
            ['label' => 'Influencers', 'url' => route('admin.influencers.index')],
            ['label' => $influencer->full_name],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('admin.influencers.edit', $influencer) }}" class="admin-btn-primary">Edit Influencer</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="admin-card p-6">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                    @if ($influencer->profile_image_url)
                        <x-ui.optimized-image :src="$influencer->profile_image_url" :alt="$influencer->full_name" sizes="96px" class="h-24 w-24 flex-none rounded-2xl object-cover" />
                    @else
                        <span class="flex h-24 w-24 flex-none items-center justify-center rounded-2xl bg-champagne-light text-3xl font-semibold text-champagne-dark">{{ str($influencer->full_name)->substr(0, 1)->upper() }}</span>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-semibold text-gray-900">{{ $influencer->full_name }}</h2>
                            <x-admin.status-badge :status="$influencer->status" />
                            <x-admin.status-badge :status="$influencer->is_active ? 'active' : 'inactive'" />
                        </div>
                        <p class="mt-1 text-sm text-gray-500">{{ \App\Models\Influencer::PLATFORMS[$influencer->primary_platform] ?? str($influencer->primary_platform)->headline() }} &middot; {{ $influencer->social_handle }}</p>
                        <p class="mt-2 text-sm text-gray-600">{{ $influencer->content_niche }}</p>
                    </div>
                </div>

                <dl class="mt-7 grid grid-cols-1 gap-x-6 gap-y-5 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Email</dt>
                        <dd class="mt-1"><a href="mailto:{{ $influencer->email }}" class="text-champagne-dark hover:underline">{{ $influencer->email }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Phone</dt>
                        <dd class="mt-1"><a href="tel:{{ $influencer->phone }}" class="text-champagne-dark hover:underline">{{ $influencer->phone }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Location</dt>
                        <dd class="mt-1 text-gray-700">{{ $influencer->location }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Audience Size</dt>
                        <dd class="mt-1 text-gray-700">{{ number_format($influencer->followers_count) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Social Profile</dt>
                        <dd class="mt-1">
                            @if ($influencer->profile_url)
                                <a href="{{ $influencer->profile_url }}" target="_blank" rel="noopener noreferrer" class="text-champagne-dark hover:underline">Open profile</a>
                            @else
                                <span class="text-gray-500">Not provided</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Portfolio</dt>
                        <dd class="mt-1">
                            @if ($influencer->portfolio_url)
                                <a href="{{ $influencer->portfolio_url }}" target="_blank" rel="noopener noreferrer" class="text-champagne-dark hover:underline">Open portfolio</a>
                            @else
                                <span class="text-gray-500">Not provided</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Submitted</dt>
                        <dd class="mt-1 text-gray-700">{{ $influencer->created_at->format('d M Y, h:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Approved</dt>
                        <dd class="mt-1 text-gray-700">{{ $influencer->approved_at?->format('d M Y, h:i A') ?? 'Not approved' }}</dd>
                    </div>
                </dl>
            </div>

            @if ($influencer->message)
                <div class="admin-card p-6">
                    <h3 class="text-sm font-semibold text-gray-900">Application Message</h3>
                    <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ $influencer->message }}</p>
                </div>
            @endif

            <div class="admin-card p-6">
                <h3 class="text-sm font-semibold text-gray-900">Partnership Details</h3>
                <dl class="mt-4 grid grid-cols-1 gap-5 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Commission Rate</dt>
                        <dd class="mt-1 text-gray-700">{{ $influencer->commission_rate !== null ? number_format((float) $influencer->commission_rate, 2).'%' : 'Not set' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Coupon Code</dt>
                        <dd class="mt-1 font-mono text-gray-700">{{ $influencer->coupon_code ?: 'Not set' }}</dd>
                    </div>
                </dl>
                @if ($influencer->admin_notes)
                    <div class="mt-5 border-t border-gray-100 pt-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Internal Notes</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ $influencer->admin_notes }}</p>
                    </div>
                @endif
            </div>

            @if ($influencer->user)
                <div class="admin-card p-6">
                    <h3 class="text-sm font-semibold text-gray-900">Linked Customer Account</h3>
                    <p class="mt-2 text-sm text-gray-600">Submitted by {{ $influencer->user->name }} ({{ $influencer->user->email }}).</p>
                    <a href="{{ route('admin.customers.show', $influencer->user) }}" class="mt-3 inline-block text-sm font-medium text-champagne-dark hover:underline">View customer</a>
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="admin-card p-6">
                <h3 class="text-sm font-semibold text-gray-900">Application Status</h3>
                <form method="POST" action="{{ route('admin.influencers.update-status', $influencer) }}" class="mt-4 space-y-3">
                    @csrf
                    @method('PATCH')
                    <x-admin.form.select name="status" :value="$influencer->status" :options="$statuses" :placeholder="null" />
                    <button type="submit" class="admin-btn-secondary w-full">Update Status</button>
                </form>
            </div>

            <div class="admin-card p-6">
                <h3 class="text-sm font-semibold text-gray-900">Partner Access</h3>
                <p class="mt-2 text-xs leading-5 text-gray-500">Active access is available after the application is approved.</p>
                <form method="POST" action="{{ route('admin.influencers.toggle-active', $influencer) }}" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="{{ $influencer->is_active ? 'admin-btn-secondary' : 'admin-btn-primary' }} w-full">
                        {{ $influencer->is_active ? 'Deactivate Partner' : 'Activate Partner' }}
                    </button>
                </form>
            </div>

            <div class="admin-card p-6">
                <h3 class="text-sm font-semibold text-gray-900">Delete Influencer</h3>
                <p class="mt-2 text-xs leading-5 text-gray-500">This permanently removes the application, profile, and uploaded image.</p>
                <div class="mt-4">
                    <x-admin.confirm-modal :action="route('admin.influencers.destroy', $influencer)" title="Delete Influencer" :message="'Delete '.$influencer->full_name.' and all associated influencer data?'" />
                </div>
            </div>
        </div>
    </div>
@endsection

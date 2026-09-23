@extends('admin.layouts.app')

@section('title', $application->reference_no)

@section('content')
    <x-admin.page-header
        :title="$application->reference_no"
        :breadcrumb="[
            ['label' => 'Career Applications', 'url' => route('admin.career-applications.index')],
            ['label' => $application->reference_no],
        ]"
    />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="admin-card p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $application->full_name }}</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $application->current_role ?: 'Current role not provided' }}
                        </p>
                    </div>
                    <x-admin.status-badge :status="$application->status" />
                </div>

                <dl class="mt-6 grid grid-cols-1 gap-x-6 gap-y-5 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Email</dt>
                        <dd class="mt-1"><a href="mailto:{{ $application->email }}" class="text-champagne-dark hover:underline">{{ $application->email }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Phone</dt>
                        <dd class="mt-1"><a href="tel:{{ $application->phone }}" class="text-champagne-dark hover:underline">{{ $application->phone }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Location</dt>
                        <dd class="mt-1 text-gray-700">{{ $application->location }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Area of Interest</dt>
                        <dd class="mt-1 text-gray-700">{{ \App\Models\CareerApplication::AREAS[$application->area_of_interest] ?? str($application->area_of_interest)->headline() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Experience</dt>
                        <dd class="mt-1 text-gray-700">{{ $application->experience_years !== null ? $application->experience_years.' years' : 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Submitted</dt>
                        <dd class="mt-1 text-gray-700">{{ $application->created_at->format('d M Y, h:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">LinkedIn</dt>
                        <dd class="mt-1">
                            @if ($application->linkedin_url)
                                <a href="{{ $application->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="break-all text-champagne-dark hover:underline">Open profile</a>
                            @else
                                <span class="text-gray-500">Not provided</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Portfolio</dt>
                        <dd class="mt-1">
                            @if ($application->portfolio_url)
                                <a href="{{ $application->portfolio_url }}" target="_blank" rel="noopener noreferrer" class="break-all text-champagne-dark hover:underline">Open portfolio</a>
                            @else
                                <span class="text-gray-500">Not provided</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="admin-card p-6">
                <h3 class="text-sm font-semibold text-gray-900">Candidate Message</h3>
                <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ $application->message }}</p>
            </div>

            @if ($application->user)
                <div class="admin-card p-6">
                    <h3 class="text-sm font-semibold text-gray-900">Linked Customer Account</h3>
                    <p class="mt-2 text-sm text-gray-600">This application was submitted by {{ $application->user->name }} ({{ $application->user->email }}).</p>
                    <a href="{{ route('admin.customers.show', $application->user) }}" class="mt-3 inline-block text-sm font-medium text-champagne-dark hover:underline">View customer</a>
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="admin-card p-6">
                <h3 class="text-sm font-semibold text-gray-900">Application Status</h3>
                <form method="POST" action="{{ route('admin.career-applications.update-status', $application) }}" class="mt-4 space-y-3">
                    @csrf
                    @method('PATCH')
                    <x-admin.form.select name="status" :value="$application->status" :options="$statuses" :placeholder="null" />
                    <button type="submit" class="admin-btn-secondary w-full">Update Status</button>
                </form>
            </div>

            <div class="admin-card p-6">
                <h3 class="text-sm font-semibold text-gray-900">Curriculum Vitae</h3>
                <p class="mt-3 break-words text-sm font-medium text-gray-700">{{ $application->cv_original_name }}</p>
                <p class="mt-1 text-xs text-gray-400">
                    {{ $application->cv_mime_type }}
                    @if ($application->cv_size)
                        &middot;
                        {{ $application->cv_size >= 1048576 ? number_format($application->cv_size / 1048576, 1).' MB' : number_format($application->cv_size / 1024, 1).' KB' }}
                    @endif
                </p>
                <a href="{{ route('admin.career-applications.cv', $application) }}" class="admin-btn-primary mt-4 w-full justify-center">Download CV</a>
            </div>
        </div>
    </div>
@endsection

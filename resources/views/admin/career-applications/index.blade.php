@extends('admin.layouts.app')

@section('title', 'Career Applications')

@section('content')
    <x-admin.page-header title="Career Applications" description="Review applications submitted from the careers page." />

    <form method="GET" class="admin-card mb-5 flex flex-wrap items-center gap-3 p-4">
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search reference, name, email..."
            class="admin-input max-w-xs"
        >
        <select name="area" class="admin-select max-w-[220px]">
            <option value="">All Areas</option>
            @foreach ($areas as $value => $label)
                <option value="{{ $value }}" @selected(request('area') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="admin-select max-w-[170px]">
            <option value="">All Statuses</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="admin-btn-secondary">Filter</button>
        @if (request()->hasAny(['search', 'area', 'status']))
            <a href="{{ route('admin.career-applications.index') }}" class="admin-btn-ghost">Clear</a>
        @endif
    </form>

    @if ($applications->isEmpty())
        <x-admin.empty-state title="No career applications found" />
    @else
        <x-admin.table :headers="['Reference', 'Candidate', 'Area', 'Experience', 'Location', 'Submitted', 'Status', '!Actions']">
            @foreach ($applications as $application)
                <tr>
                    <td class="whitespace-nowrap font-medium text-gray-900">{{ $application->reference_no }}</td>
                    <td>
                        <p class="font-medium text-gray-800">{{ $application->full_name }}</p>
                        <p class="text-xs text-gray-400">{{ $application->email }}</p>
                    </td>
                    <td>{{ $areas[$application->area_of_interest] ?? str($application->area_of_interest)->headline() }}</td>
                    <td class="whitespace-nowrap">
                        {{ $application->experience_years !== null ? $application->experience_years.' years' : 'Not provided' }}
                    </td>
                    <td>{{ $application->location }}</td>
                    <td class="whitespace-nowrap text-gray-400">{{ $application->created_at->format('d M Y') }}</td>
                    <td><x-admin.status-badge :status="$application->status" /></td>
                    <td class="text-right">
                        <a href="{{ route('admin.career-applications.show', $application) }}" class="text-sm font-medium text-champagne-dark hover:underline">View</a>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$applications" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

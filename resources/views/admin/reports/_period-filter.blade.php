@php
    $periods = ['today' => 'Today', 'yesterday' => 'Yesterday', 'last_7_days' => 'Last 7 Days', 'last_30_days' => 'Last 30 Days', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'custom' => 'Custom Range'];
@endphp

<form method="GET" class="admin-card mb-5 flex flex-wrap items-center gap-3 p-4">
    <select name="period" x-data x-on:change="$el.closest('form').requestSubmit()" class="admin-select max-w-[170px]">
        @foreach ($periods as $value => $label)
            <option value="{{ $value }}" @selected(request('period', 'last_30_days') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @if (request('period') === 'custom')
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="admin-input max-w-[160px]">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="admin-input max-w-[160px]">
        <button type="submit" class="admin-btn-secondary">Apply</button>
    @endif
    <p class="ml-auto text-sm text-gray-400">{{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</p>
    <a href="{{ route('admin.reports.export', ['type' => $exportType ?? 'sales', 'format' => 'csv'] + request()->query()) }}" class="admin-btn-secondary">Export CSV</a>
    <button type="button" onclick="window.print()" class="admin-btn-secondary">Print / PDF</button>
</form>

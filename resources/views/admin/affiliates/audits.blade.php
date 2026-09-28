@extends('admin.layouts.app')
@section('title', 'Affiliate Audit Log')
@section('content')
<x-admin.page-header title="Affiliate Audit Log" :breadcrumb="[['label' => 'Affiliates', 'url' => route('admin.affiliates.index')], ['label' => 'Audit']]" />@include('admin.affiliates._nav')
<div class="admin-card overflow-x-auto"><table class="admin-table min-w-full"><thead><tr><th>Date</th><th>Event</th><th>Affiliate</th><th>Actor</th><th>Transition</th><th>Metadata</th></tr></thead><tbody>@forelse($audits as $row)<tr><td class="whitespace-nowrap">{{ $row->created_at->format('d M Y H:i') }}</td><td>{{ str_replace('_',' ',$row->event) }}</td><td>{{ $row->affiliate?->user?->name ?? '—' }}</td><td>{{ $row->actor?->email ?? 'System' }}</td><td>{{ $row->status_from ? $row->status_from.' → ' : '' }}{{ $row->status_to }}</td><td class="max-w-xs truncate text-xs">{{ $row->metadata ? json_encode($row->metadata) : '—' }}</td></tr>@empty<tr><td colspan="6" class="py-10 text-center text-gray-400">No audit events.</td></tr>@endforelse</tbody></table></div><div class="mt-5">{{ $audits->links() }}</div>
@endsection

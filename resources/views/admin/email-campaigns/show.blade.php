@extends('admin.layouts.app')

@section('title', $campaign->name)

@section('content')
    <x-admin.page-header
        :title="$campaign->name"
        :description="$templates[$campaign->template_type] ?? str($campaign->template_type)->headline()"
        :breadcrumb="[['label' => 'Email Marketing', 'url' => route('admin.email-campaigns.index')], ['label' => $campaign->name]]"
    >
        <x-slot:actions>
            <a href="{{ route('admin.email-campaigns.preview', $campaign) }}" target="_blank" class="admin-btn-secondary">Preview Email</a>
            @if ($campaign->failed_count > 0)
                <form method="POST" action="{{ route('admin.email-campaigns.retry-failed', $campaign) }}">
                    @csrf
                    <button type="submit" class="admin-btn-primary">Retry Failed</button>
                </form>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Total Recipients', 'value' => $campaign->total_recipient_count, 'class' => 'text-gray-900'],
            ['label' => 'Queued', 'value' => $campaign->queued_count, 'class' => 'text-blue-700'],
            ['label' => 'Sent', 'value' => $campaign->sent_count, 'class' => 'text-green-700'],
            ['label' => 'Failed', 'value' => $campaign->failed_count, 'class' => 'text-red-700'],
        ] as $stat)
            <div class="admin-card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $stat['label'] }}</p>
                <p class="mt-2 text-2xl font-semibold {{ $stat['class'] }}">{{ number_format($stat['value']) }}</p>
            </div>
        @endforeach
    </div>

    @if (in_array($campaign->status, ['queued', 'processing'], true))
        <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
            Delivery is being handled by the Laravel queue. Run <code class="rounded bg-blue-100 px-1.5 py-0.5">php artisan queue:work --queue=emails,default --tries=3</code> when a worker is not already active.
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="admin-card p-6 lg:col-span-2">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Subject</p>
                    <h2 class="mt-1 text-lg font-semibold text-gray-900">{{ $campaign->subject }}</h2>
                    @if ($campaign->preheader)<p class="mt-1 text-sm text-gray-500">{{ $campaign->preheader }}</p>@endif
                </div>
                <x-admin.status-badge :status="$campaign->status" />
            </div>

            @if ($campaign->image_url)
                <x-ui.optimized-image :src="$campaign->image_url" :alt="$campaign->headline" sizes="640px" class="mt-6 aspect-[16/7] w-full rounded-xl object-cover" />
            @endif

            <div class="mt-6 rounded-xl bg-ivory-soft p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-champagne-dark">{{ $campaign->eyebrow }}</p>
                <h3 class="font-display mt-2 text-2xl text-charcoal">{{ $campaign->headline }}</h3>
                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-charcoal-soft">{{ $campaign->body }}</p>
                @if ($campaign->highlight_text)<p class="mt-4 font-medium text-champagne-dark">{{ $campaign->highlight_text }}</p>@endif
                @if ($campaign->cta_label)<p class="mt-4 text-xs text-gray-500">Button: {{ $campaign->cta_label }} &rarr; {{ $campaign->cta_url }}</p>@endif
            </div>
        </div>

        <div class="admin-card p-6">
            <h3 class="text-sm font-semibold text-gray-900">Campaign Details</h3>
            <dl class="mt-4 space-y-4 text-sm">
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Template</dt><dd class="mt-1 text-gray-700">{{ $templates[$campaign->template_type] }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">CC Address</dt><dd class="mt-1 break-all text-gray-700">{{ $campaign->cc_email }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Created By</dt><dd class="mt-1 text-gray-700">{{ $campaign->creator?->name ?: 'System' }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Queued At</dt><dd class="mt-1 text-gray-700">{{ $campaign->queued_at?->format('d M Y, h:i A') ?: 'Not queued' }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Completed At</dt><dd class="mt-1 text-gray-700">{{ $campaign->completed_at?->format('d M Y, h:i A') ?: 'In progress' }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-base font-semibold text-gray-900">Recipients</h2>
        <p class="text-xs text-gray-400">Delivery attempts are tracked individually.</p>
    </div>

    <x-admin.table :headers="['Recipient', 'Source', 'Status', 'Attempts', 'Delivery Time', 'Failure', '!Actions']">
        @foreach ($recipients as $recipient)
            <tr>
                <td>
                    <p class="font-medium text-gray-800">{{ $recipient->name ?: 'Manual recipient' }}</p>
                    <p class="text-xs text-gray-400">{{ $recipient->email }}</p>
                </td>
                <td class="capitalize text-gray-600">{{ $recipient->source }}</td>
                <td><x-admin.status-badge :status="$recipient->status" /></td>
                <td>{{ $recipient->attempts }}</td>
                <td class="whitespace-nowrap text-gray-500">{{ $recipient->sent_at?->format('d M Y, h:i A') ?: '—' }}</td>
                <td class="max-w-xs text-xs text-error">{{ $recipient->failure_message ? str($recipient->failure_message)->limit(100) : '—' }}</td>
                <td class="text-right">
                    @if ($recipient->status === 'failed')
                        <form method="POST" action="{{ route('admin.email-campaigns.recipients.retry', [$campaign, $recipient]) }}">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-champagne-dark hover:underline">Retry</button>
                        </form>
                    @else
                        <span class="text-gray-300">—</span>
                    @endif
                </td>
            </tr>
        @endforeach

        <x-slot:footer>
            <x-admin.pagination :paginator="$recipients" />
        </x-slot:footer>
    </x-admin.table>
@endsection

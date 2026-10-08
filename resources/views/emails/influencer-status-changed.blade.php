@php
    [$heading, $message, $icon, $accent] = $status === 'approved'
        ? [
            'Your application is approved!',
            'Welcome to the Rupnora Influencer Program. Our team will contact you with the next partnership steps.',
            'success',
            '#47a545',
        ]
        : [
            'Application status updated',
            'Thank you for your interest in the Rupnora Influencer Program. We are unable to approve your application at this time.',
            'error',
            '#fb6366',
        ];
@endphp

<x-mail.layout :title="$heading" :preheader="$message" :accent="$accent">
    <x-mail.status-icon :type="$icon" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:32px; color:#231535; padding-bottom:12px;">
                {{ $heading }}
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:14px; line-height:22px; color:#4f3267; padding-bottom:4px;">
                Hi {{ $influencer->full_name }}, {{ $message }}
            </td>
        </tr>
        <tr>
            <td align="center">
                <x-mail.button :url="route('influencer')" color="#231535">View Influencer Program</x-mail.button>
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:12px; line-height:18px; color:#9e9fa5; padding-top:24px;">
                Reference {{ $influencer->reference_no }} &middot; Status changed from {{ \App\Models\Influencer::STATUSES[$previousStatus] ?? ucfirst($previousStatus) }} to {{ \App\Models\Influencer::STATUSES[$status] ?? ucfirst($status) }}.
            </td>
        </tr>
    </table>
</x-mail.layout>

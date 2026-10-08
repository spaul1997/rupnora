@php
    [$heading, $message, $icon, $accent] = match ($profile->status) {
        'approved' => [
            'Your application is approved!',
            'Welcome to the Rupnora Affiliate Program. Your affiliate dashboard and referral tools are now available.',
            'success',
            '#47a545',
        ],
        'rejected' => [
            'Application status updated',
            'Thank you for your interest in the Rupnora Affiliate Program. We are unable to approve your application at this time.',
            'error',
            '#fb6366',
        ],
        'suspended' => [
            'Affiliate account suspended',
            'Your Rupnora affiliate account has been suspended. Affiliate tools and new commission eligibility are unavailable while the account is suspended.',
            'info',
            '#6144ac',
        ],
        default => [
            'Application under review',
            'Your Rupnora affiliate application status has been moved to pending review. We will notify you when the review is complete.',
            'info',
            '#6144ac',
        ],
    };
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
                Hi {{ $profile->user->name }}, {{ $message }}
            </td>
        </tr>
        <tr>
            <td align="center">
                <x-mail.button :url="route('account.affiliate.dashboard')" color="#231535">View Affiliate Account</x-mail.button>
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:12px; line-height:18px; color:#9e9fa5; padding-top:24px;">
                Status changed from {{ ucfirst($previousStatus) }} to {{ ucfirst($profile->status) }}.
            </td>
        </tr>
    </table>
</x-mail.layout>

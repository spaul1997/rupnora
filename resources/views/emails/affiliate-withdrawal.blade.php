@php
    [$heading, $message, $icon, $accent] = match ($event) {
        'paid' => [
            'Withdrawal completed',
            'Your affiliate withdrawal has been paid successfully.',
            'success',
            '#47a545',
        ],
        'rejected' => [
            'Withdrawal rejected',
            'Your withdrawal request was rejected and the reserved amount has been returned to your available balance.',
            'error',
            '#fb6366',
        ],
        'failed' => [
            'Withdrawal unsuccessful',
            'The transfer could not be completed and the reserved amount has been returned to your available balance.',
            'error',
            '#fb6366',
        ],
        default => [
            'Withdrawal request received',
            'We received your withdrawal request. The requested amount is reserved while our team reviews and processes it.',
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
            <td align="center" style="font-family:Arial,sans-serif; font-size:14px; line-height:22px; color:#4f3267; padding-bottom:20px;">
                Hi {{ $withdrawal->affiliate->user->name }}, {{ $message }}
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px; background-color:#f6f3f9; border-radius:10px;">
        <tr><td style="padding:14px 16px; font-family:Arial,sans-serif; font-size:13px; color:#4f3267;">Reference</td><td align="right" style="padding:14px 16px; font-family:Arial,sans-serif; font-size:13px; font-weight:700; color:#231535;">{{ $withdrawal->request_reference }}</td></tr>
        <tr><td style="padding:0 16px 14px; font-family:Arial,sans-serif; font-size:13px; color:#4f3267;">Requested amount</td><td align="right" style="padding:0 16px 14px; font-family:Arial,sans-serif; font-size:13px; font-weight:700; color:#231535;">₹{{ number_format((float) $withdrawal->gross_amount, 2) }}</td></tr>
        <tr><td style="padding:0 16px 14px; font-family:Arial,sans-serif; font-size:13px; color:#4f3267;">Net payout</td><td align="right" style="padding:0 16px 14px; font-family:Arial,sans-serif; font-size:13px; font-weight:700; color:#231535;">₹{{ number_format((float) $withdrawal->net_amount, 2) }}</td></tr>
        @if ($event === 'paid' && $withdrawal->transfer_reference)
            <tr><td style="padding:0 16px 14px; font-family:Arial,sans-serif; font-size:13px; color:#4f3267;">Transfer reference</td><td align="right" style="padding:0 16px 14px; font-family:Arial,sans-serif; font-size:13px; font-weight:700; color:#231535;">{{ $withdrawal->transfer_reference }}</td></tr>
        @endif
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr><td align="center"><x-mail.button :url="route('account.affiliate.withdrawals')" color="#231535">View Withdrawals</x-mail.button></td></tr>
    </table>
</x-mail.layout>

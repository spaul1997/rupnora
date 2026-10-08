@php
    $heading = $createdByAdmin ? 'Influencer profile created' : 'Application received';
    $message = $createdByAdmin
        ? 'Our team created your Rupnora influencer profile. Its current application status is '.strtolower(\App\Models\Influencer::STATUSES[$status] ?? $status).'.'
        : 'Thank you for applying to join the Rupnora Influencer Program. Our team will review your application and notify you when a decision is made.';
@endphp

<x-mail.layout :title="$heading" :preheader="$message" accent="#6144ac">
    <x-mail.status-icon type="success" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:32px; color:#231535; padding-bottom:12px;">
                {{ $heading }}
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:14px; line-height:22px; color:#4f3267; padding-bottom:20px;">
                Hi {{ $influencer->full_name }}, {{ $message }}
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px; background-color:#f6f3f9; border-radius:10px;">
        <tr><td style="padding:14px 16px; font-family:Arial,sans-serif; font-size:13px; color:#4f3267;">Application reference</td><td align="right" style="padding:14px 16px; font-family:Arial,sans-serif; font-size:13px; font-weight:700; color:#231535;">{{ $influencer->reference_no }}</td></tr>
        <tr><td style="padding:0 16px 14px; font-family:Arial,sans-serif; font-size:13px; color:#4f3267;">Platform</td><td align="right" style="padding:0 16px 14px; font-family:Arial,sans-serif; font-size:13px; font-weight:700; color:#231535;">{{ \App\Models\Influencer::PLATFORMS[$influencer->primary_platform] ?? ucfirst($influencer->primary_platform) }}</td></tr>
        <tr><td style="padding:0 16px 14px; font-family:Arial,sans-serif; font-size:13px; color:#4f3267;">Social handle</td><td align="right" style="padding:0 16px 14px; font-family:Arial,sans-serif; font-size:13px; font-weight:700; color:#231535;">{{ $influencer->social_handle }}</td></tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr><td align="center"><x-mail.button :url="route('influencer').'#apply'" color="#231535">View Influencer Program</x-mail.button></td></tr>
    </table>
</x-mail.layout>

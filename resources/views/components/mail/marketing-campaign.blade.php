@props([
    'campaign',
    'recipientName' => null,
    'accent' => '#6144ac',
    'badgeLabel' => 'Rupnora Edit',
])

<x-mail.layout :title="$campaign->subject" :preheader="$campaign->preheader ?: $campaign->headline" :accent="$accent">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:6px 0 28px;">
                <a href="{{ url('/') }}" style="font-family:Georgia,'Times New Roman',serif; font-size:25px; letter-spacing:5px; color:#231535; font-weight:bold;">RUPNORA</a>
                <div style="margin-top:6px; font-family:Arial,sans-serif; font-size:10px; letter-spacing:2px; text-transform:uppercase; color:#9e9fa5;">Jewellery for every story</div>
            </td>
        </tr>
    </table>

    @if ($campaign->image_url)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
            <tr>
                <td>
                    <img src="{{ $campaign->image_url }}" width="520" alt="{{ $campaign->headline }}" style="display:block; width:100%; max-width:520px; height:auto; border-radius:14px;">
                </td>
            </tr>
        </table>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding-bottom:12px;">
                <span style="display:inline-block; border-radius:999px; background-color:#f5e8ff; padding:7px 13px; font-family:Arial,sans-serif; font-size:10px; font-weight:bold; letter-spacing:1.7px; text-transform:uppercase; color:{{ $accent }};">{{ $campaign->eyebrow ?: $badgeLabel }}</span>
            </td>
        </tr>
        @if ($recipientName)
            <tr>
                <td align="center" style="padding-bottom:10px; font-family:Arial,sans-serif; font-size:13px; color:#4f3267;">Hello {{ $recipientName }},</td>
            </tr>
        @endif
        <tr>
            <td align="center" style="font-family:Georgia,'Times New Roman',serif; font-size:30px; line-height:38px; color:#231535; padding-bottom:16px;">{{ $campaign->headline }}</td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:14px; line-height:23px; color:#4f3267; padding-bottom:22px;">{!! nl2br(e($campaign->body)) !!}</td>
        </tr>
    </table>

    {{ $slot }}

    @if ($campaign->cta_label && $campaign->cta_url)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:26px;">
            <tr>
                <td align="center">
                    <x-mail.button :url="$campaign->cta_url" :color="$accent">{{ $campaign->cta_label }}</x-mail.button>
                </td>
            </tr>
        </table>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:32px; border-top:1px solid #e3e3e3;">
        <tr>
            <td align="center" style="padding-top:22px; font-family:Arial,sans-serif; font-size:12px; line-height:19px; color:#9e9fa5;">
                With care,<br><strong style="color:#4f3267;">The Rupnora Team</strong>
            </td>
        </tr>
    </table>
</x-mail.layout>

@props([
    'greeting' => 'Best regards,',
    'team' => 'Rupnora Jewellery',
    'tags' => ['Jewellery', 'Fashion', 'Self-Expression', 'Rupnora'],
])

@php
    $settings = \App\Models\WebsiteSetting::current();
    $socialLinks = collect([
        'instagram' => ['label' => 'Instagram', 'mark' => 'ig'],
        'facebook' => ['label' => 'Facebook', 'mark' => 'f'],
        'youtube' => ['label' => 'YouTube', 'mark' => 'yt'],
        'linkedin' => ['label' => 'LinkedIn', 'mark' => 'in'],
    ])
        ->filter(fn ($network, $key) => filled($settings->{$key}))
        ->map(fn ($network, $key) => $network + ['url' => $settings->{$key}]);

    $websiteUrl = url('/');
    $websiteLabel = preg_replace('#^https?://#', '', rtrim($websiteUrl, '/'));
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:32px; border-top:1px solid #efe2f9;">
    <tr>
        <td class="m-stack m-foot m-center" width="36%" valign="middle" style="padding:22px 12px 4px 6px; font-family:Arial,sans-serif; font-size:12px; line-height:18px; color:#4f3267;">
            {{ $greeting }}
            <div style="margin-top:4px; font-family:'Snell Roundhand','Segoe Script','Brush Script MT',cursive; font-size:22px; line-height:30px; color:#6144ac;">Team Rupnora</div>
            <div style="font-size:11px; line-height:16px; color:#9e9fa5;">{{ $team }}</div>
        </td>
        <td class="m-stack m-foot m-foot-mid" width="28%" align="center" valign="middle" style="padding:22px 10px 4px; border-left:1px solid #efe2f9; border-right:1px solid #efe2f9;">
            <a href="{{ $websiteUrl }}">
                <img src="{{ asset('images/email/rupnora-logo.png') }}" width="84" alt="Rupnora" style="display:block; width:84px; max-width:84px; height:auto; margin:0 auto;">
            </a>
            <!-- <div style="margin-top:8px; font-family:Arial,sans-serif; font-size:8px; line-height:12px; letter-spacing:1.5px; text-transform:uppercase; color:#9e9fa5;">Everyday Style, Endless Sparkle</div> -->
        </td>
        <td class="m-stack m-foot m-center" width="36%" valign="middle" style="padding:22px 6px 4px 18px; font-family:Arial,sans-serif; font-size:12px; line-height:18px; color:#4f3267;">
            Connect with us
            <a href="mailto:support@rupnora.in" style="color:#6144ac;">support@rupnora.in</a>
            <!-- @if ($socialLinks->isNotEmpty())
                <table role="presentation" class="m-auto" cellpadding="0" cellspacing="0" style="margin-top:8px;">
                    <tr>
                        @foreach ($socialLinks as $network)
                            <td style="padding-right:6px;">
                                <a href="{{ $network['url'] }}" title="{{ $network['label'] }}" style="display:block; width:28px; height:28px; border-radius:50%; background-color:#231535; font-family:Arial,sans-serif; font-size:11px; font-weight:bold; line-height:28px; text-align:center; color:#ffffff;">{{ $network['mark'] }}</a>
                            </td>
                        @endforeach
                    </tr>
                </table>
            @endif
            <div style="margin-top:8px;"><a href="{{ $websiteUrl }}" style="font-size:12px; font-weight:bold; color:#6144ac;">{{ $websiteLabel }}</a></div> -->
        </td>
    </tr>
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" style="padding-top:22px; font-family:Arial,sans-serif; font-size:9px; line-height:14px; letter-spacing:2px; text-transform:uppercase; color:#af9dd9;">
            {!! collect($tags)->map(fn ($tag) => e($tag))->implode(' &nbsp;/&nbsp; ') !!} <br>
            <small>&copy; {{ date('Y') }} Rupnora Jewellery. All rights reserved.</small>
        </td>
    </tr>
</table>

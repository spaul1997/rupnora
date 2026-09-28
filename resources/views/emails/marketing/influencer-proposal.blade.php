@php
    $heroImage = $campaign->image_url ?: asset('images/email/influencer-hero.jpg');
    $ctaUrl = $campaign->cta_url ?: route('influencer');
    $ctaLabel = $campaign->cta_label ?: 'Discuss Collaboration';

    $paragraphs = collect(preg_split('/\R{2,}/', trim($campaign->body)))
        ->filter(fn ($paragraph) => trim($paragraph) !== '')
        ->map(fn ($paragraph) => str_replace('Rupnora', '<strong style="color:#6144ac;">Rupnora</strong>', nl2br(e(trim($paragraph)), false)));

    $benefits = [
        [
            ['icon' => '&#9671;', 'title' => 'Trendy & High-Quality Jewellery', 'note' => 'Pieces made to be styled'],
            ['icon' => '&#8377;', 'title' => 'Earn Commission', 'note' => 'With your personal coupon code'],
            ['icon' => '&#9825;', 'title' => 'Creative Freedom', 'note' => 'You create, we support'],
        ],
        [
            ['icon' => '&#10022;', 'title' => 'Early Access', 'note' => 'Discover new launches first'],
            ['icon' => '&#8734;', 'title' => 'Long-Term Partnership', 'note' => 'Grow with Rupnora'],
            ['icon' => '&#9743;', 'title' => 'Dedicated Support', 'note' => 'From our partnerships team'],
        ],
    ];
@endphp

<x-mail.layout :title="$campaign->subject" :preheader="$campaign->preheader ?: $campaign->headline" padding="20px 28px 32px">
    <x-slot:head>
        <style>
            @media (max-width: 620px) {
                .ip-hero-copy { padding: 30px 22px 6px !important; text-align: center !important; }
                .ip-hero-media { padding: 18px 22px 30px !important; }
                .ip-why { padding: 26px 20px 6px !important; text-align: center !important; }
                .ip-grid { padding: 8px 6px 18px !important; }
            }
        </style>
    </x-slot:head>

    <x-mail.brand-header />

    {{-- Hero --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5e8ff; background-image:linear-gradient(135deg, #f6f3f9 0%, #efe2f9 55%, #e4d6f7 100%); border-radius:16px;">
        <tr>
            <td class="m-stack ip-hero-copy" width="56%" valign="middle" style="padding:34px 12px 34px 30px;">
                <div style="font-family:Arial,sans-serif; font-size:10px; line-height:14px; letter-spacing:3px; text-transform:uppercase; color:#4f3267;">{{ $campaign->eyebrow ?: 'Let’s create something' }}</div>
                <div style="margin-top:10px; font-family:Georgia,'Times New Roman',serif; font-size:32px; line-height:38px; font-style:italic; color:#231535;">{{ $campaign->headline }}</div>
                <div style="margin-top:12px; font-family:Arial,sans-serif; font-size:13px; line-height:20px; color:#4f3267;">Collaboration opportunity with <strong style="color:#6144ac;">Rupnora</strong></div>
                <table role="presentation" class="m-auto" cellpadding="0" cellspacing="0" style="margin-top:16px;">
                    <tr>
                        <td style="width:40px; height:2px; line-height:2px; font-size:0; background-color:#6144ac;">&nbsp;</td>
                    </tr>
                </table>
                <div style="margin-top:14px; font-family:Georgia,'Times New Roman',serif; font-size:13px; line-height:20px; font-style:italic; color:#6144ac;">{{ $campaign->highlight_text ?: 'Authentic voices. Real stories. More women discovering their sparkle.' }}</div>
            </td>
            <td class="m-stack ip-hero-media" width="44%" align="center" valign="middle" style="padding:26px 26px 26px 6px;">
                <table role="presentation" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:4px; box-shadow:0 8px 22px rgba(35,21,53,0.14);">
                    <tr>
                        <td style="padding:8px 8px 0;">
                            <img src="{{ $heroImage }}" width="176" alt="{{ $campaign->headline }}" style="display:block; width:176px; max-width:176px; height:auto; border-radius:2px;">
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:9px 8px 11px; font-family:Georgia,'Times New Roman',serif; font-size:11px; line-height:15px; font-style:italic; color:#4f3267;">Everyday style, endless sparkle <span style="color:#6144ac;">&#10022;</span></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Letter --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:30px;">
        <tr>
            <td style="padding:0 6px; font-family:Arial,sans-serif; font-size:14px; line-height:24px; color:#4f3267;">
                <p style="margin:0 0 14px; color:#231535;">Hi {{ $recipientName ?: 'there' }},</p>
                @foreach ($paragraphs as $paragraph)
                    <p style="margin:0 0 14px;">{!! $paragraph !!}</p>
                @endforeach
            </td>
        </tr>
    </table>

    {{-- Why collaborate --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:14px; background-color:#f6f3f9; border-radius:14px;">
        <tr>
            <td class="m-stack ip-why" width="34%" valign="middle" style="padding:28px 10px 28px 24px;">
                <div style="font-family:Georgia,'Times New Roman',serif; font-size:16px; line-height:24px; letter-spacing:1px; text-transform:uppercase; color:#231535;">Why collaborate with Rupnora?</div>
                <table role="presentation" class="m-auto" cellpadding="0" cellspacing="0" style="margin-top:14px;">
                    <tr>
                        <td style="width:36px; height:2px; line-height:2px; font-size:0; background-color:#6144ac;">&nbsp;</td>
                    </tr>
                </table>
            </td>
            <td class="m-stack ip-grid" width="66%" valign="middle" style="padding:16px 14px 16px 0;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    @foreach ($benefits as $row)
                        <tr>
                            @foreach ($row as $benefit)
                                <td width="33%" align="center" valign="top" style="padding:{{ $loop->parent->first ? '8px 6px 14px' : '14px 6px 8px' }};{{ $loop->first ? '' : ' border-left:1px solid #e3dcef;' }}">
                                    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto;">
                                        <tr>
                                            <td align="center" valign="middle" style="width:36px; height:36px; border-radius:50%; background-color:#ffffff; border:1px solid #cfc1ff; font-family:'Segoe UI Symbol',Arial,sans-serif; font-size:16px; line-height:36px; color:#6144ac;">{!! $benefit['icon'] !!}</td>
                                        </tr>
                                    </table>
                                    <div style="margin-top:8px; font-family:Arial,sans-serif; font-size:11px; line-height:15px; font-weight:bold; color:#231535;">{{ $benefit['title'] }}</div>
                                    <div style="margin-top:2px; font-family:Arial,sans-serif; font-size:10px; line-height:14px; color:#9e9fa5;">{{ $benefit['note'] }}</div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>

    {{-- Call to action --}}
    <x-mail.section-title>Let&rsquo;s Work Together</x-mail.section-title>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:14px 10px 0; font-family:Arial,sans-serif; font-size:14px; line-height:23px; color:#4f3267;">
                <p style="margin:0 0 12px;">If you&rsquo;re interested, we&rsquo;d love to share more details about our upcoming campaigns, product range and collaboration options. We&rsquo;re excited to hear your ideas too!</p>
                <p style="margin:0;">Looking forward to the possibility of creating something beautiful together. &#128156;</p>
            </td>
        </tr>
        <tr>
            <td align="center">
                <x-mail.button :url="$ctaUrl" variant="brand">{{ $ctaLabel }} &rarr;</x-mail.button>
            </td>
        </tr>
        <tr>
            <td align="center" style="padding-top:16px; font-family:Arial,sans-serif; font-size:10px; line-height:14px; letter-spacing:2px; text-transform:uppercase; color:#9e9fa5;">Thank you for your time &amp; consideration!</td>
        </tr>
    </table>

    <x-mail.brand-footer team="Partnerships Team, Rupnora" />
</x-mail.layout>

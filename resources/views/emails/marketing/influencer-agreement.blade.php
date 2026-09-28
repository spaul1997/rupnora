@php
    $ctaUrl = $campaign->cta_url ?: route('contact');
    $ctaLabel = $campaign->cta_label ?: 'Contact Partnerships Team';

    $paragraphs = collect(preg_split('/\R{2,}/', trim($campaign->body)))
        ->filter(fn ($paragraph) => trim($paragraph) !== '')
        ->map(fn ($paragraph) => str_replace('Rupnora', '<strong style="color:#6144ac;">Rupnora</strong>', nl2br(e(trim($paragraph)), false)));
@endphp

<x-mail.layout :title="$campaign->subject" :preheader="$campaign->preheader ?: $campaign->headline" padding="20px 28px 32px">
    <x-slot:head>
        <style>
            @media (max-width: 620px) {
                .ia-hero { padding: 28px 18px 26px !important; }
                .ia-headline { font-size: 26px !important; line-height: 33px !important; }
                .ia-ref { padding: 18px 16px !important; }
            }
        </style>
    </x-slot:head>

    <x-mail.brand-header />

    {{-- Hero --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5e8ff; background-image:linear-gradient(160deg, #f6f3f9 0%, #efe2f9 60%, #e4d6f7 100%); border-radius:16px;">
        <tr>
            <td class="ia-hero" align="center" style="padding:34px 28px 30px;">
                <div style="font-family:Arial,sans-serif; font-size:10px; line-height:14px; font-weight:bold; letter-spacing:3px; text-transform:uppercase; color:#6144ac;">&#10022;&nbsp; {{ $campaign->eyebrow ?: 'Partnership Agreement' }} &nbsp;&#10022;</div>
                <div class="ia-headline" style="margin-top:12px; font-family:Georgia,'Times New Roman',serif; font-size:32px; line-height:40px; font-style:italic; color:#231535;">{{ $campaign->headline }}</div>
                <div style="margin-top:12px; font-family:Arial,sans-serif; font-size:13px; line-height:20px; color:#4f3267;">Your collaboration with <strong style="color:#6144ac;">Rupnora</strong></div>
                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:16px auto 0;">
                    <tr>
                        <td style="width:40px; height:2px; line-height:2px; font-size:0; background-color:#6144ac;">&nbsp;</td>
                    </tr>
                </table>

                @if ($campaign->image_url)
                    <table role="presentation" width="440" cellpadding="0" cellspacing="0" style="width:100%; max-width:440px; margin:24px auto 0; background-color:#ffffff; border-radius:12px; box-shadow:0 10px 26px rgba(35,21,53,0.14);">
                        <tr>
                            <td style="padding:10px;">
                                <img src="{{ $campaign->image_url }}" width="420" alt="{{ $campaign->headline }}" style="display:block; width:100%; max-width:420px; height:auto; border-radius:8px; background-color:#f6f3f9;">
                            </td>
                        </tr>
                    </table>
                @endif
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

    {{-- Agreement reference --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:6px; background-color:#f6f3f9; border-radius:14px;">
        <tr>
            <td class="ia-ref" style="padding:20px 22px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="36" valign="top" align="center" style="width:36px; height:36px; border-radius:50%; background-color:#ffffff; border:1px solid #cfc1ff; font-family:'Segoe UI Symbol',Arial,sans-serif; font-size:16px; line-height:36px; color:#6144ac;">&#9998;</td>
                        <td valign="top" style="padding-left:14px; font-family:Arial,sans-serif;">
                            <div style="font-size:10px; line-height:14px; font-weight:bold; letter-spacing:1.7px; text-transform:uppercase; color:#9e9fa5;">Agreement reference</div>
                            <div style="margin-top:6px; font-family:Georgia,'Times New Roman',serif; font-size:15px; line-height:23px; color:#231535;">{{ $campaign->highlight_text ?: 'Please review the collaboration terms and confirm your acceptance with our partnerships team.' }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Call to action --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <x-mail.button :url="$ctaUrl" variant="brand">{{ $ctaLabel }} &rarr;</x-mail.button>
            </td>
        </tr>
        <tr>
            <td align="center" style="padding-top:16px; font-family:Arial,sans-serif; font-size:10px; line-height:14px; letter-spacing:2px; text-transform:uppercase; color:#9e9fa5;">We look forward to creating together</td>
        </tr>
    </table>

    <x-mail.shop-collections title="Our Collections" intro="The Rupnora pieces you’ll be styling and sharing." />
    <x-mail.shop-categories title="Our Range" intro="Explore every category you can feature in your content." />

    <x-mail.brand-footer team="Partnerships Team, Rupnora" />
</x-mail.layout>

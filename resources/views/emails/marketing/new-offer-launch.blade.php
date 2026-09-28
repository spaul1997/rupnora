@php
    $ctaUrl = $campaign->cta_url ?: route('collections.index');
    $ctaLabel = $campaign->cta_label ?: 'Shop the Offer';

    $paragraphs = collect(preg_split('/\R{2,}/', trim($campaign->body)))
        ->filter(fn ($paragraph) => trim($paragraph) !== '')
        ->map(fn ($paragraph) => str_replace('Rupnora', '<strong style="color:#6144ac;">Rupnora</strong>', nl2br(e(trim($paragraph)), false)));
@endphp

<x-mail.layout :title="$campaign->subject" :preheader="$campaign->preheader ?: $campaign->headline" padding="20px 28px 32px">
    <x-slot:head>
        <style>
            @media (max-width: 620px) {
                .ol-hero { padding: 28px 18px 26px !important; }
                .ol-headline { font-size: 26px !important; line-height: 33px !important; }
                .ol-code { font-size: 20px !important; line-height: 28px !important; }
            }
        </style>
    </x-slot:head>

    <x-mail.brand-header />

    {{-- Hero --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5e8ff; background-image:linear-gradient(160deg, #f6f3f9 0%, #efe2f9 60%, #e4d6f7 100%); border-radius:16px;">
        <tr>
            <td class="ol-hero" align="center" style="padding:34px 28px 30px;">
                <div style="font-family:Arial,sans-serif; font-size:10px; line-height:14px; font-weight:bold; letter-spacing:3px; text-transform:uppercase; color:#6144ac;">&#10022;&nbsp; {{ $campaign->eyebrow ?: 'Exclusive Offer' }} &nbsp;&#10022;</div>
                <div class="ol-headline" style="margin-top:12px; font-family:Georgia,'Times New Roman',serif; font-size:32px; line-height:40px; font-style:italic; color:#231535;">{{ $campaign->headline }}</div>
                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:16px auto 0;">
                    <tr>
                        <td style="width:40px; height:2px; line-height:2px; font-size:0; background-color:#6144ac;">&nbsp;</td>
                    </tr>
                </table>

                @if ($campaign->image_url)
                    <table role="presentation" width="440" cellpadding="0" cellspacing="0" style="width:100%; max-width:440px; margin:24px auto 0; background-color:#ffffff; border-radius:12px; box-shadow:0 10px 26px rgba(35,21,53,0.14);">
                        <tr>
                            <td style="padding:10px;">
                                <a href="{{ $ctaUrl }}">
                                    <img src="{{ $campaign->image_url }}" width="420" alt="{{ $campaign->headline }}" style="display:block; width:100%; max-width:420px; height:auto; border-radius:8px; background-color:#f6f3f9;">
                                </a>
                            </td>
                        </tr>
                    </table>
                @endif

                @if ($campaign->highlight_text)
                    <table role="presentation" width="380" cellpadding="0" cellspacing="0" style="width:100%; max-width:380px; margin:24px auto 0; background-color:#ffffff; border:1px dashed #8b63fb; border-radius:12px;">
                        <tr>
                            <td align="center" style="padding:18px 20px;">
                                <div style="font-family:Arial,sans-serif; font-size:10px; line-height:14px; font-weight:bold; letter-spacing:1.7px; text-transform:uppercase; color:#9e9fa5;">Your special offer</div>
                                <div class="ol-code" style="margin-top:7px; font-family:Georgia,'Times New Roman',serif; font-size:24px; line-height:32px; color:#6144ac;">{{ $campaign->highlight_text }}</div>
                            </td>
                        </tr>
                    </table>
                @endif

                <x-mail.button :url="$ctaUrl" variant="brand">{{ $ctaLabel }} &rarr;</x-mail.button>
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

    <x-mail.shop-collections />
    <x-mail.shop-categories />
    <x-mail.shop-promises />

    <x-mail.brand-footer greeting="With love," :tags="['Jewellery', 'Exclusive Offers', 'Everyday Sparkle', 'Rupnora']" />
</x-mail.layout>

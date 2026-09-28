@php
    $imageUrl = fn (string $path, string $size) => url(str_replace('-lg.webp', "-{$size}.webp", $path));
    $products = collect(\App\Support\StorefrontCatalog::newArrivals(4))->filter(fn ($product) => filled($product['image']));

    // Without an uploaded campaign image, the newest arrival becomes the hero.
    $heroImage = $campaign->image_url;
    $heroProduct = null;

    if (! $heroImage && $products->isNotEmpty()) {
        $heroProduct = $products->shift();
        $heroImage = $imageUrl($heroProduct['image'], 'md');
    }

    $gridProducts = $products->take(3)->values();

    $ctaUrl = $campaign->cta_url ?: route('new-arrivals');
    $ctaLabel = $campaign->cta_label ?: 'Discover the Design';

    $paragraphs = collect(preg_split('/\R{2,}/', trim($campaign->body)))
        ->filter(fn ($paragraph) => trim($paragraph) !== '')
        ->map(fn ($paragraph) => str_replace('Rupnora', '<strong style="color:#6144ac;">Rupnora</strong>', nl2br(e(trim($paragraph)), false)));
@endphp

<x-mail.layout :title="$campaign->subject" :preheader="$campaign->preheader ?: $campaign->headline" padding="20px 28px 32px">
    <x-slot:head>
        <style>
            @media (max-width: 620px) {
                .dl-hero { padding: 28px 18px 26px !important; }
                .dl-headline { font-size: 26px !important; line-height: 33px !important; }
                .dl-card { padding: 0 0 16px !important; }
                .dl-card-img { max-width: 240px !important; }
            }
        </style>
    </x-slot:head>

    <x-mail.brand-header />

    {{-- Hero --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5e8ff; background-image:linear-gradient(160deg, #f6f3f9 0%, #efe2f9 60%, #e4d6f7 100%); border-radius:16px;">
        <tr>
            <td class="dl-hero" align="center" style="padding:34px 28px 30px;">
                <div style="font-family:Arial,sans-serif; font-size:10px; line-height:14px; font-weight:bold; letter-spacing:3px; text-transform:uppercase; color:#6144ac;">&#10022;&nbsp; {{ $campaign->eyebrow ?: 'Just Launched' }} &nbsp;&#10022;</div>
                <div class="dl-headline" style="margin-top:12px; font-family:Georgia,'Times New Roman',serif; font-size:32px; line-height:40px; font-style:italic; color:#231535;">{{ $campaign->headline }}</div>
                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:16px auto 0;">
                    <tr>
                        <td style="width:40px; height:2px; line-height:2px; font-size:0; background-color:#6144ac;">&nbsp;</td>
                    </tr>
                </table>

                @if ($heroImage)
                    <table role="presentation" width="440" cellpadding="0" cellspacing="0" style="width:100%; max-width:440px; margin:24px auto 0; background-color:#ffffff; border-radius:12px; box-shadow:0 10px 26px rgba(35,21,53,0.14);">
                        <tr>
                            <td style="padding:10px;">
                                <a href="{{ $heroProduct ? route('product.show', $heroProduct['slug']) : $ctaUrl }}">
                                    <img src="{{ $heroImage }}" width="420" alt="{{ $heroProduct['name'] ?? $campaign->headline }}" style="display:block; width:100%; max-width:420px; height:auto; border-radius:8px; background-color:#f6f3f9;">
                                </a>
                            </td>
                        </tr>
                        @if ($heroProduct)
                            <tr>
                                <td align="center" style="padding:2px 14px 14px; font-family:Arial,sans-serif;">
                                    <div style="font-size:9px; line-height:13px; letter-spacing:1.5px; text-transform:uppercase; color:#9e9fa5;">{{ $heroProduct['category_name'] }}</div>
                                    <div style="margin-top:3px; font-family:Georgia,'Times New Roman',serif; font-size:16px; line-height:22px; color:#231535;">{{ \Illuminate\Support\Str::limit($heroProduct['name'], 60) }}</div>
                                    <div style="margin-top:4px; font-size:14px; line-height:20px;">
                                        <strong style="color:#231535;">&#8377;{{ number_format($heroProduct['price']) }}</strong>
                                        @if ($heroProduct['mrp'] > $heroProduct['price'])
                                            &nbsp;<span style="font-size:12px; color:#c5c5c9; text-decoration:line-through;">&#8377;{{ number_format($heroProduct['mrp']) }}</span>
                                            &nbsp;<span style="font-size:12px; font-weight:bold; color:#47a545;">{{ round((($heroProduct['mrp'] - $heroProduct['price']) / $heroProduct['mrp']) * 100) }}% OFF</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </table>
                @endif

                @if ($campaign->highlight_text)
                    <div style="margin-top:22px; font-family:Georgia,'Times New Roman',serif; font-size:15px; line-height:22px; font-style:italic; color:#6144ac;">{{ $campaign->highlight_text }}</div>
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

    {{-- More new arrivals --}}
    @if ($gridProducts->isNotEmpty())
        <x-mail.section-title style="margin-top:22px;">New Arrivals</x-mail.section-title>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center" style="padding:8px 10px 18px; font-family:Arial,sans-serif; font-size:13px; line-height:20px; color:#4f3267;">Fresh pieces, just added to the Rupnora collection.</td>
            </tr>
        </table>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                @foreach ($gridProducts as $product)
                    @php $productUrl = route('product.show', $product['slug']); @endphp
                    <td class="m-stack dl-card" width="{{ floor(100 / $gridProducts->count()) }}%" valign="top" style="padding:0 4px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border:1px solid #efe2f9; border-radius:12px;">
                            <tr>
                                <td align="center" style="padding:8px 8px 0;">
                                    <a href="{{ $productUrl }}">
                                        <img class="dl-card-img" src="{{ $imageUrl($product['image'], 'sm') }}" width="156" alt="{{ $product['name'] }}" style="display:block; width:100%; max-width:156px; height:auto; margin:0 auto; border-radius:8px; background-color:#f6f3f9;">
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="padding:10px 10px 14px; font-family:Arial,sans-serif;">
                                    <div style="font-size:9px; line-height:13px; letter-spacing:1.5px; text-transform:uppercase; color:#9e9fa5;">{{ $product['category_name'] }}</div>
                                    <div style="height:34px; overflow:hidden; margin-top:4px; font-size:12px; line-height:17px; font-weight:bold; color:#231535;">{{ \Illuminate\Support\Str::limit($product['name'], 40) }}</div>
                                    <div style="margin-top:6px; font-size:13px; line-height:18px;">
                                        <strong style="color:#231535;">&#8377;{{ number_format($product['price']) }}</strong>
                                        @if ($product['mrp'] > $product['price'])
                                            <span style="font-size:11px; color:#c5c5c9; text-decoration:line-through;">&#8377;{{ number_format($product['mrp']) }}</span>
                                        @endif
                                    </div>
                                    <a href="{{ $productUrl }}" style="display:inline-block; margin-top:8px; font-size:10px; line-height:14px; font-weight:bold; letter-spacing:1.5px; text-transform:uppercase; color:#6144ac;">Shop now &rarr;</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                @endforeach
            </tr>
        </table>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center" style="padding-top:18px; font-family:Arial,sans-serif; font-size:12px; line-height:18px;">
                    <a href="{{ route('new-arrivals') }}" style="font-weight:bold; letter-spacing:1px; color:#6144ac; text-decoration:underline;">View all new arrivals &rarr;</a>
                </td>
            </tr>
        </table>
    @endif

    <x-mail.shop-collections />
    <x-mail.shop-categories />
    <x-mail.shop-promises />

    <x-mail.brand-footer greeting="With love," :tags="['Jewellery', 'New Arrivals', 'Everyday Sparkle', 'Rupnora']" />
</x-mail.layout>

@props([
    'title' => 'Shop by Collection',
    'intro' => 'Curated edits for every mood and moment.',
    'limit' => 4,
])

@php
    $imageUrl = fn (string $path) => url(str_replace('-lg.webp', '-sm.webp', $path));

    // Collection size is only exposed as a "12 Designs" tag, so its leading number filters out empty collections.
    $collections = collect(\App\Support\StorefrontCatalog::collections())
        ->filter(fn ($collection) => (int) $collection['tag'] > 0)
        ->take($limit)
        ->values();
@endphp

@if ($collections->isNotEmpty())
    <x-mail.section-title>{{ $title }}</x-mail.section-title>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:8px 10px 18px; font-family:Arial,sans-serif; font-size:13px; line-height:20px; color:#4f3267;">{{ $intro }}</td>
        </tr>
    </table>
    @foreach ($collections->chunk(2) as $row)
        {{-- A lone last card stays centred instead of hugging the left edge. --}}
        <table role="presentation" width="{{ $row->count() * 50 }}%" cellpadding="0" cellspacing="0" style="margin:0 auto;">
            <tr>
                @foreach ($row as $collection)
                    @php
                        $collectionUrl = route('collection.show', $collection['slug']);
                        $collectionImage = $collection['logo'] ?? $collection['banner'] ?? null;
                    @endphp
                    <td width="{{ floor(100 / $row->count()) }}%" valign="top" style="padding:0 4px 8px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border:1px solid #efe2f9; border-radius:12px;">
                            <tr>
                                <td style="padding:8px 8px 0;">
                                    <a href="{{ $collectionUrl }}">
                                        @if ($collectionImage)
                                            <img src="{{ $imageUrl($collectionImage) }}" width="244" alt="{{ $collection['name'] }}" style="display:block; width:100%; max-width:244px; height:auto; margin:0 auto; border-radius:8px; background-color:#f6f3f9;">
                                        @else
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#efe2f9; background-image:linear-gradient(160deg, #f6f3f9 0%, #efe2f9 60%, #e4d6f7 100%); border-radius:8px;">
                                                <tr>
                                                    <td align="center" valign="middle" height="140" style="height:140px; font-family:Georgia,'Times New Roman',serif; font-size:30px; line-height:30px; color:#6144ac;">&#10022;</td>
                                                </tr>
                                            </table>
                                        @endif
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="m-coll-body" align="center" style="padding:12px 10px 14px; font-family:Arial,sans-serif;">
                                    <div style="font-size:9px; line-height:13px; letter-spacing:1.5px; text-transform:uppercase; color:#9e9fa5;">{{ $collection['tag'] }}</div>
                                    <div class="m-coll-name" style="margin-top:4px; font-family:Georgia,'Times New Roman',serif; font-size:16px; line-height:22px; color:#231535;">{{ \Illuminate\Support\Str::limit($collection['name'], 40) }}</div>
                                    <a href="{{ $collectionUrl }}" style="display:inline-block; margin-top:8px; font-size:10px; line-height:14px; font-weight:bold; letter-spacing:1.5px; text-transform:uppercase; color:#6144ac;">Explore &rarr;</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                @endforeach
            </tr>
        </table>
    @endforeach
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding-top:10px; font-family:Arial,sans-serif; font-size:12px; line-height:18px;">
                <a href="{{ route('collections.index') }}" style="font-weight:bold; letter-spacing:1px; color:#6144ac; text-decoration:underline;">View all collections &rarr;</a>
            </td>
        </tr>
    </table>
@endif

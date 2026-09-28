@props([
    'title' => 'Shop by Category',
    'intro' => 'From everyday classics to statement pieces, find your favourites.',
    'limit' => 4,
])

@php
    $imageUrl = fn (string $path) => url(str_replace('-lg.webp', '-sm.webp', $path));

    // Products are filed under subcategories, so a parent counts its children's designs too.
    // Pendants are deliberately left out of these emails.
    $categories = \App\Models\Category::query()
        ->active()
        ->parents()
        ->where('slug', '!=', 'pendants')
        ->withCount(['products' => fn ($query) => $query->active()])
        ->with(['children' => fn ($query) => $query->withCount(['products' => fn ($query) => $query->active()])])
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get()
        ->map(fn ($category) => [
            'slug' => $category->slug,
            'name' => $category->name,
            'image' => $category->image ? '/storage/'.ltrim($category->image, '/') : null,
            'count' => $category->products_count + $category->children->sum('products_count'),
        ])
        ->filter(fn ($category) => $category['count'] > 0)
        ->take($limit)
        ->values();
@endphp

@if ($categories->isNotEmpty())
    <x-mail.section-title>{{ $title }}</x-mail.section-title>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:8px 10px 18px; font-family:Arial,sans-serif; font-size:13px; line-height:20px; color:#4f3267;">{{ $intro }}</td>
        </tr>
    </table>
    @foreach ($categories->chunk(4) as $row)
        <table role="presentation" width="{{ $row->count() * 25 }}%" cellpadding="0" cellspacing="0" style="margin:0 auto;">
            <tr>
                @foreach ($row as $category)
                    @php $categoryUrl = route('category.show', $category['slug']); @endphp
                    <td width="{{ floor(100 / $row->count()) }}%" align="center" valign="top" style="padding:0 4px 16px; font-family:Arial,sans-serif;">
                        <a href="{{ $categoryUrl }}">
                            @if ($category['image'])
                                <img class="m-cat-img" src="{{ $imageUrl($category['image']) }}" width="72" alt="{{ $category['name'] }}" style="display:block; width:72px; height:auto; margin:0 auto; border-radius:50%; border:1px solid #cfc1ff; background-color:#f6f3f9;">
                            @else
                                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto;">
                                    <tr>
                                        <td class="m-cat-mono" align="center" valign="middle" width="72" height="72" style="width:72px; height:72px; border-radius:50%; border:1px solid #cfc1ff; background-color:#f6f3f9; font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:72px; color:#6144ac;">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($category['name'], 0, 1)) }}</td>
                                    </tr>
                                </table>
                            @endif
                        </a>
                        <a href="{{ $categoryUrl }}" style="display:block; margin-top:8px; font-size:12px; line-height:16px; font-weight:bold; color:#231535;">{{ \Illuminate\Support\Str::limit($category['name'], 22) }}</a>
                        <div style="margin-top:2px; font-size:10px; line-height:14px; color:#9e9fa5;">{{ $category['count'] }} {{ \Illuminate\Support\Str::plural('Design', $category['count']) }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    @endforeach
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding-top:2px; font-family:Arial,sans-serif; font-size:12px; line-height:18px;">
                <a href="{{ route('categories.index') }}" style="font-weight:bold; letter-spacing:1px; color:#6144ac; text-decoration:underline;">View all categories &rarr;</a>
            </td>
        </tr>
    </table>
@endif

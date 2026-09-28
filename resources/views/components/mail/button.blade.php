@props([
    'url',
    'color' => '#231535',
    'variant' => 'pill',
])
@php
    $radius = $variant === 'brand' ? '6px' : '999px';
    $text = $variant === 'brand'
        ? 'padding:14px 30px; font-size:12px; line-height:16px; letter-spacing:2px; text-transform:uppercase;'
        : 'padding:14px 34px; font-size:14px;';
@endphp
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:28px auto 4px;">
    <tr>
        <td align="center" style="border-radius:{{ $radius }}; background-color:{{ $color }};">
            <a href="{{ $url }}" style="display:inline-block; {{ $text }} font-family:Arial,sans-serif; font-weight:700; color:#ffffff; text-decoration:none; border-radius:{{ $radius }};">{{ $slot }}</a>
        </td>
    </tr>
</table>

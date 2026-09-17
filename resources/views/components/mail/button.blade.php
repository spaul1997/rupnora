@props([
    'url',
    'color' => '#231535',
])
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:28px auto 4px;">
    <tr>
        <td align="center" style="border-radius:999px; background-color:{{ $color }};">
            <a href="{{ $url }}" style="display:inline-block; padding:14px 34px; font-family:Arial,sans-serif; font-size:14px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:999px;">{{ $slot }}</a>
        </td>
    </tr>
</table>

@props([
    'type' => 'success',
])
@php
    $variants = [
        'success' => ['bg' => '#e6f4e6', 'fg' => '#47a545', 'symbol' => '&#10003;'],
        'error' => ['bg' => '#fdeceb', 'fg' => '#fb6366', 'symbol' => '&#10005;'],
        'info' => ['bg' => '#efe2f9', 'fg' => '#6144ac', 'symbol' => '&#33;'],
    ];
    $config = $variants[$type] ?? $variants['success'];
@endphp
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:4px auto 20px;">
    <tr>
        <td align="center" valign="middle" width="56" height="56" style="width:56px; height:56px; border-radius:50%; background-color:{{ $config['bg'] }}; font-family:Arial,sans-serif; font-size:24px; font-weight:700; line-height:56px; color:{{ $config['fg'] }}; text-align:center;">
            {!! $config['symbol'] !!}
        </td>
    </tr>
</table>

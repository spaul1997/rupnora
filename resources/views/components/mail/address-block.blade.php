@props([
    'address',
    'label' => 'Delivery Address',
])
@if ($address)
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
        <tr>
            <td style="font-family:Arial,sans-serif; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#9e9fa5; padding-bottom:6px;">{{ $label }}</td>
        </tr>
        <tr>
            <td style="font-family:Arial,sans-serif; font-size:13px; line-height:20px; color:#4f3267;">
                {{ $address['name'] ?? '' }}<br>
                {{ trim(($address['line1'] ?? '').(isset($address['line2']) && $address['line2'] ? ', '.$address['line2'] : ''), ', ') }}<br>
                {{ collect([$address['city'] ?? '', $address['district'] ?? '', $address['state'] ?? ''])->filter()->join(', ') }} {{ $address['pincode'] ?? '' }}<br>
                {{ $address['country'] ?? '' }}
                @if (!empty($address['phone']))
                    <br>{{ $address['phone'] }}
                @endif
            </td>
        </tr>
    </table>
@endif

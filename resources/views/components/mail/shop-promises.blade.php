@php
    $freeShippingThreshold = (float) \App\Models\WebsiteSetting::current()->free_shipping_threshold;
    $perks = [
        [
            ['icon' => '&#10003;', 'title' => 'Certified Jewellery', 'note' => 'Quality you can trust'],
            ['icon' => '&#8634;', 'title' => 'Easy Returns', 'note' => 'Shop with confidence'],
        ],
        [
            ['icon' => '&#8377;', 'title' => 'Secure Payments', 'note' => 'Protected checkout'],
            $freeShippingThreshold > 0
                ? ['icon' => '&#10148;', 'title' => 'Free Shipping', 'note' => 'On orders above ₹'.number_format($freeShippingThreshold)]
                : ['icon' => '&#10022;', 'title' => 'Crafted with Care', 'note' => 'Finished to sparkle every day'],
        ],
    ];
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:30px; background-color:#f6f3f9; border-radius:14px;">
    <tr>
        <td style="padding:14px 12px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                @foreach ($perks as $row)
                    <tr>
                        @foreach ($row as $perk)
                            <td class="m-stack m-perk" width="50%" valign="middle" style="padding:10px 12px;">
                                <table role="presentation" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="center" valign="middle" style="width:34px; height:34px; border-radius:50%; background-color:#ffffff; border:1px solid #cfc1ff; font-family:'Segoe UI Symbol',Arial,sans-serif; font-size:15px; line-height:34px; color:#6144ac;">{!! $perk['icon'] !!}</td>
                                        <td style="padding-left:10px; font-family:Arial,sans-serif;">
                                            <div style="font-size:12px; line-height:16px; font-weight:bold; color:#231535;">{{ $perk['title'] }}</div>
                                            <div style="font-size:11px; line-height:15px; color:#9e9fa5;">{{ $perk['note'] }}</div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </td>
    </tr>
</table>

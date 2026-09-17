@props([
    'order',
])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0; border:1px solid #e3e3e3; border-radius:12px; overflow:hidden;">
    <tr>
        <td style="padding:14px 20px; background-color:#f6f3f9; font-family:Arial,sans-serif; font-size:13px; color:#4f3267;">
            <strong>Order #{{ $order->order_number }}</strong> &middot; {{ optional($order->created_at)->format('d M Y') }}
        </td>
    </tr>
    @foreach ($order->items as $item)
        <tr>
            <td style="padding:14px 20px; border-top:1px solid #e3e3e3;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="font-family:Arial,sans-serif; font-size:13px; color:#231535; line-height:19px;">
                            {{ $item->product_name }}
                            @php
                                $meta = collect([$item->metal, $item->purity, $item->size ? 'Size '.$item->size : null])->filter()->implode(' &middot; ');
                            @endphp
                            @if ($meta)
                                <br><span style="color:#9e9fa5; font-size:12px;">{!! $meta !!}</span>
                            @endif
                            <br><span style="color:#9e9fa5; font-size:12px;">Qty: {{ $item->quantity }}</span>
                        </td>
                        <td align="right" valign="top" style="font-family:Arial,sans-serif; font-size:13px; color:#231535; white-space:nowrap; padding-top:2px;">
                            &#8377;{{ number_format((float) $item->total, 2) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    @endforeach
    <tr>
        <td style="padding:16px 20px; border-top:1px solid #e3e3e3;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-family:Arial,sans-serif; font-size:13px;">
                <tr>
                    <td style="padding:2px 0; color:#9e9fa5;">Subtotal</td>
                    <td align="right" style="padding:2px 0; color:#231535;">&#8377;{{ number_format((float) $order->subtotal, 2) }}</td>
                </tr>
                @if ((float) $order->discount_amount > 0 || (float) $order->coupon_discount > 0)
                    <tr>
                        <td style="padding:2px 0; color:#9e9fa5;">Discount</td>
                        <td align="right" style="padding:2px 0; color:#fb6366;">&minus;&#8377;{{ number_format((float) $order->discount_amount + (float) $order->coupon_discount, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td style="padding:2px 0; color:#9e9fa5;">Shipping</td>
                    <td align="right" style="padding:2px 0; color:#231535;">&#8377;{{ number_format((float) $order->shipping_charge, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding:2px 0; color:#9e9fa5;">GST</td>
                    <td align="right" style="padding:2px 0; color:#231535;">&#8377;{{ number_format((float) $order->gst_amount, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 0 0; border-top:1px solid #e3e3e3; font-weight:700; color:#231535; font-size:14px;">Grand Total</td>
                    <td align="right" style="padding:10px 0 0; border-top:1px solid #e3e3e3; font-weight:700; color:#231535; font-size:14px;">&#8377;{{ number_format((float) $order->grand_total, 2) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

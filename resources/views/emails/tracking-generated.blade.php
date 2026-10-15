<x-mail.layout :title="'Your Order Is Ready to Track'" :preheader="'Tracking is now available for order #'.$order->order_number.'.'" accent="#6144ac">
    <x-mail.status-icon type="success" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:32px; color:#231535; padding-bottom:12px;">
                Your order is ready to track
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:14px; line-height:22px; color:#4f3267; padding-bottom:4px;">
                Hi {{ $order->customer_name }}, tracking has been created for order #{{ $order->order_number }}.
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0; border:1px solid #e3e3e3; border-radius:12px;">
        <tr>
            <td style="padding:18px 20px; font-family:Arial,sans-serif; font-size:13px; color:#231535;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding:5px 0; color:#9e9fa5;">Courier</td>
                        <td align="right" style="padding:5px 0; font-weight:700;">{{ $order->courier_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding:5px 0; color:#9e9fa5;">Tracking ID / AWB</td>
                        <td align="right" style="padding:5px 0; font-weight:700;">{{ $order->tracking_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding:5px 0; color:#9e9fa5;">Order Number</td>
                        <td align="right" style="padding:5px 0;">#{{ $order->order_number }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <x-mail.button :url="$order->tracking_url" color="#231535">Track Shipment</x-mail.button>
            </td>
        </tr>
    </table>

    <p style="margin:18px 0 0; font-family:Arial,sans-serif; font-size:12px; line-height:18px; text-align:center; color:#9e9fa5;">
        Tracking URL:<br>
        <a href="{{ $order->tracking_url }}" style="color:#6144ac; word-break:break-all;">{{ $order->tracking_url }}</a>
    </p>
</x-mail.layout>

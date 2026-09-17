<x-mail.layout :title="'Refund Completed'" :preheader="'Your refund for order #'.$order->order_number.' has been completed.'" accent="#47a545">
    <x-mail.status-icon type="success" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:32px; color:#231535; padding-bottom:12px;">
                Refund Completed
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:14px; line-height:22px; color:#4f3267; padding-bottom:4px;">
                Hi {{ $order->customer_name }}, your refund for order #{{ $order->order_number }} has been successfully credited.
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0; border:1px solid #e3e3e3; border-radius:12px;">
        <tr>
            <td style="padding:18px 20px; font-family:Arial,sans-serif; font-size:13px; color:#231535;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding:4px 0; color:#9e9fa5;">Refunded Amount</td>
                        <td align="right" style="padding:4px 0; font-weight:700; font-size:15px; color:#47a545;">&#8377;{{ number_format((float) $order->refund_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0; color:#9e9fa5;">Order Number</td>
                        <td align="right" style="padding:4px 0;">#{{ $order->order_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0; color:#9e9fa5;">Completed On</td>
                        <td align="right" style="padding:4px 0;">{{ now()->format('d M Y') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <x-mail.button :url="route('order.success', $order->id)" color="#231535">View Order</x-mail.button>
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:12px; line-height:18px; color:#9e9fa5; padding-top:20px;">
                Thank you for your patience. We hope to see you again soon.
            </td>
        </tr>
    </table>
</x-mail.layout>

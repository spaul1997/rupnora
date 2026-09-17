<x-mail.layout :title="'Payment Received'" :preheader="'We\'ve received your payment for order #'.$order->order_number.'.'" accent="#47a545">
    <x-mail.status-icon type="success" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:32px; color:#231535; padding-bottom:12px;">
                Payment Received
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:14px; line-height:22px; color:#4f3267; padding-bottom:4px;">
                Hi {{ $order->customer_name }}, we've successfully received your payment for order #{{ $order->order_number }}.
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0; border:1px solid #e3e3e3; border-radius:12px;">
        <tr>
            <td style="padding:18px 20px; font-family:Arial,sans-serif; font-size:13px; color:#231535;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding:4px 0; color:#9e9fa5;">Amount Paid</td>
                        <td align="right" style="padding:4px 0; font-weight:700; font-size:15px;">&#8377;{{ number_format((float) $order->paid_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0; color:#9e9fa5;">Payment Method</td>
                        <td align="right" style="padding:4px 0;">{{ $order->payment_method ? ucfirst($order->payment_method) : 'Online' }}</td>
                    </tr>
                    @if ($order->transaction_id)
                        <tr>
                            <td style="padding:4px 0; color:#9e9fa5;">Transaction ID</td>
                            <td align="right" style="padding:4px 0;">{{ $order->transaction_id }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding:4px 0; color:#9e9fa5;">Order Number</td>
                        <td align="right" style="padding:4px 0;">#{{ $order->order_number }}</td>
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
    </table>
</x-mail.layout>

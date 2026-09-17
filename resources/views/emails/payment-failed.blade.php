<x-mail.layout :title="'Payment Failed'" :preheader="'Your payment for order #'.$order->order_number.' could not be completed.'" accent="#fb6366">
    <x-mail.status-icon type="error" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:32px; color:#231535; padding-bottom:12px;">
                Payment Failed
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:14px; line-height:22px; color:#4f3267; padding-bottom:4px;">
                Hi {{ $order->customer_name }}, your payment of &#8377;{{ number_format((float) $order->grand_total, 2) }} for order #{{ $order->order_number }} could not be processed.
                @if ($reason)
                    {{ $reason }}
                @endif
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:13px; line-height:20px; color:#9e9fa5; padding-top:4px;">
                Your order is on hold and no amount has been deducted. You can retry the payment anytime within the next 24 hours.
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <x-mail.button :url="route('order.success', $order->id)" color="#231535">Retry Payment</x-mail.button>
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:12px; line-height:18px; color:#9e9fa5; padding-top:20px;">
                Need help? <a href="{{ route('contact') }}" style="color:#6144ac; font-weight:600;">Contact our support team</a>.
            </td>
        </tr>
    </table>
</x-mail.layout>

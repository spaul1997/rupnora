<x-mail.layout :title="'Order Could Not Be Placed'" :preheader="'We couldn\'t process your order #'.$order->order_number.'.'" accent="#fb6366">
    <x-mail.status-icon type="error" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:32px; color:#231535; padding-bottom:12px;">
                We Couldn't Place Your Order
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:14px; line-height:22px; color:#4f3267; padding-bottom:4px;">
                Hi {{ $order->customer_name }}, unfortunately order #{{ $order->order_number }} could not be completed.
                @if ($reason)
                    {{ $reason }}
                @else
                    Please check your payment details and try again.
                @endif
            </td>
        </tr>
        @if ((float) $order->paid_amount > 0)
            <tr>
                <td align="center" style="font-family:Arial,sans-serif; font-size:13px; line-height:20px; color:#9e9fa5; padding-top:4px;">
                    Any amount deducted will be refunded to your original payment method within 5&ndash;7 business days.
                </td>
            </tr>
        @endif
    </table>

    <x-mail.order-summary :order="$order" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <x-mail.button :url="route('cart')" color="#231535">Try Again</x-mail.button>
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:12px; line-height:18px; color:#9e9fa5; padding-top:20px;">
                Need help? <a href="{{ route('contact') }}" style="color:#6144ac; font-weight:600;">Contact our support team</a>.
            </td>
        </tr>
    </table>
</x-mail.layout>

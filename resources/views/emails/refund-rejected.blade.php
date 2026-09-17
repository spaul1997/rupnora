<x-mail.layout :title="'Update on Your Return Request'" :preheader="'An update on your return request for order #'.$order->order_number.'.'" accent="#fb6366">
    <x-mail.status-icon type="error" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:32px; color:#231535; padding-bottom:12px;">
                Update on Your Return Request
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:14px; line-height:22px; color:#4f3267; padding-bottom:4px;">
                Hi {{ $order->customer_name }}, after reviewing your return request for order #{{ $order->order_number }}, we're unable to process it at this time.
            </td>
        </tr>
        @if ($reason)
            <tr>
                <td align="center" style="padding-top:4px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" style="background-color:#f6f3f9; border-radius:10px;">
                        <tr>
                            <td style="padding:12px 16px; font-family:Arial,sans-serif; font-size:13px; line-height:20px; color:#4f3267;">
                                <strong>Reason:</strong> {{ $reason }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endif
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <x-mail.button :url="route('contact')" color="#231535">Contact Support</x-mail.button>
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:12px; line-height:18px; color:#9e9fa5; padding-top:20px;">
                If you believe this is a mistake, reply to this email or reach out and our team will help.
            </td>
        </tr>
    </table>
</x-mail.layout>

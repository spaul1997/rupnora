<x-mail.layout :title="'Return Request Accepted'" :preheader="'Your return request for order #'.$order->order_number.' has been accepted.'" accent="#47a545">
    <x-mail.status-icon type="success" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:32px; color:#231535; padding-bottom:12px;">
                Your Return Request is Accepted
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:14px; line-height:22px; color:#4f3267; padding-bottom:4px;">
                Hi {{ $order->customer_name }}, good news &mdash; we've accepted your return request for order #{{ $order->order_number }}.
                @if ($note)
                    {{ $note }}
                @else
                    Our courier partner will reach out shortly to schedule a pickup.
                @endif
            </td>
        </tr>
    </table>

    <x-mail.order-summary :order="$order" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <x-mail.button :url="route('order.success', $order->id)" color="#231535">View Order</x-mail.button>
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:12px; line-height:18px; color:#9e9fa5; padding-top:20px;">
                Once we receive and inspect the item, your refund will be processed automatically.
            </td>
        </tr>
    </table>
</x-mail.layout>

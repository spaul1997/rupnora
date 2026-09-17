<x-mail.layout :title="'Order Confirmed'" :preheader="'Your order #'.$order->order_number.' has been placed successfully.'" accent="#47a545">
    <x-mail.status-icon type="success" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:32px; color:#231535; padding-bottom:12px;">
                Order Confirmed!
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:14px; line-height:22px; color:#4f3267; padding-bottom:4px;">
                Hi {{ $order->customer_name }}, thank you for shopping with Rupnora. We've received your order and it's now being prepared with care.
            </td>
        </tr>
    </table>

    <x-mail.order-summary :order="$order" />

    <x-mail.address-block :address="$order->shipping_address" label="Delivery Address" />

    @if ($order->estimated_delivery)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 8px; background-color:#f6f3f9; border-radius:10px;">
            <tr>
                <td style="padding:12px 16px; font-family:Arial,sans-serif; font-size:13px; color:#4f3267;">
                    Estimated delivery: <strong>{{ \Illuminate\Support\Carbon::parse($order->estimated_delivery)->format('d M Y') }}</strong>
                </td>
            </tr>
        </table>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <x-mail.button :url="route('order.success', $order->id)" color="#231535">Track Your Order</x-mail.button>
            </td>
        </tr>
    </table>
</x-mail.layout>

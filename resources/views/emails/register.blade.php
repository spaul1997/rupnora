<x-mail.layout :title="'Welcome to Rupnora'" :preheader="'Your account is ready — start exploring certified jewellery made for you.'" accent="#6144ac">
    <x-mail.status-icon type="success" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:32px; color:#231535; padding-bottom:12px;">
                Welcome to Rupnora, {{ $user->name }}!
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:14px; line-height:22px; color:#4f3267; padding-bottom:4px;">
                Your account has been created successfully. You're all set to explore certified gold, diamond and silver jewellery, track orders and enjoy member-only offers.
            </td>
        </tr>
        <tr>
            <td align="center">
                <x-mail.button :url="url('/')" color="#231535">Start Shopping</x-mail.button>
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:12px; line-height:18px; color:#9e9fa5; padding-top:24px;">
                Signed up with {{ $user->email }}.<br>If this wasn't you, please contact our support team right away.
            </td>
        </tr>
    </table>
</x-mail.layout>

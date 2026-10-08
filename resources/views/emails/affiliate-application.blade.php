<x-mail.layout :title="'Affiliate Application Received'" :preheader="'Your Rupnora affiliate application is now under review.'" accent="#6144ac">
    <x-mail.status-icon type="success" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:32px; color:#231535; padding-bottom:12px;">
                Application received
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:14px; line-height:22px; color:#4f3267; padding-bottom:4px;">
                Hi {{ $profile->user->name }}, thank you for applying to become a Rupnora affiliate. Our team will review your application and notify you when its status is updated.
            </td>
        </tr>
        <tr>
            <td align="center">
                <x-mail.button :url="route('account.affiliate.dashboard')" color="#231535">View Application</x-mail.button>
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:12px; line-height:18px; color:#9e9fa5; padding-top:24px;">
                Submitted on {{ $profile->applied_at->format('d M Y') }} using {{ $profile->user->email }}.
            </td>
        </tr>
    </table>
</x-mail.layout>

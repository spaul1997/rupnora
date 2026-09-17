<x-mail.layout :title="'Password Changed'" preheader="Your Rupnora account password was changed successfully." accent="#47a545">
    <x-mail.status-icon type="success" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:32px; color:#231535; padding-bottom:12px;">
                Password Changed Successfully
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:14px; line-height:22px; color:#4f3267; padding-bottom:4px;">
                @if ($name)Hi {{ $name }}, your@else Your @endif Rupnora account password was changed on {{ now()->format('d M Y \a\t h:i A') }}.
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:13px; line-height:20px; color:#9e9fa5; padding-top:4px;">
                If you made this change, no further action is needed.
            </td>
        </tr>
        <tr>
            <td align="center">
                <x-mail.button :url="route('login')" color="#231535">Sign In</x-mail.button>
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:12px; line-height:18px; color:#9e9fa5; padding-top:24px;">
                If you didn't request this change, please <a href="{{ route('contact') }}" style="color:#6144ac; font-weight:600;">contact our support team</a> immediately.
            </td>
        </tr>
    </table>
</x-mail.layout>

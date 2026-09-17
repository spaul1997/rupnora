<x-mail.layout :title="'Your Verification Code'" preheader="Use this code to reset your Rupnora account password." accent="#6144ac">
    <x-mail.status-icon type="info" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:32px; color:#231535; padding-bottom:12px;">
                Verify It's You
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:14px; line-height:22px; color:#4f3267; padding-bottom:4px;">
                @if ($name)Hi {{ $name }}, use@else Use @endif the code below to reset your Rupnora account password. This code expires in 10 minutes.
            </td>
        </tr>
        <tr>
            <td align="center" style="padding:24px 0;">
                <table role="presentation" cellpadding="0" cellspacing="0">
                    <tr>
                        @foreach (str_split($otp) as $digit)
                            <td style="width:48px; height:56px; text-align:center; vertical-align:middle; font-family:Georgia,'Times New Roman',serif; font-size:26px; font-weight:700; color:#231535; background-color:#f6f3f9; border:1px solid #e3e3e3; border-radius:10px;">
                                {{ $digit }}
                            </td>
                            @if (!$loop->last)
                                <td style="width:10px;">&nbsp;</td>
                            @endif
                        @endforeach
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td align="center" style="font-family:Arial,sans-serif; font-size:12px; line-height:18px; color:#9e9fa5;">
                Didn't request this? You can safely ignore this email &mdash; your password won't be changed.
            </td>
        </tr>
    </table>
</x-mail.layout>

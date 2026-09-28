<x-mail.marketing-campaign :campaign="$campaign" :recipient-name="$recipientName" accent="#6144ac" badge-label="Exclusive Offer">
    @if ($campaign->highlight_text)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f3f9; border:1px dashed #8b63fb; border-radius:12px;">
            <tr>
                <td align="center" style="padding:18px 20px;">
                    <div style="font-family:Arial,sans-serif; font-size:10px; font-weight:bold; letter-spacing:1.7px; text-transform:uppercase; color:#9e9fa5;">Your special offer</div>
                    <div style="margin-top:7px; font-family:Georgia,'Times New Roman',serif; font-size:24px; color:#6144ac;">{{ $campaign->highlight_text }}</div>
                </td>
            </tr>
        </table>
    @endif
</x-mail.marketing-campaign>

<x-mail.marketing-campaign :campaign="$campaign" :recipient-name="$recipientName" accent="#231535" badge-label="Partnership Agreement">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e3e3e3; border-radius:12px;">
        <tr>
            <td style="padding:18px 20px; font-family:Arial,sans-serif; font-size:13px; line-height:22px; color:#4f3267;">
                <strong style="color:#231535;">Agreement reference</strong><br>
                {{ $campaign->highlight_text ?: 'Please review the collaboration terms and confirm your acceptance with our partnerships team.' }}
            </td>
        </tr>
    </table>
</x-mail.marketing-campaign>

<x-mail.marketing-campaign :campaign="$campaign" :recipient-name="$recipientName" accent="#6144ac" badge-label="Brand Collaboration">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f3f9; border-radius:12px;">
        <tr>
            <td style="padding:18px 20px; font-family:Arial,sans-serif; font-size:13px; line-height:22px; color:#4f3267;">
                <strong style="color:#231535;">Collaboration opportunity</strong><br>
                {{ $campaign->highlight_text ?: 'Creative storytelling, jewellery styling and an audience-first partnership with Rupnora.' }}
            </td>
        </tr>
    </table>
</x-mail.marketing-campaign>

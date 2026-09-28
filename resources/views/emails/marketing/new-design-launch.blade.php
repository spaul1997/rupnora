<x-mail.marketing-campaign :campaign="$campaign" :recipient-name="$recipientName" accent="#8b63fb" badge-label="New Design">
    @if ($campaign->highlight_text)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center" style="font-family:Georgia,'Times New Roman',serif; font-size:18px; line-height:26px; color:#6144ac; font-style:italic;">{{ $campaign->highlight_text }}</td>
            </tr>
        </table>
    @endif
</x-mail.marketing-campaign>

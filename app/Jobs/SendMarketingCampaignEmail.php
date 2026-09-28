<?php

namespace App\Jobs;

use App\Mail\MarketingCampaignMail;
use App\Models\EmailCampaignRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class SendMarketingCampaignEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public array $backoff = [60, 300, 900];

    public function __construct(public int $recipientId)
    {
        $this->onQueue((string) config('marketing.queue', 'emails'));
    }

    public function handle(): void
    {
        $recipient = EmailCampaignRecipient::with('campaign')->find($this->recipientId);

        if (! $recipient || $recipient->status === 'sent') {
            return;
        }

        $recipient->update([
            'status' => 'sending',
            'attempts' => $recipient->attempts + 1,
            'failed_at' => null,
            'failure_message' => null,
        ]);
        $recipient->campaign->refreshDeliveryStats();

        try {
            Mail::to(new Address($recipient->email, $recipient->name ?: $recipient->email))
                ->cc(new Address($recipient->campaign->cc_email, 'Rupnora Marketing'))
                ->send(new MarketingCampaignMail($recipient->campaign, $recipient->name));

            $recipient->update([
                'status' => 'sent',
                'sent_at' => now(),
                'failed_at' => null,
                'failure_message' => null,
            ]);
            $recipient->campaign->refreshDeliveryStats();
        } catch (Throwable $exception) {
            $this->markFailed($recipient, $exception);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $recipient = EmailCampaignRecipient::with('campaign')->find($this->recipientId);

        if ($recipient && $recipient->status !== 'sent') {
            $this->markFailed($recipient, $exception);
        }
    }

    protected function markFailed(EmailCampaignRecipient $recipient, ?Throwable $exception): void
    {
        $recipient->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_message' => Str::limit($exception?->getMessage() ?: 'The message could not be sent.', 1000, ''),
        ]);
        $recipient->campaign->refreshDeliveryStats();
    }
}

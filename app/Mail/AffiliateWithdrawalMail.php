<?php

namespace App\Mail;

use App\Models\AffiliateWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AffiliateWithdrawalMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public AffiliateWithdrawal $withdrawal,
        public string $event,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Rupnora'),
            subject: match ($this->event) {
                'paid' => 'Your affiliate withdrawal has been paid',
                'rejected' => 'Your affiliate withdrawal was rejected',
                'failed' => 'Your affiliate withdrawal could not be completed',
                default => 'We received your affiliate withdrawal request',
            },
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.affiliate-withdrawal',
            with: ['withdrawal' => $this->withdrawal->loadMissing('affiliate.user')],
        );
    }
}

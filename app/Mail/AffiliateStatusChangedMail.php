<?php

namespace App\Mail;

use App\Models\AffiliateProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AffiliateStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public AffiliateProfile $profile,
        public string $previousStatus,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Rupnora'),
            subject: match ($this->profile->status) {
                'approved' => 'Your Rupnora affiliate application is approved',
                'rejected' => 'Update on your Rupnora affiliate application',
                'suspended' => 'Your Rupnora affiliate account has been suspended',
                default => 'Your Rupnora affiliate status has been updated',
            },
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.affiliate-status-changed',
            with: ['profile' => $this->profile->loadMissing('user')],
        );
    }
}

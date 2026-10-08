<?php

namespace App\Mail;

use App\Models\Influencer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InfluencerApplicationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Influencer $influencer,
        public bool $createdByAdmin = false,
        public string $status = 'new',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Rupnora'),
            subject: $this->createdByAdmin
                ? 'Your Rupnora influencer profile has been created'
                : 'We received your Rupnora influencer application',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.influencer-application',
            with: [
                'influencer' => $this->influencer,
                'createdByAdmin' => $this->createdByAdmin,
                'status' => $this->status,
            ],
        );
    }
}

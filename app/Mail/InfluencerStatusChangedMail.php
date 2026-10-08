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

class InfluencerStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Influencer $influencer,
        public string $previousStatus,
        public string $status,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Rupnora'),
            subject: $this->status === 'approved'
                ? 'Your Rupnora influencer application is approved'
                : 'Update on your Rupnora influencer application',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.influencer-status-changed',
            with: [
                'influencer' => $this->influencer,
                'previousStatus' => $this->previousStatus,
                'status' => $this->status,
            ],
        );
    }
}

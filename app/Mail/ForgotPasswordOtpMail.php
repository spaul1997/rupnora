<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ForgotPasswordOtpMail extends Mailable
{
    public function __construct(public string $otp, public ?string $name = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Rupnora'),
            subject: 'Your Rupnora Verification Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.forgot-password-otp',
            with: [
                'otp' => $this->otp,
                'name' => $this->name,
            ],
        );
    }
}

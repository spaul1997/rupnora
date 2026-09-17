<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PasswordResetSuccessMail extends Mailable
{
    public function __construct(public ?string $name = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Rupnora'),
            subject: 'Your Password Has Been Changed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-success',
            with: ['name' => $this->name],
        );
    }
}

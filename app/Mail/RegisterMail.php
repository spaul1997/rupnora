<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RegisterMail extends Mailable
{
    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Rupnora'),
            subject: 'Welcome to Rupnora, '.$this->user->name.'!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.register',
            with: ['user' => $this->user],
        );
    }
}

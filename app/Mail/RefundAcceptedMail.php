<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RefundAcceptedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public ?string $note = null)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Rupnora'),
            subject: 'Your Return Request Has Been Accepted — Order #'.$this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.refund-accepted',
            with: [
                'order' => $this->order->loadMissing('items'),
                'note' => $this->note,
            ],
        );
    }
}

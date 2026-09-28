<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AffiliateActivity extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $title, public string $body, public ?string $url = null) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return ['title' => $this->title, 'body' => $this->body, 'url' => $this->url, 'type' => 'affiliate'];
    }
}

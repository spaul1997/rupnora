<?php

namespace App\Observers;

use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\AccountActivity;

class ContactMessageObserver
{
    public function updated(ContactMessage $message): void
    {
        if ($message->wasChanged('admin_reply') && filled($message->admin_reply)) {
            $customerId = $message->user_id;
            $body = 'There is a reply to your request '.$message->ticket_no.'.';
            $notify = fn () => User::customers()->find($customerId)?->notify(new AccountActivity('Support replied', $body, route('account.support')));
            $connection = $message->getConnection();
            if ($connection->transactionLevel() > 0) {
                $connection->afterCommit($notify);
            } else {
                $notify();
            }
        }
    }
}

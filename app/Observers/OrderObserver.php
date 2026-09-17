<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\User;
use App\Notifications\AccountActivity;
use Illuminate\Support\Str;

class OrderObserver
{
    public function created(Order $order): void
    {
        $this->notify($order, 'Order placed', 'Your order '.$order->order_number.' has been placed.');
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            $status = Str::headline($order->status);
            $this->notify($order, 'Order update: '.$status, 'Your order '.$order->order_number.' is now '.strtolower($status).'.');
        } elseif ($order->wasChanged('payment_status')) {
            $this->notify($order, 'Payment updated', 'Payment for '.$order->order_number.' is now '.Str::headline($order->payment_status).'.');
        }
    }

    private function notify(Order $order, string $title, string $body): void
    {
        $customerId = $order->user_id;
        $url = route('account.orders.show', $order->order_number);
        $notify = fn () => User::customers()->find($customerId)?->notify(new AccountActivity($title, $body, $url));
        $connection = $order->getConnection();

        if ($connection->transactionLevel() > 0) {
            $connection->afterCommit($notify);
        } else {
            $notify();
        }
    }
}

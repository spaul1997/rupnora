<?php

namespace App\Services;

use App\Mail\InfluencerApplicationMail;
use App\Mail\InfluencerStatusChangedMail;
use App\Models\Influencer;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class InfluencerMailService
{
    public function applicationReceived(Influencer $influencer, bool $createdByAdmin = false): void
    {
        $this->send($influencer, new InfluencerApplicationMail(
            $influencer,
            $createdByAdmin,
            $influencer->status,
        ));
    }

    public function statusChanged(Influencer $influencer, string $previousStatus): void
    {
        if ($previousStatus === $influencer->status || ! in_array($influencer->status, ['approved', 'rejected'], true)) {
            return;
        }

        $this->send($influencer, new InfluencerStatusChangedMail(
            $influencer,
            $previousStatus,
            $influencer->status,
        ));
    }

    private function send(Influencer $influencer, Mailable $mail): void
    {
        try {
            $pendingMail = Mail::to($influencer->email);
            $ccEmail = trim((string) config('marketing.cc_email'));

            if ($ccEmail !== '' && strcasecmp($ccEmail, $influencer->email) !== 0) {
                $pendingMail->cc($ccEmail);
            }

            $pendingMail->send($mail);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}

<?php

namespace App\Jobs;

use App\Services\AffiliateCommissionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReleaseAffiliateCommissions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct()
    {
        $this->onQueue((string) config('affiliate.queue', 'default'));
    }

    public function handle(AffiliateCommissionService $service): void
    {
        $service->releaseDue();
    }
}

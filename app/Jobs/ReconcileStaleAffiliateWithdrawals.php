<?php

namespace App\Jobs;

use App\Models\AffiliateAuditLog;
use App\Models\AffiliateWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReconcileStaleAffiliateWithdrawals implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue((string) config('affiliate.queue', 'default'));
    }

    public function handle(): void
    {
        AffiliateWithdrawal::query()
            ->where('status', 'processing')
            ->where('reconciliation_status', 'pending_confirmation')
            ->where('processing_at', '<=', now()->subDay())
            ->chunkById(100, function ($withdrawals) {
                foreach ($withdrawals as $withdrawal) {
                    $updated = AffiliateWithdrawal::query()->whereKey($withdrawal->id)
                        ->where('reconciliation_status', 'pending_confirmation')
                        ->update(['reconciliation_status' => 'manual_review']);
                    if ($updated) {
                        AffiliateAuditLog::create([
                            'affiliate_id' => $withdrawal->affiliate_id,
                            'withdrawal_id' => $withdrawal->id,
                            'event' => 'withdrawal_reconciliation_required',
                            'metadata' => ['reason' => 'Processing remained unconfirmed for 24 hours. Funds remain reserved.'],
                            'created_at' => now(),
                        ]);
                    }
                }
            });
    }
}

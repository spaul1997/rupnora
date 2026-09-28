<?php

namespace App\Services;

use App\Models\AffiliateLedgerEntry;
use App\Models\AffiliateProfile;

class AffiliateLedgerService
{
    public function append(AffiliateProfile $affiliate, string $reference, string $type, array $deltas, array $context = []): AffiliateLedgerEntry
    {
        return AffiliateLedgerEntry::firstOrCreate(
            ['reference' => $reference],
            [
                'affiliate_id' => $affiliate->id,
                'commission_id' => $context['commission_id'] ?? null,
                'withdrawal_id' => $context['withdrawal_id'] ?? null,
                'type' => $type,
                'pending_delta' => $deltas['pending'] ?? 0,
                'available_delta' => $deltas['available'] ?? 0,
                'reserved_delta' => $deltas['reserved'] ?? 0,
                'paid_delta' => $deltas['paid'] ?? 0,
                'reversed_delta' => $deltas['reversed'] ?? 0,
                'gross_amount' => $context['gross_amount'] ?? 0,
                'deduction_amount' => $context['deduction_amount'] ?? 0,
                'net_amount' => $context['net_amount'] ?? 0,
                'description' => $context['description'] ?? $type,
                'metadata' => $context['metadata'] ?? null,
                'occurred_at' => $context['occurred_at'] ?? now(),
            ]
        );
    }
}

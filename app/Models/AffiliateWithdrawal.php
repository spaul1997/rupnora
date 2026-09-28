<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateWithdrawal extends Model
{
    public const STATUSES = ['requested', 'approved', 'processing', 'paid', 'rejected', 'failed'];

    protected $fillable = [
        'affiliate_id', 'payout_account_id', 'request_reference', 'idempotency_key',
        'transfer_reference', 'status', 'gross_amount', 'deduction_amount', 'net_amount',
        'payout_snapshot', 'reconciliation_status', 'admin_notes', 'failure_reason',
        'approved_by', 'processed_by', 'requested_at', 'approved_at', 'processing_at',
        'paid_at', 'rejected_at', 'failed_at',
    ];

    protected $hidden = ['payout_snapshot'];

    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
            'deduction_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'payout_snapshot' => 'encrypted:array',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'processing_at' => 'datetime',
            'paid_at' => 'datetime',
            'rejected_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_id');
    }

    public function payoutAccount(): BelongsTo
    {
        return $this->belongsTo(AffiliatePayoutAccount::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}

<?php

namespace App\Models;

use LogicException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateLedgerEntry extends Model
{
    protected $fillable = [
        'affiliate_id', 'commission_id', 'withdrawal_id', 'reference', 'type',
        'pending_delta', 'available_delta', 'reserved_delta', 'paid_delta',
        'reversed_delta', 'gross_amount', 'deduction_amount', 'net_amount',
        'description', 'metadata', 'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'pending_delta' => 'decimal:2',
            'available_delta' => 'decimal:2',
            'reserved_delta' => 'decimal:2',
            'paid_delta' => 'decimal:2',
            'reversed_delta' => 'decimal:2',
            'gross_amount' => 'decimal:2',
            'deduction_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Affiliate ledger entries are immutable.'));
        static::deleting(fn () => throw new LogicException('Affiliate ledger entries are immutable.'));
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_id');
    }

    public function commission(): BelongsTo
    {
        return $this->belongsTo(AffiliateCommission::class);
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(AffiliateWithdrawal::class);
    }
}

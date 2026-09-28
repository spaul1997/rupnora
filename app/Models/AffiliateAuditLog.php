<?php

namespace App\Models;

use LogicException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'affiliate_id', 'withdrawal_id', 'actor_id', 'event', 'status_from',
        'status_to', 'metadata', 'created_at',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'created_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Affiliate audit logs are immutable.'));
        static::deleting(fn () => throw new LogicException('Affiliate audit logs are immutable.'));
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_id');
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(AffiliateWithdrawal::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}

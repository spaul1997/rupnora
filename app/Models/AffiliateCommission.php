<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateCommission extends Model
{
    public const STATUSES = ['pending', 'available', 'partially_reversed', 'reversed'];

    protected $fillable = [
        'affiliate_id', 'order_id', 'order_item_id', 'status', 'eligible_amount',
        'commission_rate', 'gross_amount', 'reversed_amount', 'rule_type', 'rule_id',
        'idempotency_key', 'available_at', 'released_at', 'reversed_at',
    ];

    protected function casts(): array
    {
        return [
            'eligible_amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'gross_amount' => 'decimal:2',
            'reversed_amount' => 'decimal:2',
            'available_at' => 'datetime',
            'released_at' => 'datetime',
            'reversed_at' => 'datetime',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AffiliateCommissionRule::class, 'rule_id')->withTrashed();
    }
}

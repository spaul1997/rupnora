<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateReferralClick extends Model
{
    protected $fillable = [
        'affiliate_id', 'user_id', 'product_id', 'attributed_order_id', 'referral_code',
        'session_hash', 'visitor_hash', 'landing_url', 'referrer_url', 'is_valid',
        'is_suspicious', 'suspicious_reason', 'clicked_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_valid' => 'boolean',
            'is_suspicious' => 'boolean',
            'clicked_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'attributed_order_id');
    }
}

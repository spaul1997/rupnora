<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending', 'confirmed', 'processing', 'packed', 'shipped',
        'out_for_delivery', 'delivered', 'cancelled',
        'return_requested', 'returned', 'refunded',
    ];

    public const PAYMENT_STATUSES = ['pending', 'paid', 'failed', 'refunded', 'partial_refund', 'chargeback', 'cod'];

    protected $fillable = [
        'order_number', 'user_id', 'customer_name', 'customer_email', 'customer_phone',
        'affiliate_id', 'affiliate_referral_click_id', 'affiliate_coupon_id',
        'affiliate_attribution_source', 'affiliate_referral_code', 'affiliate_rule_snapshot',
        'affiliate_flagged', 'affiliate_flag_reason', 'affiliate_attributed_at',
        'status', 'payment_status', 'payment_method', 'transaction_id', 'payment_gateway', 'paid_at',
        'subtotal', 'discount_amount', 'coupon_code', 'coupon_discount', 'shipping_charge', 'gst_amount',
        'gift_wrap', 'gift_wrap_charge', 'gift_message_category', 'gift_message', 'gift_to', 'gift_from',
        'grand_total', 'paid_amount', 'refund_amount',
        'shipping_address', 'billing_address',
        'courier_name', 'tracking_number', 'tracking_url', 'estimated_delivery', 'delivered_at', 'stock_reserved',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'coupon_discount' => 'decimal:2',
            'shipping_charge' => 'decimal:2',
            'gst_amount' => 'decimal:2',
            'gift_wrap' => 'boolean',
            'gift_wrap_charge' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'paid_at' => 'datetime',
            'estimated_delivery' => 'date',
            'delivered_at' => 'datetime',
            'stock_reserved' => 'boolean',
            'affiliate_rule_snapshot' => 'array',
            'affiliate_flagged' => 'boolean',
            'affiliate_attributed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_id');
    }

    public function affiliateReferralClick(): BelongsTo
    {
        return $this->belongsTo(AffiliateReferralClick::class);
    }

    public function affiliateCoupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'affiliate_coupon_id');
    }

    public function affiliateCommissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}

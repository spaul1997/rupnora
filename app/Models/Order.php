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

    public const PAYMENT_STATUSES = ['pending', 'paid', 'failed', 'refunded', 'partial_refund', 'cod'];

    protected $fillable = [
        'order_number', 'user_id', 'customer_name', 'customer_email', 'customer_phone',
        'status', 'payment_status', 'payment_method', 'transaction_id', 'payment_gateway', 'paid_at',
        'subtotal', 'discount_amount', 'coupon_code', 'coupon_discount', 'shipping_charge', 'gst_amount',
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
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'paid_at' => 'datetime',
            'estimated_delivery' => 'date',
            'delivered_at' => 'datetime',
            'stock_reserved' => 'boolean',
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

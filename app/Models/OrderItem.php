<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_variant_id',
        'product_name', 'sku', 'metal', 'purity', 'size',
        'quantity', 'price', 'total',
        'affiliate_eligible_amount', 'affiliate_commission_rate',
        'affiliate_commission_rule_type', 'affiliate_commission_rule_id',
        'affiliate_commission_amount', 'returned_quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price' => 'decimal:2',
            'total' => 'decimal:2',
            'affiliate_eligible_amount' => 'decimal:2',
            'affiliate_commission_rate' => 'decimal:2',
            'affiliate_commission_amount' => 'decimal:2',
            'returned_quantity' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function affiliateCommissionRule(): BelongsTo
    {
        return $this->belongsTo(AffiliateCommissionRule::class)->withTrashed();
    }

    public function affiliateCommission(): HasOne
    {
        return $this->hasOne(AffiliateCommission::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPriceHistory extends Model
{
    public const TRACKED_FIELDS = [
        'mrp',
        'selling_price',
        'offer_price',
        'offer_expiry_date',
        'discount_type',
        'discount_value',
        'discount_expiry_date',
        'making_charge',
        'gst_percentage',
        'final_price',
    ];

    protected $fillable = [
        'product_id', 'changed_by', 'source', 'change_type', 'changed_fields', 'note',
        'mrp', 'selling_price', 'offer_price', 'offer_expiry_date',
        'discount_type', 'discount_value', 'discount_expiry_date',
        'making_charge', 'gst_percentage', 'previous_final_price', 'final_price', 'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'changed_fields' => 'array',
            'mrp' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'offer_price' => 'decimal:2',
            'offer_expiry_date' => 'date',
            'discount_value' => 'decimal:2',
            'discount_expiry_date' => 'date',
            'making_charge' => 'decimal:2',
            'gst_percentage' => 'decimal:2',
            'previous_final_price' => 'decimal:2',
            'final_price' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

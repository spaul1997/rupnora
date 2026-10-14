<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPurchaseHistory extends Model
{
    protected $fillable = [
        'product_id',
        'purchase_date',
        'vendor',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

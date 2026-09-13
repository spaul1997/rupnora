<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'sku', 'barcode', 'category_id', 'collection', 'brand',
        'short_description', 'description',
        'jewellery_type', 'metal_type', 'metal_colour', 'purity',
        'gross_weight', 'net_weight', 'metal_weight',
        'has_diamond', 'diamond_carat', 'diamond_colour', 'diamond_clarity', 'diamond_cut', 'diamond_shape', 'diamond_count',
        'has_gemstone', 'gemstone_type', 'gemstone_weight', 'gemstone_colour',
        'mrp', 'selling_price', 'offer_price', 'discount_type', 'discount_value', 'making_charge', 'gst_percentage', 'final_price',
        'stock_quantity', 'minimum_stock', 'stock_status',
        'is_active', 'is_featured', 'is_new_arrival', 'is_best_seller', 'is_trending', 'is_on_sale',
        'meta_title', 'meta_description', 'meta_keywords',
    ];

    protected function casts(): array
    {
        return [
            'gross_weight' => 'decimal:3',
            'net_weight' => 'decimal:3',
            'metal_weight' => 'decimal:3',
            'has_diamond' => 'boolean',
            'diamond_carat' => 'decimal:3',
            'diamond_count' => 'integer',
            'has_gemstone' => 'boolean',
            'gemstone_weight' => 'decimal:3',
            'mrp' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'offer_price' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'making_charge' => 'decimal:2',
            'gst_percentage' => 'decimal:2',
            'final_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'minimum_stock' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_new_arrival' => 'boolean',
            'is_best_seller' => 'boolean',
            'is_trending' => 'boolean',
            'is_on_sale' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            $product->final_price = $product->computeFinalPrice();
            $product->stock_status = $product->computeStockStatus();
        });
    }

    public function computeFinalPrice(): float
    {
        $base = (float) ($this->offer_price ?? $this->selling_price);

        if ($this->discount_type === 'percentage' && $this->discount_value) {
            $base -= $base * ((float) $this->discount_value / 100);
        } elseif ($this->discount_type === 'fixed' && $this->discount_value) {
            $base -= (float) $this->discount_value;
        }

        $base += (float) ($this->making_charge ?? 0);
        $base += $base * ((float) ($this->gst_percentage ?? 0) / 100);

        return round(max($base, 0), 2);
    }

    public function computeStockStatus(): string
    {
        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        }

        if ($this->stock_quantity <= $this->minimum_stock) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasMany
    {
        return $this->hasMany(ProductImage::class)->where('is_primary', true);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupon_products');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWhereCollectionSlug($query, string $slug)
    {
        return $query->where(function ($query) use ($slug) {
            $query->where('collection', $slug)
                ->orWhere('collection', 'like', '%'.json_encode($slug).'%');
        });
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'minimum_stock');
    }

    public function collectionSlugs(): array
    {
        return self::normalizeCollectionSlugs($this->collection);
    }

    public static function normalizeCollectionSlugs(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value));
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return array_values(array_filter($decoded));
        }

        return [$value];
    }
}

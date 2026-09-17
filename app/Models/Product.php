<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    public const OCCASIONS = [
        'everyday' => 'Everyday',
        'office' => 'Office',
        'casual' => 'Casual',
        'party' => 'Party',
        'festive' => 'Festive',
        'wedding' => 'Wedding',
        'engagement' => 'Engagement',
        'anniversary' => 'Anniversary',
        'birthday' => 'Birthday',
        'gift' => 'Gift',
    ];

    public const GENDERS = [
        'Women' => 'Women',
        'Men' => 'Men',
        'Unisex' => 'Unisex',
        'Kids' => 'Kids',
    ];

    protected $fillable = [
        'name', 'slug', 'sku', 'barcode', 'category_id', 'collection', 'brand',
        'short_description', 'description',
        'jewellery_type', 'metal_type', 'finish_plating', 'metal_colour', 'purity',
        'gross_weight', 'net_weight', 'metal_weight',
        'has_diamond', 'diamond_carat', 'diamond_colour', 'diamond_clarity', 'diamond_cut', 'diamond_shape', 'diamond_count',
        'has_gemstone', 'gemstone_type', 'gemstone_weight', 'gemstone_colour',
        'occasion', 'gender', 'is_adjustable', 'is_water_resistant', 'is_return_available', 'is_refund_available',
        'mrp', 'selling_price', 'offer_price', 'offer_expiry_date', 'discount_type', 'discount_value', 'discount_expiry_date', 'making_charge', 'gst_percentage', 'final_price',
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
            'is_adjustable' => 'boolean',
            'is_water_resistant' => 'boolean',
            'is_return_available' => 'boolean',
            'is_refund_available' => 'boolean',
            'mrp' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'offer_price' => 'decimal:2',
            'offer_expiry_date' => 'date',
            'discount_value' => 'decimal:2',
            'discount_expiry_date' => 'date',
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

    protected function finalPrice(): Attribute
    {
        // Expiry changes the current price even when the product has not been saved again.
        return Attribute::get(fn () => $this->computeFinalPrice());
    }

    public function offerHasExpired(): bool
    {
        return $this->offer_expiry_date?->lt(today()) ?? false;
    }

    public function discountHasExpired(): bool
    {
        return $this->discount_expiry_date?->lt(today()) ?? false;
    }

    public function hasActiveOffer(): bool
    {
        return $this->offer_price !== null && ! $this->offerHasExpired();
    }

    public function hasActiveDiscount(): bool
    {
        return in_array($this->discount_type, ['percentage', 'fixed'])
            && (float) $this->discount_value > 0
            && ! $this->discountHasExpired();
    }

    public function computeFinalPrice(): float
    {
        $base = (float) ($this->hasActiveOffer() ? $this->offer_price : $this->selling_price);

        if ($this->hasActiveDiscount() && $this->discount_type === 'percentage') {
            $base -= $base * ((float) $this->discount_value / 100);
        } elseif ($this->hasActiveDiscount() && $this->discount_type === 'fixed') {
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

    public function scopePricedAtMost(Builder $query, float $maximum): Builder
    {
        if ($maximum < 0) {
            return $query->whereRaw('1 = 0');
        }

        // Collection price limits must use the same live price as the storefront.
        $column = fn (string $name) => $this->qualifyColumn($name);
        $offer = 'CASE WHEN '.$column('offer_price').' IS NOT NULL AND ('.$column('offer_expiry_date').' IS NULL OR '.$column('offer_expiry_date').' >= ?) THEN '.$column('offer_price').' ELSE '.$column('selling_price').' END';
        $discount = 'CASE WHEN '.$column('discount_expiry_date').' IS NULL OR '.$column('discount_expiry_date').' >= ? THEN CASE '.$column('discount_type')." WHEN 'percentage' THEN (".$offer.') * COALESCE('.$column('discount_value').", 0) / 100.0 WHEN 'fixed' THEN COALESCE(".$column('discount_value').', 0) ELSE 0 END ELSE 0 END';
        $price = '(('.$offer.') - ('.$discount.') + COALESCE('.$column('making_charge').', 0)) * (1 + COALESCE('.$column('gst_percentage').', 0) / 100.0)';
        $date = today()->toDateString();

        // Negative calculated prices are clamped to zero; both satisfy a nonnegative limit.
        return $query->whereRaw('ROUND('.$price.', 2) <= CAST(? AS DECIMAL(18, 2))', [$date, $date, $date, $maximum]);
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

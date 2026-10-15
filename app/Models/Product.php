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

    public ?string $priceChangeNote = null;

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
        'occasion', 'gender', 'is_adjustable', 'is_water_resistant', 'is_return_available', 'is_refund_available', 'has_warranty', 'warranty_months',
        'mrp', 'selling_price', 'offer_price', 'offer_expiry_date', 'discount_type', 'discount_value', 'discount_start_date', 'discount_expiry_date', 'discount_offer_id', 'making_charge', 'gst_percentage', 'final_price',
        'stock_quantity', 'minimum_stock', 'stock_status',
        'is_active', 'is_featured', 'is_new_arrival', 'is_best_seller', 'is_trending', 'is_on_sale',
        'meta_title', 'meta_description', 'meta_keywords',
    ];

    protected $hidden = [
        'purchaseHistories',
        'purchase_histories',
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
            'has_warranty' => 'boolean',
            'warranty_months' => 'integer',
            'mrp' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'offer_price' => 'decimal:2',
            'offer_expiry_date' => 'date',
            'discount_value' => 'decimal:2',
            'discount_start_date' => 'date',
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
        static::creating(function (Product $product) {
            if (! array_key_exists('is_new_arrival', $product->getAttributes())) {
                $product->is_new_arrival = true;
            }
        });

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

    public function discountHasNotStarted(): bool
    {
        return $this->discount_start_date?->gt(today()) ?? false;
    }

    public function hasActiveOffer(): bool
    {
        return $this->offer_price !== null && ! $this->offerHasExpired();
    }

    public function hasActiveDiscount(): bool
    {
        return in_array($this->discount_type, ['percentage', 'fixed'])
            && (float) $this->discount_value > 0
            && ! $this->discountHasNotStarted()
            && ! $this->discountHasExpired();
    }

    public function computeFinalPrice(): float
    {
        $base = (float) ($this->hasActiveOffer() ? $this->offer_price : $this->selling_price);

        if ($this->hasActiveDiscount() && $this->discount_type === 'percentage') {
            $percentageDiscount = floor($base * ((float) $this->discount_value / 100));
            $base -= $percentageDiscount;
        } elseif ($this->hasActiveDiscount() && $this->discount_type === 'fixed') {
            $base -= (float) $this->discount_value;
        }

        return $this->priceWithCharges($base);
    }

    /**
     * The price customers would pay without the discount offer, for showing against the final price.
     */
    public function priceBeforeDiscount(): float
    {
        return $this->priceWithCharges((float) ($this->hasActiveOffer() ? $this->offer_price : $this->selling_price));
    }

    private function priceWithCharges(float $base): float
    {
        $base += (float) ($this->making_charge ?? 0);
        $base += $base * ((float) ($this->gst_percentage ?? 0) / 100);

        return round(max($base, 0), 2);
    }

    public static function includedGstAmount(float $inclusiveAmount, float $percentage): float
    {
        $inclusiveAmount = max(0, $inclusiveAmount);
        $percentage = max(0, $percentage);

        if ($inclusiveAmount === 0.0 || $percentage === 0.0) {
            return 0;
        }

        return round($inclusiveAmount * $percentage / (100 + $percentage), 2);
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

    /**
     * @return array{prefix: string, code: string, serial: string}|null
     */
    public function skuSegments(): ?array
    {
        if (! preg_match('/^([A-Z0-9]+-[A-Z0-9]+)(?:-([A-Z0-9]+))?-(\d+)$/i', $this->sku, $matches)) {
            return null;
        }

        return [
            'prefix' => $matches[1],
            'code' => $matches[2] ?? '',
            'serial' => $matches[3],
        ];
    }

    public function skuWithCode(?string $code): ?string
    {
        $segments = $this->skuSegments();

        if ($segments === null) {
            return null;
        }

        $code = strtoupper(trim((string) $code));

        return $segments['prefix'].'-'.($code !== '' ? $code.'-' : '').$segments['serial'];
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

    public function priceHistories(): HasMany
    {
        return $this->hasMany(ProductPriceHistory::class)->latest('recorded_at')->latest('id');
    }

    public function purchaseHistories(): HasMany
    {
        return $this->hasMany(ProductPurchaseHistory::class)->latest('purchase_date')->latest('id');
    }

    public function visitorLogs(): HasMany
    {
        return $this->hasMany(VisitorLog::class);
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupon_products');
    }

    public function discountOffer(): BelongsTo
    {
        return $this->belongsTo(DiscountOffer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Products the storefront badges as "Sale": linked to a discount offer whose discount is live today.
     */
    public function scopeOnSale(Builder $query): Builder
    {
        $today = today()->toDateString();

        return $query->whereNotNull('discount_offer_id')
            ->whereIn('discount_type', ['percentage', 'fixed'])
            ->where('discount_value', '>', 0)
            ->where(fn (Builder $q) => $q->whereNull('discount_start_date')->orWhereDate('discount_start_date', '<=', $today))
            ->where(fn (Builder $q) => $q->whereNull('discount_expiry_date')->orWhereDate('discount_expiry_date', '>=', $today));
    }

    public function scopePricedAtMost(Builder $query, float $maximum): Builder
    {
        if ($maximum < 0) {
            return $query->whereRaw('1 = 0');
        }

        // Collection price limits must use the same live price as the storefront.
        $column = fn (string $name) => $this->qualifyColumn($name);
        $offer = 'CASE WHEN '.$column('offer_price').' IS NOT NULL AND ('.$column('offer_expiry_date').' IS NULL OR '.$column('offer_expiry_date').' >= ?) THEN '.$column('offer_price').' ELSE '.$column('selling_price').' END';
        $percentageDiscount = '('.$offer.') * COALESCE('.$column('discount_value').', 0) / 100.0';
        $percentageDiscount = $query->getConnection()->getDriverName() === 'sqlite'
            ? 'CAST('.$percentageDiscount.' AS INTEGER)'
            : 'FLOOR('.$percentageDiscount.')';
        $discount = 'CASE WHEN ('.$column('discount_start_date').' IS NULL OR '.$column('discount_start_date').' <= ?) AND ('.$column('discount_expiry_date').' IS NULL OR '.$column('discount_expiry_date').' >= ?) THEN CASE '.$column('discount_type')." WHEN 'percentage' THEN ".$percentageDiscount.' WHEN \'fixed\' THEN COALESCE('.$column('discount_value').', 0) ELSE 0 END ELSE 0 END';
        $price = '(('.$offer.') - ('.$discount.') + COALESCE('.$column('making_charge').', 0)) * (1 + COALESCE('.$column('gst_percentage').', 0) / 100.0)';
        $date = today()->toDateString();

        // Negative calculated prices are clamped to zero; both satisfy a nonnegative limit.
        return $query->whereRaw('ROUND('.$price.', 2) <= CAST(? AS DECIMAL(18, 2))', [$date, $date, $date, $date, $maximum]);
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class DiscountOffer extends Model
{
    protected $fillable = [
        'reason', 'scope_type', 'start_date', 'end_date', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_discount_offer')
            ->withPivot(['discount_type', 'discount_value'])
            ->withTimestamps();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'discount_offer_products')
            ->withPivot(['is_excluded', 'discount_type', 'discount_value'])
            ->withTimestamps();
    }

    public function includedProducts(): BelongsToMany
    {
        return $this->products()->wherePivot('is_excluded', false);
    }

    public function excludedProducts(): BelongsToMany
    {
        return $this->products()->wherePivot('is_excluded', true);
    }

    public function assignedProducts(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActiveNow(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today());
    }

    public function status(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->start_date->gt(today())) {
            return 'upcoming';
        }

        if ($this->end_date->lt(today())) {
            return 'expired';
        }

        return 'active';
    }

    public function isActiveNow(): bool
    {
        return $this->is_active
            && $this->start_date->lte(today())
            && $this->end_date->gte(today());
    }

    public function targetProductIds(): Collection
    {
        if ($this->scope_type === 'product') {
            return $this->includedProducts()->pluck('products.id');
        }

        $categoryIds = $this->categories()->pluck('categories.id');

        if ($categoryIds->isEmpty()) {
            return collect();
        }

        $excludedIds = $this->excludedProducts()->pluck('products.id');

        return Product::query()
            ->where(function (Builder $query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds)
                    ->orWhereHas('category', fn (Builder $query) => $query->whereIn('parent_id', $categoryIds));
            })
            ->when($excludedIds->isNotEmpty(), fn (Builder $query) => $query->whereNotIn('id', $excludedIds))
            ->pluck('id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JewelleryCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'logo', 'banner', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'collection', 'slug');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $fillable = [
        'type', 'is_default', 'name', 'phone', 'line1', 'line2', 'landmark',
        'city', 'state', 'pincode', 'country',
    ];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toStorefront(): array
    {
        return [
            'id' => $this->id,
            'default' => $this->is_default,
            ...$this->only(['type', 'name', 'phone', 'city', 'state', 'pincode', 'country']),
            'line1' => $this->line1,
            'line2' => $this->line2 ?? '',
            'landmark' => $this->landmark ?? '',
        ];
    }
}

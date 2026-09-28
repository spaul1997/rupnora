<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliatePayoutAccount extends Model
{
    protected $fillable = [
        'affiliate_id', 'payout_method', 'account_holder_name', 'bank_name',
        'account_number', 'ifsc', 'upi_id', 'account_last_four', 'is_verified',
    ];

    protected $hidden = ['account_number', 'ifsc', 'upi_id'];

    protected function casts(): array
    {
        return [
            'account_holder_name' => 'encrypted',
            'bank_name' => 'encrypted',
            'account_number' => 'encrypted',
            'ifsc' => 'encrypted',
            'upi_id' => 'encrypted',
            'is_verified' => 'boolean',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_id');
    }
}

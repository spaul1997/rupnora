<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class AffiliateProfile extends Model
{
    public const STATUSES = ['pending', 'approved', 'rejected', 'suspended'];

    protected $fillable = [
        'user_id', 'status', 'referral_code', 'application_message', 'website_url',
        'social_url', 'audience_summary', 'commission_rate', 'admin_notes',
        'approved_by', 'applied_at', 'approved_at', 'rejected_at', 'suspended_at',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'applied_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(AffiliateReferralClick::class, 'affiliate_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'affiliate_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class, 'affiliate_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(AffiliateLedgerEntry::class, 'affiliate_id');
    }

    public function payoutAccount(): HasOne
    {
        return $this->hasOne(AffiliatePayoutAccount::class, 'affiliate_id');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(AffiliateWithdrawal::class, 'affiliate_id');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public static function generateReferralCode(User $user): string
    {
        do {
            $code = 'RPN'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT).Str::upper(Str::random(5));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    /** @return array{pending: float, available: float, reserved: float, paid: float, reversed: float} */
    public function wallet(): array
    {
        $totals = $this->ledgerEntries()->selectRaw(
            'COALESCE(SUM(pending_delta), 0) pending, COALESCE(SUM(available_delta), 0) available, '.
            'COALESCE(SUM(reserved_delta), 0) reserved, COALESCE(SUM(paid_delta), 0) paid, '.
            'COALESCE(SUM(reversed_delta), 0) reversed'
        )->first();

        return [
            'pending' => round((float) $totals->pending, 2),
            'available' => round((float) $totals->available, 2),
            'reserved' => round((float) $totals->reserved, 2),
            'paid' => round((float) $totals->paid, 2),
            'reversed' => round((float) $totals->reversed, 2),
        ];
    }
}

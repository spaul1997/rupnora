<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Influencer extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'reviewing' => 'Reviewing',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'suspended' => 'Suspended',
    ];

    public const PLATFORMS = [
        'instagram' => 'Instagram',
        'youtube' => 'YouTube',
        'facebook' => 'Facebook',
        'tiktok' => 'TikTok',
        'pinterest' => 'Pinterest',
        'blog' => 'Blog / Website',
        'other' => 'Other',
    ];

    protected $fillable = [
        'reference_no',
        'user_id',
        'full_name',
        'email',
        'phone',
        'location',
        'primary_platform',
        'social_handle',
        'profile_url',
        'followers_count',
        'content_niche',
        'portfolio_url',
        'message',
        'profile_image_path',
        'status',
        'commission_rate',
        'coupon_code',
        'admin_notes',
        'is_active',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'followers_count' => 'integer',
            'commission_rate' => 'decimal:2',
            'is_active' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('status', 'approved');
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        return $this->profile_image_path
            ? Storage::disk('public')->url($this->profile_image_path)
            : null;
    }

    public static function generateReferenceNumber(): string
    {
        do {
            $reference = 'RPN-INF-'.now()->format('Y').'-'.Str::upper(Str::random(8));
        } while (static::where('reference_no', $reference)->exists());

        return $reference;
    }
}

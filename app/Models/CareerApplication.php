<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CareerApplication extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'reviewing' => 'Reviewing',
        'shortlisted' => 'Shortlisted',
        'rejected' => 'Rejected',
        'hired' => 'Hired',
    ];

    public const AREAS = [
        'jewellery-curation' => 'Jewellery & Curation',
        'brand-content' => 'Brand & Content',
        'customer-experience' => 'Customer Experience',
        'ecommerce-operations' => 'Ecommerce & Operations',
        'technology' => 'Technology',
        'other' => 'Other',
    ];

    protected $fillable = [
        'reference_no',
        'user_id',
        'full_name',
        'email',
        'phone',
        'location',
        'area_of_interest',
        'current_role',
        'experience_years',
        'linkedin_url',
        'portfolio_url',
        'message',
        'cv_path',
        'cv_original_name',
        'cv_mime_type',
        'cv_size',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
            'cv_size' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateReferenceNumber(): string
    {
        do {
            $reference = 'RPN-CAR-'.now()->format('Y').'-'.Str::upper(Str::random(8));
        } while (static::where('reference_no', $reference)->exists());

        return $reference;
    }
}

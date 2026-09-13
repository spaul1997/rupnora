<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    protected $fillable = [
        'company_name', 'support_email', 'sales_email', 'phone', 'whatsapp',
        'address', 'business_hours', 'google_map_url',
        'facebook', 'instagram', 'linkedin', 'youtube',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }
}

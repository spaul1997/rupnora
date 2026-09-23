<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    protected $fillable = [
        'company_name', 'support_email', 'sales_email', 'phone', 'whatsapp',
        'address', 'business_hours', 'google_map_url',
        'facebook', 'instagram', 'linkedin', 'youtube',
        'express_delivery_charge', 'cod_order_limit', 'free_shipping_threshold',
    ];

    protected function casts(): array
    {
        return [
            'express_delivery_charge' => 'decimal:2',
            'cod_order_limit' => 'decimal:2',
            'free_shipping_threshold' => 'decimal:2',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }
}

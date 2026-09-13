<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('support_email')->nullable();
            $table->string('sales_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('address')->nullable();
            $table->string('business_hours')->nullable();
            $table->string('google_map_url')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('youtube')->nullable();
            $table->timestamps();
        });

        DB::table('website_settings')->insert([
            'company_name' => 'Aurelle Jewellery',
            'support_email' => 'care@aurellejewellery.com',
            'sales_email' => 'sales@aurellejewellery.com',
            'phone' => '+91 80 4567 8900',
            'whatsapp' => '+91 98765 00000',
            'address' => '42 MG Road, Indiranagar, Bengaluru, Karnataka 560038',
            'business_hours' => 'Mon - Sat: 10:30 AM - 8:00 PM · Sun: 11:00 AM - 6:00 PM',
            'google_map_url' => null,
            'facebook' => null,
            'instagram' => null,
            'linkedin' => null,
            'youtube' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('website_settings');
    }
};

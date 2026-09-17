<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->date('offer_expiry_date')->nullable()->after('offer_price');
            $table->date('discount_expiry_date')->nullable()->after('discount_value');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['offer_expiry_date', 'discount_expiry_date']);
        });
    }
};

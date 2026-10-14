<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('category_discount_offer', function (Blueprint $table) {
            $table->string('discount_type', 20)->nullable()->after('category_id');
            $table->decimal('discount_value', 12, 2)->nullable()->after('discount_type');
        });

        Schema::table('discount_offer_products', function (Blueprint $table) {
            $table->string('discount_type', 20)->nullable()->after('is_excluded');
            $table->decimal('discount_value', 12, 2)->nullable()->after('discount_type');
        });

        DB::table('discount_offers')->orderBy('id')->get()->each(function ($offer) {
            DB::table('category_discount_offer')
                ->where('discount_offer_id', $offer->id)
                ->update([
                    'discount_type' => $offer->discount_type,
                    'discount_value' => $offer->discount_value,
                ]);

            DB::table('discount_offer_products')
                ->where('discount_offer_id', $offer->id)
                ->where('is_excluded', false)
                ->update([
                    'discount_type' => $offer->discount_type,
                    'discount_value' => $offer->discount_value,
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('discount_offer_products', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });

        Schema::table('category_discount_offer', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });
    }
};

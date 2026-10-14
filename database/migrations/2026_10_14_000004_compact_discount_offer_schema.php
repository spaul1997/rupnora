<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('category_discount_offer', function (Blueprint $table) {
            $table->index(['category_id', 'discount_offer_id'], 'category_offer_lookup');
        });

        Schema::table('discount_offer_products', function (Blueprint $table) {
            $table->index(['product_id', 'is_excluded', 'discount_offer_id'], 'product_offer_lookup');
        });

        // Discount values live on target rows, where category and product
        // campaigns can each carry different values.
        Schema::table('discount_offers', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });
    }

    public function down(): void
    {
        Schema::table('discount_offers', function (Blueprint $table) {
            $table->string('discount_type', 20)->nullable()->after('scope_type');
            $table->decimal('discount_value', 12, 2)->nullable()->after('discount_type');
        });

        Schema::table('discount_offer_products', function (Blueprint $table) {
            $table->dropIndex('product_offer_lookup');
        });

        Schema::table('category_discount_offer', function (Blueprint $table) {
            $table->dropIndex('category_offer_lookup');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_offers', function (Blueprint $table) {
            $table->id();
            $table->string('reason');
            $table->enum('scope_type', ['category', 'product']);
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 12, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'start_date', 'end_date']);
        });

        Schema::create('category_discount_offer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_offer_id')->constrained('discount_offers')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['discount_offer_id', 'category_id']);
        });

        Schema::create('discount_offer_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_offer_id')->constrained('discount_offers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->boolean('is_excluded')->default(false);
            $table->timestamps();

            $table->unique(['discount_offer_id', 'product_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('discount_offer_id')
                ->nullable()
                ->after('discount_expiry_date')
                ->constrained('discount_offers')
                ->nullOnDelete();
            $table->date('discount_start_date')->nullable()->after('discount_value');
        });

        Schema::table('product_price_histories', function (Blueprint $table) {
            $table->date('discount_start_date')->nullable()->after('discount_value');
            $table->foreignId('discount_offer_id')
                ->nullable()
                ->after('discount_expiry_date')
                ->constrained('discount_offers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_price_histories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_offer_id');
            $table->dropColumn('discount_start_date');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_offer_id');
            $table->dropColumn('discount_start_date');
        });

        Schema::dropIfExists('discount_offer_products');
        Schema::dropIfExists('category_discount_offer');
        Schema::dropIfExists('discount_offers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Basic information
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('collection')->nullable();
            $table->string('brand')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            // Jewellery information
            $table->string('jewellery_type');
            $table->string('metal_type');
            $table->string('metal_colour')->nullable();
            $table->string('purity')->nullable();
            $table->decimal('gross_weight', 10, 3)->nullable();
            $table->decimal('net_weight', 10, 3)->nullable();
            $table->decimal('metal_weight', 10, 3)->nullable();

            // Diamond details
            $table->boolean('has_diamond')->default(false);
            $table->decimal('diamond_carat', 10, 3)->nullable();
            $table->string('diamond_colour')->nullable();
            $table->string('diamond_clarity')->nullable();
            $table->string('diamond_cut')->nullable();
            $table->string('diamond_shape')->nullable();
            $table->unsignedInteger('diamond_count')->nullable();

            // Gemstone details
            $table->boolean('has_gemstone')->default(false);
            $table->string('gemstone_type')->nullable();
            $table->decimal('gemstone_weight', 10, 3)->nullable();
            $table->string('gemstone_colour')->nullable();

            // Pricing
            $table->decimal('mrp', 12, 2);
            $table->decimal('selling_price', 12, 2);
            $table->decimal('offer_price', 12, 2)->nullable();
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->decimal('making_charge', 12, 2)->default(0);
            $table->decimal('gst_percentage', 5, 2)->default(3.00);
            $table->decimal('final_price', 12, 2);

            // Inventory
            $table->integer('stock_quantity')->default(0);
            $table->unsignedInteger('minimum_stock')->default(5);
            $table->enum('stock_status', ['in_stock', 'low_stock', 'out_of_stock'])->default('in_stock');

            // Flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new_arrival')->default(false);
            $table->boolean('is_best_seller')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->boolean('is_on_sale')->default(false);

            // SEO
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            $table->timestamps();

            $table->index('sku');
            $table->index('category_id');
            $table->index('is_active');
            $table->index('stock_quantity');
            $table->index('jewellery_type');
            $table->index('metal_type');
            $table->index('purity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

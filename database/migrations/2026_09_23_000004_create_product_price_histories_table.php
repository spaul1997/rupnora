<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source', 30)->default('system');
            $table->enum('change_type', ['initial', 'increase', 'decrease', 'unchanged'])->default('initial');
            $table->json('changed_fields')->nullable();
            $table->text('note')->nullable();
            $table->decimal('mrp', 12, 2);
            $table->decimal('selling_price', 12, 2);
            $table->decimal('offer_price', 12, 2)->nullable();
            $table->date('offer_expiry_date')->nullable();
            $table->string('discount_type', 20)->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->date('discount_expiry_date')->nullable();
            $table->decimal('making_charge', 12, 2)->default(0);
            $table->decimal('gst_percentage', 5, 2)->default(0);
            $table->decimal('previous_final_price', 12, 2)->nullable();
            $table->decimal('final_price', 12, 2);
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['product_id', 'recorded_at']);
        });

        $now = now();
        $today = $now->toDateString();

        DB::table('products')->orderBy('id')->chunkById(200, function ($products) use ($now, $today) {
            $rows = [];

            foreach ($products as $product) {
                $offerActive = $product->offer_price !== null
                    && ($product->offer_expiry_date === null || $product->offer_expiry_date >= $today);
                $discountActive = in_array($product->discount_type, ['percentage', 'fixed'], true)
                    && (float) $product->discount_value > 0
                    && ($product->discount_expiry_date === null || $product->discount_expiry_date >= $today);
                $base = (float) ($offerActive ? $product->offer_price : $product->selling_price);

                if ($discountActive && $product->discount_type === 'percentage') {
                    $base -= $base * ((float) $product->discount_value / 100);
                } elseif ($discountActive && $product->discount_type === 'fixed') {
                    $base -= (float) $product->discount_value;
                }

                $base += (float) ($product->making_charge ?? 0);
                $finalPrice = round(max($base + ($base * ((float) ($product->gst_percentage ?? 0) / 100)), 0), 2);

                $rows[] = [
                    'product_id' => $product->id,
                    'changed_by' => null,
                    'source' => 'backfill',
                    'change_type' => 'initial',
                    'changed_fields' => json_encode([]),
                    'note' => 'Initial price captured when price tracking was enabled.',
                    'mrp' => $product->mrp,
                    'selling_price' => $product->selling_price,
                    'offer_price' => $product->offer_price,
                    'offer_expiry_date' => $product->offer_expiry_date,
                    'discount_type' => $product->discount_type,
                    'discount_value' => $product->discount_value,
                    'discount_expiry_date' => $product->discount_expiry_date,
                    'making_charge' => $product->making_charge ?? 0,
                    'gst_percentage' => $product->gst_percentage ?? 0,
                    'previous_final_price' => null,
                    'final_price' => $finalPrice,
                    'recorded_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows !== []) {
                DB::table('product_price_histories')->insert($rows);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_histories');
    }
};

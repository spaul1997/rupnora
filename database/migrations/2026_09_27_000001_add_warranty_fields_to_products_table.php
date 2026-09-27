<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'has_warranty')) {
                $table->boolean('has_warranty')->default(false)->after('is_refund_available');
            }

            if (! Schema::hasColumn('products', 'warranty_months')) {
                $table->unsignedSmallInteger('warranty_months')->nullable()->after('has_warranty');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'warranty_months')) {
                $table->dropColumn('warranty_months');
            }

            if (Schema::hasColumn('products', 'has_warranty')) {
                $table->dropColumn('has_warranty');
            }
        });
    }
};

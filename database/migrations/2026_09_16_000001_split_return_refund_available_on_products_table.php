<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'is_return_available')) {
                $table->boolean('is_return_available')->default(false)->after('is_water_resistant');
            }

            if (! Schema::hasColumn('products', 'is_refund_available')) {
                $table->boolean('is_refund_available')->default(false)->after('is_return_available');
            }
        });

        if (Schema::hasColumn('products', 'is_return_refund_available')) {
            DB::table('products')
                ->where('is_return_refund_available', true)
                ->update([
                    'is_return_available' => true,
                    'is_refund_available' => true,
                ]);

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('is_return_refund_available');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'is_return_refund_available')) {
                $table->boolean('is_return_refund_available')->default(false)->after('is_water_resistant');
            }
        });

        if (Schema::hasColumn('products', 'is_return_available') && Schema::hasColumn('products', 'is_refund_available')) {
            DB::table('products')
                ->where(function ($query) {
                    $query->where('is_return_available', true)
                        ->orWhere('is_refund_available', true);
                })
                ->update(['is_return_refund_available' => true]);
        }

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'is_return_available')) {
                $table->dropColumn('is_return_available');
            }

            if (Schema::hasColumn('products', 'is_refund_available')) {
                $table->dropColumn('is_refund_available');
            }
        });
    }
};

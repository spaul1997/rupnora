<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $exists = Schema::hasColumn('website_settings', 'shipping_per_limit');

        Schema::table('website_settings', function (Blueprint $table) use ($exists) {
            $column = $table->decimal('shipping_per_limit', 5, 2)->default(10);

            if ($exists) {
                $column->change();
            } else {
                $column->after('free_shipping_threshold');
            }
        });
    }

    public function down(): void
    {
        Schema::table('website_settings', function (Blueprint $table) {
            $table->dropColumn('shipping_per_limit');
        });
    }
};

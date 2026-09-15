<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('finish_plating')->nullable()->after('metal_type');
            $table->string('occasion')->nullable()->after('gemstone_colour');
            $table->string('gender')->nullable()->after('occasion');
            $table->boolean('is_adjustable')->default(false)->after('gender');
            $table->boolean('is_water_resistant')->default(false)->after('is_adjustable');

            $table->index('occasion');
            $table->index('gender');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['occasion']);
            $table->dropIndex(['gender']);
            $table->dropColumn([
                'finish_plating',
                'occasion',
                'gender',
                'is_adjustable',
                'is_water_resistant',
            ]);
        });
    }
};

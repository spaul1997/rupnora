<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jewellery_collections', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('description');
            $table->string('banner')->nullable()->after('logo');
        });
    }

    public function down(): void
    {
        Schema::table('jewellery_collections', function (Blueprint $table) {
            $table->dropColumn(['logo', 'banner']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->json('shiprocket_tracking')->nullable()->after('shiprocket_synced_at');
            $table->timestamp('shiprocket_tracked_at')->nullable()->after('shiprocket_tracking');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shiprocket_tracking', 'shiprocket_tracked_at']);
        });
    }
};

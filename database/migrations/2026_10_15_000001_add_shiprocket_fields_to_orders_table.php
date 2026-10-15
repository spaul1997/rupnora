<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shiprocket_order_id')->nullable()->unique()->after('tracking_url');
            $table->string('shiprocket_shipment_id')->nullable()->unique()->after('shiprocket_order_id');
            $table->string('shiprocket_status')->nullable()->after('shiprocket_shipment_id');
            $table->timestamp('shiprocket_synced_at')->nullable()->after('shiprocket_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['shiprocket_order_id']);
            $table->dropUnique(['shiprocket_shipment_id']);
            $table->dropColumn([
                'shiprocket_order_id',
                'shiprocket_shipment_id',
                'shiprocket_status',
                'shiprocket_synced_at',
            ]);
        });
    }
};

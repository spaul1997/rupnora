<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('gift_wrap')->default(false)->after('gst_amount');
            $table->decimal('gift_wrap_charge', 12, 2)->default(0)->after('gift_wrap');
            $table->string('gift_message_category', 30)->nullable()->after('gift_wrap_charge');
            $table->text('gift_message')->nullable()->after('gift_message_category');
            $table->string('gift_to', 100)->nullable()->after('gift_message');
            $table->string('gift_from', 100)->nullable()->after('gift_to');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'gift_wrap',
                'gift_wrap_charge',
                'gift_message_category',
                'gift_message',
                'gift_to',
                'gift_from',
            ]);
        });
    }
};

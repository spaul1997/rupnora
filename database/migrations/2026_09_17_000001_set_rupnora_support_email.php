<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('website_settings')->updateOrInsert(
            ['id' => 1],
            [
                'support_email' => 'info@rupnora.in',
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('website_settings')
            ->where('id', 1)
            ->update([
                'support_email' => 'care@aurellejewellery.com',
                'updated_at' => now(),
            ]);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'gst_percentage')) {
            return;
        }

        match (DB::getDriverName()) {
            'mysql', 'mariadb' => DB::statement('ALTER TABLE products MODIFY gst_percentage DECIMAL(5, 2) NOT NULL DEFAULT 0.00'),
            'pgsql' => DB::statement('ALTER TABLE products ALTER COLUMN gst_percentage SET DEFAULT 0.00'),
            default => null,
        };
    }

    public function down(): void
    {
        if (! Schema::hasColumn('products', 'gst_percentage')) {
            return;
        }

        match (DB::getDriverName()) {
            'mysql', 'mariadb' => DB::statement('ALTER TABLE products MODIFY gst_percentage DECIMAL(5, 2) NOT NULL DEFAULT 3.00'),
            'pgsql' => DB::statement('ALTER TABLE products ALTER COLUMN gst_percentage SET DEFAULT 3.00'),
            default => null,
        };
    }
};

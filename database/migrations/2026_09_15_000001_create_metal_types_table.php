<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metal_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $legacyMetalTypes = DB::table('products')
            ->whereNotNull('metal_type')
            ->distinct()
            ->pluck('metal_type');

        $metalTypes = collect([
            'Gold',
            'White Gold',
            'Rose Gold',
            'Silver',
            'Platinum',
            'Brass',
            'Copper',
            'Alloy',
            'Zinc Alloy',
            'Stainless Steel',
            'German Silver',
            'Sterling Silver / 925 Silver',
            'Iron',
            'Aluminium',
            'Titanium',
            'Nickel Alloy',
            'Pewter',
            'Mixed Metal',
            'Gold-Plated Metal',
            'Silver-Plated Metal',
            'Rose Gold-Plated Metal',
            'Rhodium-Plated Metal',
            'Oxidised Metal',
            'Antique-Finish Metal',
            'Other',
        ])
            ->merge($legacyMetalTypes);

        $now = now();
        $sortOrder = 0;

        foreach ($metalTypes as $name) {
            if (! is_string($name) || trim($name) === '') {
                continue;
            }

            $name = trim($name);

            if (DB::table('metal_types')->where('name', $name)->exists()) {
                continue;
            }

            $slugBase = trim(Str::substr(Str::slug($name), 0, 240), '-');
            $slugBase = $slugBase !== '' ? $slugBase : 'metal-type';
            $slug = $slugBase;
            $suffix = 2;

            while (DB::table('metal_types')->where('slug', $slug)->exists()) {
                $slug = $slugBase.'-'.$suffix;
                $suffix++;
            }

            DB::table('metal_types')->insert([
                'name' => $name,
                'slug' => $slug,
                'sort_order' => $sortOrder,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $sortOrder++;
        }

        foreach ($legacyMetalTypes as $legacyName) {
            if (! is_string($legacyName) || trim($legacyName) === '') {
                continue;
            }

            $canonicalName = DB::table('metal_types')
                ->where('name', trim($legacyName))
                ->value('name');

            if (is_string($canonicalName)) {
                DB::table('products')
                    ->where('metal_type', $legacyName)
                    ->update(['metal_type' => $canonicalName]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('metal_types');
    }
};

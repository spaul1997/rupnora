<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->makeGlobal('jewellery_types');
        $this->makeGlobal('jewellery_collections');
    }

    public function down(): void
    {
        foreach (['jewellery_types', 'jewellery_collections'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'category_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('category_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
                });
            }
        }
    }

    protected function makeGlobal(string $tableName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $this->deleteDuplicateSlugs($tableName);

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (Schema::hasColumn($tableName, 'category_id')) {
                $table->dropUnique($tableName.'_category_id_slug_unique');
                $table->dropIndex($tableName.'_category_id_is_active_sort_order_index');
                $table->dropForeign($tableName.'_category_id_foreign');
                $table->dropColumn('category_id');
            }
        });

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! $this->indexExists($tableName, $tableName.'_slug_unique')) {
                $table->unique('slug');
            }

            if (! $this->indexExists($tableName, $tableName.'_is_active_sort_order_index')) {
                $table->index(['is_active', 'sort_order']);
            }
        });
    }

    protected function deleteDuplicateSlugs(string $tableName): void
    {
        $rows = DB::table($tableName)->orderBy('id')->get(['id', 'slug']);
        $seen = [];
        $duplicateIds = [];

        foreach ($rows as $row) {
            if (isset($seen[$row->slug])) {
                $duplicateIds[] = $row->id;
                continue;
            }

            $seen[$row->slug] = true;
        }

        collect($duplicateIds)
            ->chunk(500)
            ->each(fn ($ids) => DB::table($tableName)->whereIn('id', $ids)->delete());
    }

    protected function indexExists(string $tableName, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    protected array $categoryTree = [
        'Earrings' => ['Stud', 'Hoop', 'Huggie', 'Drop', 'Dangle', 'Jhumka', 'Chandbali', 'Ear Cuff', 'Ear Climber', 'Threader'],
        'Necklaces' => ['Chain Necklace', 'Pendant Necklace', 'Choker', 'Layered Necklace', 'Pearl Necklace', 'Lariat Necklace', 'Beaded Necklace', 'Statement Necklace'],
        'Pendants' => ['Simple Pendant', 'Stone Pendant', 'Initial Pendant', 'Religious Pendant', 'Heart Pendant', 'Locket', 'Pendant with Chain'],
        'Bracelets' => ['Chain Bracelet', 'Charm Bracelet', 'Cuff Bracelet', 'Tennis Bracelet', 'Beaded Bracelet', 'Adjustable Bracelet', 'Friendship Bracelet'],
        'Rings' => ['Adjustable Ring', 'Band Ring', 'Solitaire Ring', 'Cocktail Ring', 'Stackable Ring', 'Statement Ring', 'Couple Ring', 'Midi Ring'],
        'Anklets' => ['Chain Anklet', 'Beaded Anklet', 'Charm Anklet', 'Ghungroo Anklet', 'Single Anklet', 'Anklet Pair'],
        'Jewellery Sets' => ['Earrings Set', 'Necklace Set', 'Pendant Set', 'Bangle Set', 'Complete Jewellery Set', 'Gift Set'],
    ];

    public function up(): void
    {
        foreach ($this->categoryTree as $parentIndex => $children) {
            $parentName = is_int($parentIndex) ? '' : $parentIndex;
            $parentSlug = Str::slug($parentName);

            $parentId = $this->upsertCategory($parentSlug, [
                'name' => $parentName,
                'parent_id' => null,
                'description' => "Explore our {$parentName} collection.",
                'sort_order' => array_search($parentName, array_keys($this->categoryTree), true) ?: 0,
                'is_active' => true,
            ]);

            foreach ($children as $childIndex => $childName) {
                $this->upsertCategory(Str::slug($childName), [
                    'name' => $childName,
                    'parent_id' => $parentId,
                    'description' => "{$childName} styles under {$parentName}.",
                    'sort_order' => $childIndex,
                    'is_active' => true,
                ]);
            }
        }
    }

    protected function upsertCategory(string $slug, array $values): int
    {
        $existingId = DB::table('categories')->where('slug', $slug)->value('id');
        $now = now();

        if ($existingId) {
            DB::table('categories')
                ->where('id', $existingId)
                ->update($values + ['updated_at' => $now]);

            return (int) $existingId;
        }

        return (int) DB::table('categories')->insertGetId($values + [
            'slug' => $slug,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        $childSlugs = collect($this->categoryTree)
            ->flatten()
            ->map(fn (string $name) => Str::slug($name))
            ->all();

        $parentSlugs = collect(array_keys($this->categoryTree))
            ->map(fn (string $name) => Str::slug($name))
            ->all();

        $productCategoryIds = DB::table('products')->pluck('category_id')->all();

        DB::table('categories')
            ->whereIn('slug', $childSlugs)
            ->whereNotIn('id', $productCategoryIds)
            ->delete();

        DB::table('categories')
            ->whereIn('slug', $parentSlugs)
            ->whereNotIn('id', $productCategoryIds)
            ->whereNotIn('id', DB::table('categories')->whereNotNull('parent_id')->pluck('parent_id')->filter()->all())
            ->delete();
    }
};

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\JewelleryCollection;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyWearCollectionFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_pages_hide_only_the_collection_filter(): void
    {
        $this->createCollectionsAndProduct();

        foreach (['daily-wear', 'festive-collection'] as $slug) {
            $this->get(route('collection.show', $slug))->assertOk()
                ->assertViewHas('meta', fn (array $meta) => count($meta) === 1
                    && $meta[0]['collections'] === ['daily-wear', 'festive-collection'])
                ->assertSee('Collection Filter Product')
                ->assertSee('x-model="filters.type"', false)
                ->assertSee('x-model="filters.metal"', false)
                ->assertDontSee('x-model="filters.collection"', false);
        }
    }

    public function test_category_pages_keep_the_collection_filter(): void
    {
        $this->createCollectionsAndProduct();

        $this->get(route('category.show', 'filter-test'))->assertOk()
            ->assertSee('x-model="filters.collection"', false);
    }

    private function createCollectionsAndProduct(): void
    {
        JewelleryCollection::create([
            'name' => 'Daily Wear',
            'slug' => 'daily-wear',
            'is_active' => true,
        ]);
        JewelleryCollection::create([
            'name' => 'Festive Collection',
            'slug' => 'festive-collection',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Filter Test',
            'slug' => 'filter-test',
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Collection Filter Product',
            'slug' => 'collection-filter-product',
            'sku' => 'COLLECTION-FILTER-001',
            'category_id' => $category->id,
            'collection' => json_encode(['daily-wear', 'festive-collection']),
            'jewellery_type' => 'Ring',
            'metal_type' => 'Gold',
            'mrp' => 1200,
            'selling_price' => 1000,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
    }
}

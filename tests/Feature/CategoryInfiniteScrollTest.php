<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\JewelleryCollection;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryInfiniteScrollTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_starts_with_twenty_products_and_loads_the_rest_as_json(): void
    {
        $category = Category::create([
            'name' => 'Infinite Scroll Rings',
            'slug' => 'infinite-scroll-rings',
            'is_active' => true,
        ]);

        foreach (range(1, 25) as $index) {
            Product::create([
                'name' => 'Infinite Ring '.$index,
                'slug' => 'infinite-ring-'.$index,
                'sku' => 'INFINITE-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                'category_id' => $category->id,
                'jewellery_type' => 'Ring',
                'metal_type' => 'Gold',
                'mrp' => 5000 + $index,
                'selling_price' => 4500 + $index,
                'stock_quantity' => 10,
                'is_active' => true,
            ]);
        }

        $this->get(route('category.show', $category->slug))
            ->assertOk()
            ->assertViewHas('products', fn (array $products) => count($products) === 20)
            ->assertViewHas('totalProducts', 25)
            ->assertViewHas('hasMore', true)
            ->assertDontSee('pagination');

        $this->getJson(route('category.show', $category->slug).'?page=2')
            ->assertOk()
            ->assertJsonCount(5, 'meta')
            ->assertJsonPath('has_more', false)
            ->assertJsonPath('next_page', null)
            ->assertJsonPath('loaded_count', 25)
            ->assertJsonPath('total', 25)
            ->assertJson(fn ($json) => $json->whereType('html', 'string')->etc());
    }

    public function test_category_filters_show_non_empty_subcategories_and_collections_only(): void
    {
        $parent = Category::create([
            'name' => 'Filter Jewellery',
            'slug' => 'filter-jewellery',
            'is_active' => true,
        ]);
        $populated = Category::create([
            'name' => 'Diamond Bands',
            'slug' => 'diamond-bands',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);
        Category::create([
            'name' => 'Empty Bands',
            'slug' => 'empty-bands',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);
        JewelleryCollection::create([
            'name' => 'Starlight Collection',
            'slug' => 'starlight-collection',
            'is_active' => true,
        ]);
        Product::create([
            'name' => 'Starlight Diamond Band',
            'slug' => 'starlight-diamond-band',
            'sku' => 'FILTER-001',
            'category_id' => $populated->id,
            'collection' => 'starlight-collection',
            'jewellery_type' => 'Ring',
            'metal_type' => 'White Gold',
            'occasion' => 'wedding',
            'mrp' => 8500,
            'selling_price' => 8000,
            'stock_quantity' => 4,
            'is_active' => true,
        ]);

        $this->get(route('category.show', $parent->slug))
            ->assertOk()
            ->assertSee('Sub-Category')
            ->assertSee('Diamond Bands')
            ->assertDontSee('Empty Bands')
            ->assertSee('Collection')
            ->assertSee('Starlight Collection')
            ->assertDontSee('Parent Category')
            ->assertDontSee('Occasion');
    }
}

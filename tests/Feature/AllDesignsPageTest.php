<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllDesignsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_designs_lists_active_products_across_categories_and_loads_the_remaining_products(): void
    {
        $categories = collect(['Rings', 'Earrings'])->map(fn (string $name) => Category::create([
            'name' => $name,
            'slug' => strtolower($name),
            'is_active' => true,
        ]));
        $productIds = [];

        foreach (range(1, 23) as $index) {
            $productIds[] = Product::create([
                'name' => 'Design '.$index,
                'slug' => 'design-'.$index,
                'sku' => 'DESIGN-'.$index,
                'category_id' => $categories[($index - 1) % 2]->id,
                'jewellery_type' => $index % 2 ? 'Ring' : 'Earrings',
                'metal_type' => 'Gold',
                'mrp' => 1500,
                'selling_price' => 1200,
                'stock_quantity' => $index === 1 ? 0 : 5,
                'is_new_arrival' => false,
                'is_best_seller' => false,
                'is_active' => true,
            ])->id;
        }

        Product::create([
            'name' => 'Hidden Design',
            'slug' => 'hidden-design',
            'sku' => 'HIDDEN-DESIGN',
            'category_id' => $categories[0]->id,
            'mrp' => 1500,
            'selling_price' => 1200,
            'stock_quantity' => 5,
            'is_active' => false,
        ]);

        $firstPage = $this->get(route('all-designs'))
            ->assertOk()
            ->assertSee('All Designs')
            ->assertDontSee('Hidden Design')
            ->assertViewHas('totalProducts', 23)
            ->assertViewHas('hasMore', true)
            ->assertViewHas('products', fn (array $products) => count($products) === 20)
            ->assertViewHas('filterProducts', fn (array $products) => count($products) === 23
                && count(array_unique(array_column($products, 'category'))) === 2
                && count(array_filter($products, fn (array $product) => ! $product['in_stock'])) === 1);

        $secondPage = $this->get(route('all-designs', ['page' => 2]))
            ->assertOk()
            ->assertViewHas('currentPage', 2)
            ->assertViewHas('products', fn (array $products) => count($products) === 3);

        $this->assertEqualsCanonicalizing($productIds, array_column([
            ...$firstPage->viewData('products'),
            ...$secondPage->viewData('products'),
        ], 'id'));

        $this->getJson(route('all-designs', ['page' => 2]))
            ->assertOk()
            ->assertJsonCount(3, 'meta')
            ->assertJsonPath('total', 23)
            ->assertJsonPath('loaded_count', 23)
            ->assertJsonPath('has_more', false)
            ->assertJsonPath('next_page', null);

        $this->get(route('all-designs', ['page' => 3]))->assertNotFound();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Support\StorefrontCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_categories_are_the_four_categories_with_the_most_active_products(): void
    {
        foreach (range(1, 5) as $categoryNumber) {
            $category = Category::create([
                'name' => 'Category '.$categoryNumber,
                'slug' => 'category-'.$categoryNumber,
                'is_active' => true,
            ]);

            foreach (range(1, $categoryNumber) as $productNumber) {
                Product::create([
                    'name' => "Product {$categoryNumber}-{$productNumber}",
                    'slug' => "product-{$categoryNumber}-{$productNumber}",
                    'sku' => "FOOTER-{$categoryNumber}-{$productNumber}",
                    'category_id' => $category->id,
                    'jewellery_type' => 'Ring',
                    'metal_type' => 'Gold',
                    'selling_price' => 1500,
                    'mrp' => 2000,
                    'stock_quantity' => 10,
                    'is_active' => true,
                ]);
            }
        }

        $categories = StorefrontCatalog::topCategoriesByProductCount();

        $this->assertSame(
            ['Category 5', 'Category 4', 'Category 3', 'Category 2'],
            collect($categories)->pluck('name')->all(),
        );
        $this->assertSame([5, 4, 3, 2], collect($categories)->pluck('count')->all());
    }
}

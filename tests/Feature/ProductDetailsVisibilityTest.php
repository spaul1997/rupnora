<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailsVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_details_hide_private_inventory_and_empty_fields(): void
    {
        $category = Category::create([
            'name' => 'Product Detail Earrings',
            'slug' => 'product-detail-earrings',
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Aurora Pearl Drop Earrings',
            'slug' => 'aurora-pearl-drop-earrings',
            'sku' => 'EAR-001',
            'category_id' => $category->id,
            'jewellery_type' => 'Earrings',
            'metal_type' => 'Rose Gold Plated',
            'occasion' => 'Everyday',
            'mrp' => 2500,
            'selling_price' => 2200,
            'stock_quantity' => 6,
            'is_active' => true,
        ]);

        $this->get(route('product.show', 'aurora-pearl-drop-earrings'))
            ->assertOk()
            ->assertSee('Product Details')
            ->assertSee('Aurora Pearl Drop Earrings')
            ->assertSee('EAR-001')
            ->assertDontSee('Available Quantity')
            ->assertDontSee('Occasion')
            ->assertDontSee('Barcode')
            ->assertDontSee('Brand')
            ->assertDontSee('Diamond Details')
            ->assertDontSee('Gemstone Details')
            ->assertDontSee('0g')
            ->assertSee('Adjustable')
            ->assertSee('Water Resistant')
            ->assertSee('Return Available')
            ->assertSee('Refund Available')
            ->assertDontSee('Not specified');
    }

    public function test_product_details_show_warranty_when_available(): void
    {
        $category = Category::create([
            'name' => 'Warranty Detail Earrings',
            'slug' => 'warranty-detail-earrings',
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Warranty Pearl Drop Earrings',
            'slug' => 'warranty-pearl-drop-earrings',
            'sku' => 'EAR-WARRANTY-001',
            'category_id' => $category->id,
            'jewellery_type' => 'Earrings',
            'metal_type' => 'Rose Gold Plated',
            'mrp' => 2500,
            'selling_price' => 2200,
            'stock_quantity' => 6,
            'is_active' => true,
            'has_warranty' => true,
            'warranty_months' => 12,
        ]);

        $this->get(route('product.show', 'warranty-pearl-drop-earrings'))
            ->assertOk()
            ->assertSee('Warranty')
            ->assertSee('12 months');
    }
}

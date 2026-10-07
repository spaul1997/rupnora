<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Support\ShoppingCart;
use App\Support\StorefrontCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class OutOfStockPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_out_of_stock_product_cannot_be_added_to_the_cart(): void
    {
        $product = $this->outOfStockProduct();

        $this->postJson(route('cart.store'), [
            'product_id' => $product->id,
            'qty' => 1,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('product_id')
            ->assertJsonPath('errors.product_id.0', 'This product is currently out of stock.');

        $this->assertSame(0, ShoppingCart::count());
    }

    public function test_out_of_stock_purchase_buttons_are_disabled_on_product_pages_and_cards(): void
    {
        $product = $this->outOfStockProduct();

        $response = $this->get(route('product.show', $product->slug))->assertOk();
        $content = $response->getContent();

        $response
            ->assertSee('This product is currently out of stock and cannot be purchased.')
            ->assertSee('Out of Stock');

        $this->assertSame(2, preg_match_all('/data-purchase-action="add-to-cart"[^>]*disabled/', $content));
        $this->assertSame(2, preg_match_all('/data-purchase-action="buy-now"[^>]*disabled/', $content));

        $card = Blade::render(
            '<x-ui.product-card :product="$product" />',
            ['product' => StorefrontCatalog::product($product->id)]
        );

        $this->assertStringContainsString('Out of Stock', $card);
        $this->assertMatchesRegularExpression('/<button[^>]*data-card-cart-action[^>]*disabled/', $card);
    }

    private function outOfStockProduct(): Product
    {
        $category = Category::query()->create([
            'name' => 'Out of Stock Jewellery',
            'slug' => 'out-of-stock-jewellery',
            'is_active' => true,
        ]);

        return Product::query()->create([
            'name' => 'Unavailable Gold Ring',
            'slug' => 'unavailable-gold-ring',
            'sku' => 'OUT-STOCK-0001',
            'category_id' => $category->id,
            'jewellery_type' => 'Ring',
            'metal_type' => 'Gold',
            'mrp' => 2500,
            'selling_price' => 2200,
            'stock_quantity' => 0,
            'is_active' => true,
        ]);
    }
}

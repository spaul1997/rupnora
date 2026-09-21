<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\StorefrontCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialProofToastTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_genuine_eligible_order_is_labelled_as_a_recent_purchase(): void
    {
        $product = $this->product();
        $customer = User::factory()->create();
        $order = Order::create([
            'order_number' => 'ORD-SOCIAL-001',
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'subtotal' => 1200,
            'grand_total' => 1200,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 1,
            'price' => 1200,
            'total' => 1200,
        ]);

        $item = collect(StorefrontCatalog::socialProofItems())->firstWhere('id', $product->id);

        $this->assertNotNull($item);
        $this->assertTrue($item['recentlyPurchased']);
        $this->assertSame(route('product.show', $product->slug), $item['url']);
    }

    public function test_an_unsold_product_is_presented_as_a_popular_pick(): void
    {
        $product = $this->product();

        $item = collect(StorefrontCatalog::socialProofItems())->firstWhere('id', $product->id);

        $this->assertNotNull($item);
        $this->assertFalse($item['recentlyPurchased']);
    }

    public function test_the_storefront_receives_social_proof_data(): void
    {
        $product = $this->product();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('socialProofItems', false)
            ->assertSee($product->name);
    }

    private function product(): Product
    {
        $category = Category::create([
            'name' => 'Social Proof Jewellery',
            'slug' => 'social-proof-jewellery',
            'is_active' => true,
        ]);

        return Product::create([
            'name' => 'Moonlight Solitaire Ring',
            'slug' => 'moonlight-solitaire-ring',
            'sku' => 'SOCIAL-001',
            'category_id' => $category->id,
            'jewellery_type' => 'Ring',
            'metal_type' => 'Gold',
            'mrp' => 1500,
            'selling_price' => 1200,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
    }
}

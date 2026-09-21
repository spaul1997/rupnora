<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Support\StorefrontCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRatingFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_product_has_stable_default_rating_and_review_metrics(): void
    {
        $product = $this->createProduct();

        $first = StorefrontCatalog::product($product->slug);
        $second = StorefrontCatalog::product($product->slug);

        $this->assertNotNull($first);
        $this->assertSame($first['rating'], $second['rating']);
        $this->assertSame($first['ratings_count'], $second['ratings_count']);
        $this->assertSame($first['reviews_count'], $second['reviews_count']);
        $this->assertGreaterThanOrEqual(4.0, $first['rating']);
        $this->assertLessThanOrEqual(4.8, $first['rating']);
        $this->assertGreaterThanOrEqual(150, $first['ratings_count']);
        $this->assertLessThanOrEqual(200, $first['ratings_count']);
        $this->assertGreaterThanOrEqual(40, $first['reviews_count']);
        $this->assertLessThanOrEqual(80, $first['reviews_count']);
    }

    public function test_approved_customer_reviews_increment_the_default_counts(): void
    {
        $product = $this->createProduct();
        $before = StorefrontCatalog::product($product->slug);
        $customer = User::factory()->create();

        Review::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Beautiful piece',
            'review' => 'The finish and detailing are excellent.',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $after = StorefrontCatalog::product($product->slug);

        $this->assertSame($before['ratings_count'] + 1, $after['ratings_count']);
        $this->assertSame($before['reviews_count'] + 1, $after['reviews_count']);
        $this->assertGreaterThanOrEqual($before['rating'], $after['rating']);
    }

    private function createProduct(): Product
    {
        $category = Category::create([
            'name' => 'Rating Test Jewellery',
            'slug' => 'rating-test-jewellery',
            'is_active' => true,
        ]);

        return Product::create([
            'name' => 'Celestial Gold Ring',
            'slug' => 'celestial-gold-ring',
            'sku' => 'RATING-001',
            'category_id' => $category->id,
            'jewellery_type' => 'Ring',
            'metal_type' => 'Gold',
            'mrp' => 5000,
            'selling_price' => 4500,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
    }
}

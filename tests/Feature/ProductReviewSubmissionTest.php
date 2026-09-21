<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_asked_to_sign_in_and_cannot_submit_a_review(): void
    {
        $product = $this->createProduct();

        $this->get(route('product.show', $product->slug))
            ->assertOk()
            ->assertSee('Sign In to Review')
            ->assertDontSee('Submit Review');

        $this->post(route('product.reviews.store', $product), [
            'rating' => 5,
            'review' => 'This should not be accepted for a guest.',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_signed_in_customer_can_submit_a_review_for_moderation(): void
    {
        $product = $this->createProduct();
        $customer = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->post(route('product.reviews.store', $product), [
                'rating' => 5,
                'title' => 'Beautiful craftsmanship',
                'review' => 'The ring looks even better in person and fits perfectly.',
            ])->assertRedirect(route('product.show', $product->slug).'#customer-reviews')
            ->assertSessionHas('review_success');

        $this->assertDatabaseHas('reviews', [
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Beautiful craftsmanship',
            'status' => 'pending',
        ]);
    }

    public function test_sign_in_returns_customer_to_the_product_review_form(): void
    {
        $product = $this->createProduct();
        $customer = User::factory()->create([
            'email' => 'reviewer@example.com',
            'role' => 'customer',
            'is_active' => true,
        ]);
        $returnPath = route('product.show', $product->slug, false).'?review=1#customer-reviews';

        $this->get(route('login', ['redirect' => $returnPath]))
            ->assertOk()
            ->assertSessionHas('url.intended', url('/').$returnPath);

        $this->post(route('login.store'), [
            'login' => $customer->email,
            'password' => 'password',
        ])->assertRedirect(url('/').$returnPath);
    }

    public function test_customer_can_update_their_existing_review_without_creating_a_duplicate(): void
    {
        $product = $this->createProduct();
        $customer = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        Review::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'rating' => 4,
            'review' => 'The original review has enough detail.',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $this->actingAs($customer)
            ->post(route('product.reviews.store', $product), [
                'rating' => 5,
                'review' => 'The updated review is even more positive.',
            ])->assertSessionHas('review_success');

        $this->assertDatabaseCount('reviews', 1);
        $this->assertDatabaseHas('reviews', [
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'rating' => 5,
            'status' => 'pending',
            'approved_at' => null,
        ]);
    }

    public function test_approved_product_review_is_displayed_on_the_product_page(): void
    {
        $product = $this->createProduct();
        $customer = User::factory()->create([
            'name' => 'Kavya Rao',
            'role' => 'customer',
            'is_active' => true,
        ]);

        Review::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'rating' => 5,
            'review' => 'A genuinely lovely piece with a beautiful finish.',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $this->get(route('product.show', $product->slug))
            ->assertOk()
            ->assertSee('Kavya Rao')
            ->assertSee('A genuinely lovely piece with a beautiful finish.');
    }

    private function createProduct(): Product
    {
        $category = Category::create([
            'name' => 'Review Test Jewellery',
            'slug' => 'review-test-jewellery',
            'is_active' => true,
        ]);

        return Product::create([
            'name' => 'Starlight Gold Ring',
            'slug' => 'starlight-gold-ring',
            'sku' => 'REVIEW-001',
            'category_id' => $category->id,
            'jewellery_type' => 'Ring',
            'metal_type' => 'Gold',
            'mrp' => 6000,
            'selling_price' => 5500,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
    }
}

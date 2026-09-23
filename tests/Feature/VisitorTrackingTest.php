<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_layout_contains_the_common_visitor_tracker(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('visitorTracking:', escape: false)
            ->assertSee('visitor-tracking', escape: false);
    }

    public function test_tracking_endpoint_saves_page_product_ip_user_agent_and_date(): void
    {
        $product = $this->makeProduct();

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.25',
            'HTTP_USER_AGENT' => 'Rupnora Browser Test/1.0',
        ])->postJson(route('visitor-tracking.store'), [
            'page_path' => '/product/visitor-test-ring',
            'page_title' => 'Visitor Test Ring — Rupnora',
            'route_name' => 'product.show',
            'product_id' => $product->id,
        ])->assertCreated()->assertJson(['tracked' => true]);

        $visit = VisitorLog::firstOrFail();

        $this->assertSame('/product/visitor-test-ring', $visit->page_path);
        $this->assertSame($product->id, $visit->product_id);
        $this->assertSame('203.0.113.25', $visit->ip_address);
        $this->assertSame('Rupnora Browser Test/1.0', $visit->user_agent);
        $this->assertNotNull($visit->visited_at);
    }

    public function test_product_page_exposes_its_product_id_to_the_tracker(): void
    {
        $product = $this->makeProduct();

        $this->get(route('product.show', $product->slug))
            ->assertOk()
            ->assertSee('\u0022routeName\u0022:\u0022product.show\u0022', escape: false)
            ->assertSee('\u0022productId\u0022:'.$product->id, escape: false);
    }

    public function test_admin_can_review_and_filter_visitor_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $product = $this->makeProduct();

        VisitorLog::create([
            'product_id' => $product->id,
            'page_path' => '/product/visitor-test-ring',
            'page_title' => 'Visitor Test Ring — Rupnora',
            'route_name' => 'product.show',
            'ip_address' => '203.0.113.25',
            'user_agent' => 'Rupnora Browser Test/1.0',
            'visited_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.visitor-tracking.index', ['type' => 'products']))
            ->assertOk()
            ->assertSee('Visitor Test Ring')
            ->assertSee('203.0.113.25')
            ->assertSee('Rupnora Browser Test/1.0');
    }

    public function test_non_admin_cannot_view_visitor_records(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $this->actingAs($customer)
            ->get(route('admin.visitor-tracking.index'))
            ->assertForbidden();
    }

    private function makeProduct(): Product
    {
        $category = Category::create([
            'name' => 'Visitor Test Jewellery',
            'slug' => 'visitor-test-jewellery',
            'is_active' => true,
        ]);

        return Product::create([
            'name' => 'Visitor Test Ring',
            'slug' => 'visitor-test-ring',
            'sku' => 'VISITOR-001',
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

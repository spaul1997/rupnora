<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPriceHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_price_changes_are_automatically_recorded_with_direction_and_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $product = $this->product();

        $initial = $product->priceHistories()->firstOrFail();
        $this->assertSame('initial', $initial->change_type);
        $this->assertSame(1000.0, (float) $initial->final_price);
        $this->assertNull($initial->previous_final_price);

        $this->actingAs($admin);
        $product->priceChangeNote = 'Seasonal price reduction.';
        $product->update(['selling_price' => 900]);

        $decrease = $product->priceHistories()->firstOrFail();
        $this->assertSame('decrease', $decrease->change_type);
        $this->assertSame(1000.0, (float) $decrease->previous_final_price);
        $this->assertSame(900.0, (float) $decrease->final_price);
        $this->assertSame($admin->id, $decrease->changed_by);
        $this->assertSame('admin', $decrease->source);
        $this->assertSame('Seasonal price reduction.', $decrease->note);
        $this->assertContains('selling_price', $decrease->changed_fields);
        $this->assertContains('final_price', $decrease->changed_fields);

        $product->priceChangeNote = null;
        $product->update(['selling_price' => 1100, 'mrp' => 1300]);
        $increase = $product->priceHistories()->firstOrFail();
        $this->assertSame('increase', $increase->change_type);
        $this->assertSame(900.0, (float) $increase->previous_final_price);
        $this->assertSame(1100.0, (float) $increase->final_price);

        $historyCount = $product->priceHistories()->count();
        $product->update(['name' => 'Renamed Product']);
        $this->assertSame($historyCount, $product->priceHistories()->count());
    }

    public function test_admin_product_page_displays_the_price_chart_and_snapshot_table(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $product = $this->product();
        $product->priceChangeNote = 'Gold rate revision.';
        $this->actingAs($admin);
        $product->update(['selling_price' => 1150]);

        $this->get(route('admin.products.show', $product))->assertOk()
            ->assertViewHas('priceHistory', fn ($history) => $history->count() === 2)
            ->assertSee('Price History')
            ->assertSee('price history chart')
            ->assertSee('Gold rate revision.')
            ->assertSee('Increase')
            ->assertSee('+₹150.00');
    }

    public function test_scheduled_sync_records_price_changes_caused_by_offer_expiry(): void
    {
        $this->travelTo(now()->startOfDay());
        $product = $this->product([
            'offer_price' => 800,
            'offer_expiry_date' => today()->toDateString(),
        ]);

        $this->assertSame(800.0, (float) $product->priceHistories()->firstOrFail()->final_price);

        $this->travel(1)->day();
        $this->artisan('products:sync-prices')->expectsOutput('Synchronized 1 product price.')->assertExitCode(0);

        $history = $product->priceHistories()->firstOrFail();
        $this->assertSame('increase', $history->change_type);
        $this->assertSame(800.0, (float) $history->previous_final_price);
        $this->assertSame(1000.0, (float) $history->final_price);
        $this->assertSame('automatic', $history->source);
        $this->assertSame('Effective price changed automatically after an offer or discount period ended.', $history->note);
        $this->assertSame(2, $product->priceHistories()->count());
    }

    private function product(array $overrides = []): Product
    {
        $category = Category::create([
            'name' => 'Price History Category '.uniqid(),
            'slug' => 'price-history-category-'.uniqid(),
            'is_active' => true,
        ]);

        return Product::create(array_merge([
            'name' => 'Price History Product',
            'slug' => 'price-history-product-'.uniqid(),
            'sku' => 'PRICE-'.strtoupper(uniqid()),
            'category_id' => $category->id,
            'jewellery_type' => 'Ring',
            'metal_type' => 'Gold',
            'mrp' => 1200,
            'selling_price' => 1000,
            'making_charge' => 0,
            'gst_percentage' => 0,
            'stock_quantity' => 10,
            'minimum_stock' => 2,
            'is_active' => true,
        ], $overrides));
    }
}

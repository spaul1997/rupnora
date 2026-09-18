<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\JewelleryCollection;
use App\Models\JewelleryType;
use App\Models\MetalType;
use App\Models\Product;
use App\Models\User;
use App\Support\StorefrontCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductPromotionExpiryTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('pricingCases')]
    public function test_current_prices_and_collection_price_limits_ignore_expired_promotions(array $attributes, float $expected): void
    {
        $this->travelTo(Carbon::parse('2026-09-17 23:59:59', 'UTC'));
        $product = $this->product($attributes);
        // Simulate a price last saved at an earlier time.
        DB::table('products')->where('id', $product->id)->update(['final_price' => 1]);

        $this->assertSame($expected, $product->fresh()->final_price);
        $this->assertSame($expected, StorefrontCatalog::product($product->id)['price']);
        $this->assertTrue(Product::whereKey($product->id)->pricedAtMost($expected)->exists());
        $this->assertFalse(Product::whereKey($product->id)->pricedAtMost($expected - 0.01)->exists());
    }

    public static function pricingCases(): array
    {
        return [
            'no expiry dates' => [[], 720.0],
            'both valid through today' => [['offer_expiry_date' => '2026-09-17', 'discount_expiry_date' => '2026-09-17'], 720.0],
            'offer expired, discount active' => [['offer_expiry_date' => '2026-09-16', 'discount_expiry_date' => '2026-09-18'], 900.0],
            'discount expired, offer active' => [['offer_expiry_date' => '2026-09-18', 'discount_expiry_date' => '2026-09-16'], 800.0],
            'both expired' => [['offer_expiry_date' => '2026-09-16', 'discount_expiry_date' => '2026-09-16'], 1000.0],
            'fixed discount active' => [['discount_type' => 'fixed', 'discount_value' => 100, 'discount_expiry_date' => '2026-09-17'], 700.0],
            'fixed discount expired' => [['discount_type' => 'fixed', 'discount_value' => 100, 'discount_expiry_date' => '2026-09-16'], 800.0],
            'no offer price' => [['offer_price' => null], 900.0],
            'zero offer price' => [['offer_price' => 0], 0.0],
            'full discount' => [['discount_value' => 100], 0.0],
            'fixed discount greater than price' => [['discount_type' => 'fixed', 'discount_value' => 2000], 0.0],
            'making charge and GST' => [['making_charge' => 50, 'gst_percentage' => 3], 793.1],
            'expired offer with making charge and GST' => [['offer_expiry_date' => '2026-09-16', 'making_charge' => 50, 'gst_percentage' => 3], 978.5],
        ];
    }

    public function test_promotions_stop_after_the_expiry_day_without_another_product_save(): void
    {
        $this->travelTo(Carbon::parse('2026-09-17 23:59:59', 'UTC'));
        $product = $this->product(['offer_expiry_date' => '2026-09-17', 'discount_expiry_date' => '2026-09-17']);
        $this->assertSame(720.0, $product->final_price);
        $this->travelTo(Carbon::parse('2026-09-18 00:00:00', 'UTC'));
        $this->assertSame(1000.0, $product->final_price);
        $this->assertSame(720.0, (float) DB::table('products')->where('id', $product->id)->value('final_price'));
        $this->assertTrue($product->offerHasExpired());
        $this->assertTrue($product->discountHasExpired());
    }

    public function test_admin_can_create_edit_and_clear_both_expiry_dates(): void
    {
        $this->travelTo(Carbon::parse('2026-09-17 12:00:00', 'UTC'));
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $data = $this->adminData();
        $this->actingAs($admin)->get(route('admin.products.create'))->assertOk()->assertSee('Offer Expiry Date')->assertDontSee('Discount Expiry Date');
        $this->post(route('admin.products.store'), $data)->assertSessionHasNoErrors();
        $product = Product::where('sku', $data['sku'])->firstOrFail();
        $this->assertSame('2026-09-17', $product->offer_expiry_date->toDateString());
        $this->assertSame('2026-09-18', $product->discount_expiry_date->toDateString());
        $this->get(route('admin.products.edit', $product))->assertOk()->assertSee('value="2026-09-17"', escape: false)->assertDontSee('value="2026-09-18"', escape: false);

        $data['offer_expiry_date'] = '2026-09-16';
        $data['discount_expiry_date'] = '2026-09-16';
        $this->put(route('admin.products.update', $product), $data)->assertSessionHasNoErrors();
        $this->assertSame(1000.0, $product->fresh()->final_price);
        $this->get(route('admin.products.show', $product))->assertOk()->assertSee('Expired')->assertSee('1,000');

        $data['offer_expiry_date'] = '';
        $data['discount_expiry_date'] = '';
        $this->put(route('admin.products.update', $product), $data)->assertSessionHasNoErrors();
        $this->assertNull($product->fresh()->offer_expiry_date);
        $this->assertNull($product->fresh()->discount_expiry_date);
        $this->assertSame(720.0, $product->fresh()->final_price);
    }

    public function test_admin_rejects_invalid_expiry_dates_on_create_and_update(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $data = $this->adminData();
        $valid = $data;
        $data['offer_expiry_date'] = '2026-02-30';
        $data['discount_expiry_date'] = 'not-a-date';
        $this->actingAs($admin)->post(route('admin.products.store'), $data)->assertSessionHasErrors(['offer_expiry_date', 'discount_expiry_date']);
        $this->assertDatabaseMissing('products', ['sku' => $data['sku']]);
        $this->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('The product was not saved. Please fix the following:');
        $this->post(route('admin.products.store'), $valid)->assertSessionHasNoErrors();
        $product = Product::where('sku', $data['sku'])->firstOrFail();
        $this->put(route('admin.products.update', $product), $data)->assertSessionHasErrors(['offer_expiry_date', 'discount_expiry_date']);
        $this->assertSame('2026-09-17', $product->fresh()->offer_expiry_date->toDateString());
    }

    public function test_product_listings_details_and_wishlist_show_the_current_price_and_active_dates(): void
    {
        $this->travelTo(Carbon::parse('2026-09-17 12:00:00', 'UTC'));
        $product = $this->product(['offer_expiry_date' => '2026-09-17', 'discount_expiry_date' => '2026-09-17', 'is_new_arrival' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $customer->wishlistProducts()->attach($product);
        $this->actingAs($customer)->get(route('product.show', $product->slug))->assertOk()->assertSee('Offer price valid through 17 Sep 2026.')->assertSee('Discount valid through 17 Sep 2026.');
        $this->travelTo(Carbon::parse('2026-09-18 00:00:00', 'UTC'));
        $this->get(route('product.show', $product->slug))->assertOk()->assertViewHas('product', fn ($data) => $data['price'] === 1000.0)
            ->assertDontSee('Offer price valid through')->assertDontSee('Discount valid through')->assertDontSee('% OFF');
        $this->get(route('new-arrivals'))->assertOk()->assertViewHas('products', fn ($products) => $products[0]['price'] === 1000.0);
        $this->get(route('account.wishlist'))->assertOk()->assertViewHas('products', fn ($products) => $products[0]['price'] === 1000.0);
    }

    public function test_collection_price_limits_change_when_the_offer_expires(): void
    {
        $this->travelTo(Carbon::parse('2026-09-17 12:00:00', 'UTC'));
        $product = $this->product(['selling_price' => 120000, 'mrp' => 120000, 'offer_price' => 40000, 'discount_type' => null, 'offer_expiry_date' => '2026-09-17']);
        $this->assertContains($product->id, array_column(StorefrontCatalog::byCollection('daily-wear'), 'id'));
        $this->travelTo(Carbon::parse('2026-09-18 00:00:00', 'UTC'));
        $this->assertNotContains($product->id, array_column(StorefrontCatalog::byCollection('daily-wear'), 'id'));
    }

    public function test_cart_responses_and_checkout_reprice_items_after_expiry_and_preserve_previous_orders(): void
    {
        $this->travelTo(Carbon::parse('2026-09-17 12:00:00', 'UTC'));
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $address = $customer->addresses()->create(['type' => 'Home', 'name' => 'Pricing Customer', 'phone' => '9876543210', 'line1' => 'Pricing Street',
            'city' => 'Kolkata', 'state' => 'West Bengal', 'pincode' => '700001', 'country' => 'India', 'is_default' => true]);
        $product = $this->product(['offer_expiry_date' => '2026-09-17', 'discount_expiry_date' => '2026-09-17']);
        $this->actingAs($customer)->postJson(route('cart.store'), ['product_id' => $product->id, 'qty' => 1])->assertOk()->assertJsonPath('items.0.price', 720);
        $this->postJson(route('checkout.order.store'), ['address_id' => $address->id, 'delivery' => 'standard', 'payment' => 'cod'])->assertOk();
        $original = $customer->orders()->firstOrFail();
        $this->assertSame(720.0, (float) $original->items()->first()->price);
        $response = $this->postJson(route('cart.store'), ['product_id' => $product->id, 'qty' => 1])->assertOk();
        $key = $response->json('items.0.key');

        $this->travelTo(Carbon::parse('2026-09-18 00:00:00', 'UTC'));
        $this->patchJson(route('cart.update', $key), ['qty' => 1])->assertOk()->assertJsonPath('items.0.price', 1000)->assertJsonPath('summary.total', 1030);
        $this->get(route('checkout'))->assertOk()->assertViewHas('items', fn ($items) => $items[0]['product']['price'] === 1000.0);
        $this->postJson(route('checkout.order.store'), ['address_id' => $address->id, 'delivery' => 'standard', 'payment' => 'cod'])->assertOk();
        $latest = $customer->orders()->latest('id')->firstOrFail();
        $this->assertSame(1000.0, (float) $latest->items()->first()->price);
        $this->assertSame(1030.0, (float) $latest->grand_total);
        $this->assertSame(720.0, (float) $original->items()->first()->price);
        $this->get(route('account.orders.show', $original->order_number))->assertOk()->assertViewHas('order', fn ($order) => $order['items'][0]['product']['price'] === 720.0);
    }

    public function test_variant_offer_prices_also_respect_the_product_offer_expiry_date(): void
    {
        $this->travelTo(Carbon::parse('2026-09-17 12:00:00', 'UTC'));
        $product = $this->product(['offer_expiry_date' => '2026-09-17']);
        $product->variants()->create(['sku' => 'PROMO-VARIANT', 'size' => '12', 'price' => 1000, 'offer_price' => 800, 'stock_quantity' => 5]);
        $this->assertSame(800.0, (float) StorefrontCatalog::product($product->id)['variants'][0]['price']);
        $this->travelTo(Carbon::parse('2026-09-18 00:00:00', 'UTC'));
        $this->assertSame(1000.0, (float) StorefrontCatalog::product($product->id)['variants'][0]['price']);
    }

    private function product(array $attributes = []): Product
    {
        $category = Category::firstOrCreate(['slug' => 'promotion-test'], ['name' => 'Promotion Test', 'is_active' => true]);

        return Product::create(['name' => 'Promotion Test Product', 'slug' => 'promotion-test-product', 'sku' => 'PROMOTION-TEST-001',
            'category_id' => $category->id, 'jewellery_type' => 'Ring', 'metal_type' => 'Gold', 'mrp' => 1000, 'selling_price' => 1000,
            'offer_price' => 800, 'discount_type' => 'percentage', 'discount_value' => 10, 'gst_percentage' => 0, 'stock_quantity' => 10, 'is_active' => true, ...$attributes]);
    }

    private function adminData(): array
    {
        $parent = Category::create(['name' => 'Promotion Parent', 'slug' => 'promotion-parent', 'is_active' => true]);
        $child = Category::create(['name' => 'Promotion Child', 'slug' => 'promotion-child', 'parent_id' => $parent->id, 'is_active' => true]);
        $type = JewelleryType::create(['name' => 'Promotion Style', 'slug' => 'promotion-style', 'is_active' => true]);
        $collection = JewelleryCollection::create(['name' => 'Promotion Collection', 'slug' => 'promotion-collection', 'is_active' => true]);

        return ['name' => 'Admin Promotion Product', 'sku' => 'ADMIN-PROMO-001', 'parent_category_id' => $parent->id, 'category_id' => $child->id,
            'jewellery_type' => $type->name, 'metal_type' => MetalType::active()->firstOrFail()->name, 'collection' => [$collection->slug],
            'mrp' => 1000, 'selling_price' => 1000, 'offer_price' => 800, 'offer_expiry_date' => '2026-09-17',
            'discount_type' => 'percentage', 'discount_value' => 10, 'discount_expiry_date' => '2026-09-18', 'stock_quantity' => 10, 'is_active' => 1];
    }
}

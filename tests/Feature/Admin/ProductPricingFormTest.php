<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\JewelleryCollection;
use App\Models\JewelleryType;
use App\Models\MetalType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPricingFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_pricing_fields_use_selling_price_and_read_only_gst_inclusive_product_price(): void
    {
        $this->catalogData();

        $this->actingAs($this->admin())
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('Selling Price (₹)')
            ->assertSee('GST Amount')
            ->assertSee('Product Price (with GST)')
            ->assertSee('name="gst_percentage"', false)
            ->assertSee('value="3"', false)
            ->assertSee('name="gst_amount"', false)
            ->assertSee('x-bind:value="gstAmount()"', false)
            ->assertSee('name="selling_price"', false)
            ->assertSee('name="product_price_with_gst"', false)
            ->assertSee('x-bind:value="productPriceWithGst()"', false)
            ->assertSee('Purchase History')
            ->assertSee('+ Add Purchase')
            ->assertSee('purchase_histories[${i}][purchase_date]', false)
            ->assertSee('purchase_histories[${i}][vendor]', false)
            ->assertSee('purchase_histories[${i}][price]', false)
            ->assertDontSee('Offer Price')
            ->assertDontSee('Offer Expiry Date')
            ->assertDontSee('On Sale');
    }

    public function test_product_price_with_gst_is_calculated_from_selling_price(): void
    {
        [$parent, $category, $jewelleryType, $metalType, $collection] = $this->catalogData();

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), [
                'name' => 'GST Pricing Product',
                'sku' => 'GST-PRICING-001',
                'parent_category_id' => $parent->id,
                'category_id' => $category->id,
                'collection' => [$collection->slug],
                'jewellery_type' => $jewelleryType->name,
                'metal_type' => $metalType->name,
                'mrp' => 1200,
                'selling_price' => 1000,
                'gst_percentage' => 3,
                'stock_quantity' => 2,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $product = Product::query()->where('sku', 'GST-PRICING-001')->firstOrFail();

        $this->assertSame(1000.0, (float) $product->selling_price);
        $this->assertSame(3.0, (float) $product->gst_percentage);
        $this->assertSame(1030.0, $product->final_price);

        $this->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('value="1000"', false)
            ->assertSee('value="30.00"', false)
            ->assertSee('value="1030"', false);
    }

    public function test_blank_gst_percentage_defaults_to_three_percent(): void
    {
        [$parent, $category, $jewelleryType, $metalType, $collection] = $this->catalogData();

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), [
                'name' => 'Default GST Product',
                'sku' => 'DEFAULT-GST-001',
                'parent_category_id' => $parent->id,
                'category_id' => $category->id,
                'collection' => [$collection->slug],
                'jewellery_type' => $jewelleryType->name,
                'metal_type' => $metalType->name,
                'mrp' => 1200,
                'selling_price' => 1000,
                'gst_percentage' => '',
                'stock_quantity' => 2,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $product = Product::query()->where('sku', 'DEFAULT-GST-001')->firstOrFail();

        $this->assertSame(1000.0, (float) $product->selling_price);
        $this->assertSame(3.0, (float) $product->gst_percentage);
        $this->assertSame(1030.0, $product->final_price);
    }

    public function test_admin_can_add_and_replace_multiple_purchase_history_rows(): void
    {
        [$parent, $category, $jewelleryType, $metalType, $collection] = $this->catalogData();
        $admin = $this->admin();
        $data = [
            'name' => 'Purchase History Product',
            'sku' => 'PURCHASE-HISTORY-001',
            'parent_category_id' => $parent->id,
            'category_id' => $category->id,
            'collection' => [$collection->slug],
            'jewellery_type' => $jewelleryType->name,
            'metal_type' => $metalType->name,
            'mrp' => 1500,
            'selling_price' => 1000,
            'gst_percentage' => 3,
            'stock_quantity' => 2,
            'purchase_histories' => [
                ['purchase_date' => '2026-09-01', 'vendor' => 'Vendor Alpha', 'price' => 700.25],
                ['purchase_date' => '2026-10-01', 'vendor' => 'Vendor Beta', 'price' => 725.50],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.products.store'), $data)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $product = Product::query()->where('sku', 'PURCHASE-HISTORY-001')->firstOrFail();

        $this->assertCount(2, $product->purchaseHistories);
        $this->assertDatabaseHas('product_purchase_histories', [
            'product_id' => $product->id,
            'vendor' => 'Vendor Alpha',
            'price' => 700.25,
        ]);
        $this->assertDatabaseHas('product_purchase_histories', [
            'product_id' => $product->id,
            'vendor' => 'Vendor Beta',
            'price' => 725.50,
        ]);
        $this->assertSame('2026-09-01', $product->purchaseHistories->firstWhere('vendor', 'Vendor Alpha')->purchase_date->toDateString());
        $this->assertSame('2026-10-01', $product->purchaseHistories->firstWhere('vendor', 'Vendor Beta')->purchase_date->toDateString());

        $this->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('Vendor Alpha')
            ->assertSee('Vendor Beta');

        $data['purchase_histories'] = [
            ['purchase_date' => '2026-10-14', 'vendor' => 'Vendor Gamma', 'price' => 740.75],
        ];

        $this->put(route('admin.products.update', $product), $data)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertCount(1, $product->fresh()->purchaseHistories);
        $this->assertDatabaseMissing('product_purchase_histories', [
            'product_id' => $product->id,
            'vendor' => 'Vendor Alpha',
        ]);
        $this->assertDatabaseHas('product_purchase_histories', [
            'product_id' => $product->id,
            'vendor' => 'Vendor Gamma',
            'price' => 740.75,
        ]);
        $this->assertSame('2026-10-14', $product->fresh()->purchaseHistories->first()->purchase_date->toDateString());
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function catalogData(): array
    {
        $parent = Category::query()->firstOrCreate(
            ['slug' => 'pricing-parent'],
            ['name' => 'Pricing Parent', 'is_active' => true]
        );
        $category = Category::query()->firstOrCreate(
            ['slug' => 'pricing-child'],
            ['name' => 'Pricing Child', 'parent_id' => $parent->id, 'is_active' => true]
        );
        $jewelleryType = JewelleryType::query()->firstOrCreate(
            ['slug' => 'pricing-type'],
            ['name' => 'Pricing Type', 'is_active' => true]
        );
        $metalType = MetalType::query()->firstOrCreate(
            ['slug' => 'pricing-metal'],
            ['name' => 'Pricing Metal', 'is_active' => true]
        );
        $collection = JewelleryCollection::query()->firstOrCreate(
            ['slug' => 'pricing-collection'],
            ['name' => 'Pricing Collection', 'is_active' => true]
        );

        return [$parent, $category, $jewelleryType, $metalType, $collection];
    }
}

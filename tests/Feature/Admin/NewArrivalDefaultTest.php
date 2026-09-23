<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\JewelleryCollection;
use App\Models\JewelleryType;
use App\Models\MetalType;
use App\Models\Product;
use App\Models\User;
use App\Support\StorefrontCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewArrivalDefaultTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_new_product_is_shown_in_new_arrivals_by_default(): void
    {
        $data = $this->productData('NEW-ARRIVAL-DEFAULT-001');

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $data)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $product = Product::query()->where('sku', $data['sku'])->firstOrFail();
        $product->images()->create([
            'image_path' => 'products/secondary.webp',
            'is_primary' => false,
            'sort_order' => 0,
        ]);
        $product->images()->create([
            'image_path' => 'products/primary.webp',
            'is_primary' => true,
            'sort_order' => 1,
        ]);

        $arrival = collect(StorefrontCatalog::newArrivals())->firstWhere('id', $product->id);

        $this->assertTrue($product->is_new_arrival);
        $this->assertNotNull($arrival);
        $this->assertSame('/storage/products/primary.webp', $arrival['primary_image']);
        $this->assertSame('/storage/products/primary.webp', $arrival['image']);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('/storage/products/primary.webp', false);
    }

    public function test_an_admin_can_opt_a_new_product_out_of_new_arrivals(): void
    {
        $data = $this->productData('NEW-ARRIVAL-OPT-OUT-001') + ['is_new_arrival' => '0'];

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $data)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $product = Product::query()->where('sku', $data['sku'])->firstOrFail();

        $this->assertFalse($product->is_new_arrival);
        $this->assertNotContains($product->id, array_column(StorefrontCatalog::newArrivals(), 'id'));
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function productData(string $sku): array
    {
        $parent = Category::query()->create([
            'name' => 'New Arrival Parent '.$sku,
            'slug' => 'new-arrival-parent-'.strtolower($sku),
            'is_active' => true,
        ]);
        $category = Category::query()->create([
            'name' => 'New Arrival Child '.$sku,
            'slug' => 'new-arrival-child-'.strtolower($sku),
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);
        $jewelleryType = JewelleryType::query()->create([
            'name' => 'New Arrival Style '.$sku,
            'slug' => 'new-arrival-style-'.strtolower($sku),
            'is_active' => true,
        ]);
        $metalType = MetalType::query()->create([
            'name' => 'New Arrival Metal '.$sku,
            'slug' => 'new-arrival-metal-'.strtolower($sku),
            'is_active' => true,
        ]);
        $collection = JewelleryCollection::query()->create([
            'name' => 'New Arrival Collection '.$sku,
            'slug' => 'new-arrival-collection-'.strtolower($sku),
            'is_active' => true,
        ]);

        return [
            'name' => 'Automatic New Arrival '.$sku,
            'sku' => $sku,
            'parent_category_id' => $parent->id,
            'category_id' => $category->id,
            'collection' => [$collection->slug],
            'jewellery_type' => $jewelleryType->name,
            'metal_type' => $metalType->name,
            'mrp' => 1500,
            'selling_price' => 1200,
            'stock_quantity' => 2,
            'is_active' => '1',
        ];
    }
}

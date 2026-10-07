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

class ProductSlugLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_slugs_are_unique_on_create_and_immutable_on_update(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        [$parent, $subcategory, $jewelleryType, $metalType, $collection] = $this->catalogData();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), $this->productData(
                $parent,
                $subcategory,
                $jewelleryType,
                $metalType,
                $collection,
                ['name' => 'First Slug Product', 'slug' => 'Featured Ring', 'sku' => 'SLUG-ONE-0001']
            ))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), $this->productData(
                $parent,
                $subcategory,
                $jewelleryType,
                $metalType,
                $collection,
                ['name' => 'Second Slug Product', 'slug' => 'Featured Ring', 'sku' => 'SLUG-TWO-0001']
            ))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $firstProduct = Product::query()->where('sku', 'SLUG-ONE-0001')->firstOrFail();
        $secondProduct = Product::query()->where('sku', 'SLUG-TWO-0001')->firstOrFail();

        $this->assertSame('featured-ring', $firstProduct->slug);
        $this->assertSame('featured-ring-2', $secondProduct->slug);

        $this->actingAs($admin)
            ->get(route('admin.products.edit', $secondProduct))
            ->assertOk()
            ->assertSee('name="slug"', false)
            ->assertSee('readonly', false)
            ->assertSee('Slug cannot be changed after product creation.');

        $this->actingAs($admin)
            ->put(route('admin.products.update', $secondProduct), $this->productData(
                $parent,
                $subcategory,
                $jewelleryType,
                $metalType,
                $collection,
                [
                    'name' => 'Renamed Slug Product',
                    'slug' => 'tampered-slug',
                    'sku' => $secondProduct->sku,
                ]
            ))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('featured-ring-2', $secondProduct->fresh()->slug);
    }

    public function test_dynamic_sitemap_contains_a_new_product_and_keeps_its_stable_slug_after_update(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        [$parent, $subcategory, $jewelleryType, $metalType, $collection] = $this->catalogData();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), $this->productData(
                $parent,
                $subcategory,
                $jewelleryType,
                $metalType,
                $collection,
                ['name' => 'Sitemap Product', 'slug' => '', 'sku' => 'SITEMAP-0001']
            ))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $product = Product::query()->where('sku', 'SITEMAP-0001')->firstOrFail();
        $productUrl = route('product.show', $product->slug);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee($productUrl, false);

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), $this->productData(
                $parent,
                $subcategory,
                $jewelleryType,
                $metalType,
                $collection,
                [
                    'name' => 'Updated Sitemap Product',
                    'slug' => 'changed-sitemap-slug',
                    'sku' => $product->sku,
                ]
            ))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('sitemap-product', $product->fresh()->slug);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee($productUrl, false)
            ->assertDontSee('changed-sitemap-slug');
    }

    private function catalogData(): array
    {
        $parent = Category::query()->firstOrCreate(
            ['slug' => 'slug-test-parent'],
            ['name' => 'Slug Test Parent', 'is_active' => true]
        );
        $subcategory = Category::query()->firstOrCreate(
            ['slug' => 'slug-test-child'],
            ['name' => 'Slug Test Child', 'parent_id' => $parent->id, 'is_active' => true]
        );
        $jewelleryType = JewelleryType::query()->firstOrCreate(
            ['slug' => 'slug-test-type'],
            ['name' => 'Slug Test Type', 'is_active' => true]
        );
        $metalType = MetalType::query()->firstOrCreate(
            ['slug' => 'slug-test-metal'],
            ['name' => 'Slug Test Metal', 'is_active' => true]
        );
        $collection = JewelleryCollection::query()->firstOrCreate(
            ['slug' => 'slug-test-collection'],
            ['name' => 'Slug Test Collection', 'is_active' => true]
        );

        return [$parent, $subcategory, $jewelleryType, $metalType, $collection];
    }

    private function productData(
        Category $parent,
        Category $subcategory,
        JewelleryType $jewelleryType,
        MetalType $metalType,
        JewelleryCollection $collection,
        array $overrides = []
    ): array {
        return array_merge([
            'name' => 'Slug Test Product',
            'slug' => '',
            'sku' => 'SLUG-TEST-0001',
            'parent_category_id' => $parent->id,
            'category_id' => $subcategory->id,
            'collection' => [$collection->slug],
            'jewellery_type' => $jewelleryType->name,
            'metal_type' => $metalType->name,
            'mrp' => 1500,
            'selling_price' => 1200,
            'stock_quantity' => 5,
            'is_active' => '1',
        ], $overrides);
    }
}

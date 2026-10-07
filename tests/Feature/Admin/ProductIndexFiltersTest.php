<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_filters_only_offer_parent_categories_and_have_searchable_dropdowns_without_purity(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $parent = $this->category('Filter Parent', 'filter-parent');
        $child = $this->category('Filter Child', 'filter-child', $parent);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertViewHas('categories', function ($categories) use ($parent, $child): bool {
                return $categories->contains('id', $parent->id)
                    && ! $categories->contains('id', $child->id);
            })
            ->assertSee('data-searchable-select="category_id"', false)
            ->assertSee('data-searchable-select="metal_type"', false)
            ->assertSee('data-searchable-select="stock_status"', false)
            ->assertSee('data-searchable-select="is_active"', false)
            ->assertDontSee('All Purity')
            ->assertDontSee('name="purity"', false);
    }

    public function test_parent_category_filter_includes_products_from_its_child_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $selectedParent = $this->category('Selected Parent', 'selected-parent');
        $selectedChild = $this->category('Selected Child', 'selected-child', $selectedParent);
        $otherParent = $this->category('Other Parent', 'other-parent');
        $otherChild = $this->category('Other Child', 'other-child', $otherParent);

        $this->product('Included Child Product', 'included-child-product', 'FILTER-0001', $selectedChild);
        $this->product('Excluded Child Product', 'excluded-child-product', 'FILTER-0002', $otherChild);

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['category_id' => $selectedParent->id]))
            ->assertOk()
            ->assertSee('Included Child Product')
            ->assertDontSee('Excluded Child Product');
    }

    private function category(string $name, string $slug, ?Category $parent = null): Category
    {
        return Category::query()->create([
            'name' => $name,
            'slug' => $slug,
            'parent_id' => $parent?->id,
            'is_active' => true,
        ]);
    }

    private function product(string $name, string $slug, string $sku, Category $category): Product
    {
        return Product::query()->create([
            'name' => $name,
            'slug' => $slug,
            'sku' => $sku,
            'category_id' => $category->id,
            'collection' => '[]',
            'jewellery_type' => 'Test Jewellery',
            'metal_type' => 'Gold',
            'mrp' => 1500,
            'selling_price' => 1200,
            'stock_quantity' => 5,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);
    }
}

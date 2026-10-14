<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\DiscountOffer;
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

    public function test_product_list_displays_the_final_price_including_gst(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $category = $this->category('Final Price Category', 'final-price-category');
        $product = $this->product('Final Price Product', 'final-price-product', 'FINAL-PRICE-001', $category);
        $product->update([
            'selling_price' => 1000,
            'gst_percentage' => 3,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('₹1,030')
            ->assertDontSee('₹1,000');
    }

    public function test_product_list_shows_the_special_price_against_the_original_price(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $category = $this->category('Special Price Category', 'special-price-category');
        $this->product('Special Price Product', 'special-price-product', 'SPECIAL-0001', $category)->update([
            'mrp' => 3000,
            'selling_price' => 2000,
            'gst_percentage' => 0,
            'discount_type' => 'percentage',
            'discount_value' => 25,
        ]);
        $this->product('Regular Price Product', 'regular-price-product', 'SPECIAL-0002', $category)->update([
            'mrp' => 4000,
            'selling_price' => 1800,
            'gst_percentage' => 0,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSeeInOrder(['₹1,500', '₹2,000', '25% off'])
            ->assertSee('₹1,800')
            ->assertDontSee('₹3,000')
            ->assertDontSee('₹4,000');
    }

    public function test_on_sale_filter_only_lists_products_with_a_live_offer_discount(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $category = $this->category('Sale Category', 'sale-category');
        $offer = DiscountOffer::query()->create([
            'reason' => 'Festive Sale',
            'scope_type' => 'product',
            'start_date' => today()->subDay(),
            'end_date' => today()->addWeek(),
            'is_active' => true,
        ]);
        $offerDiscount = [
            'discount_offer_id' => $offer->id,
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'discount_start_date' => today()->subDay(),
            'discount_expiry_date' => today()->addWeek(),
        ];

        $this->product('Live Sale Product', 'live-sale-product', 'SALE-0001', $category)->update($offerDiscount);
        $this->product('Expired Sale Product', 'expired-sale-product', 'SALE-0002', $category)
            ->update(['discount_expiry_date' => today()->subDay()] + $offerDiscount);
        $this->product('Upcoming Sale Product', 'upcoming-sale-product', 'SALE-0003', $category)
            ->update(['discount_start_date' => today()->addDay()] + $offerDiscount);
        $this->product('Manual Discount Product', 'manual-discount-product', 'SALE-0004', $category)
            ->update(['discount_offer_id' => null] + $offerDiscount);
        $this->product('Regular Price Product', 'regular-price-product', 'SALE-0005', $category);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('name="on_sale"', false);

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['on_sale' => 1]))
            ->assertOk()
            ->assertSee('Live Sale Product')
            ->assertDontSee('Expired Sale Product')
            ->assertDontSee('Upcoming Sale Product')
            ->assertDontSee('Manual Discount Product')
            ->assertDontSee('Regular Price Product');
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

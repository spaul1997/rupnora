<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\DiscountOffer;
use App\Models\Product;
use App\Models\User;
use App\Support\StorefrontCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DiscountOfferManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_offer_form_contains_campaign_dates_and_category_or_product_scopes(): void
    {
        [$parent, $child] = $this->categories();
        $inactiveParent = Category::create([
            'name' => 'Inactive Parent',
            'slug' => 'inactive-parent',
            'is_active' => false,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.discount-offers.create'))
            ->assertOk()
            ->assertSee('Offer Reason')
            ->assertSee('Start Date')
            ->assertSee('End Date')
            ->assertSee('Category wise')
            ->assertSee('Except Products')
            ->assertSee('Product wise')
            ->assertSee($parent->name)
            ->assertDontSee($child->name)
            ->assertDontSee($inactiveParent->name)
            ->assertSee("category_discounts[{$parent->id}][discount_type]", false);
    }

    public function test_category_offer_applies_to_category_products_except_selected_exclusions(): void
    {
        $this->travelTo(Carbon::parse('2026-10-14 12:00:00'));
        $admin = $this->admin();
        [$parent, $child] = $this->categories();
        $included = $this->product('Included Ring', 'INCLUDED-001', $child);
        $excluded = $this->product('Excluded Ring', 'EXCLUDED-001', $child);
        $secondParent = Category::create(['name' => 'Second Parent', 'slug' => 'second-parent', 'is_active' => true]);
        $secondChild = Category::create(['name' => 'Second Child', 'slug' => 'second-child', 'parent_id' => $secondParent->id, 'is_active' => true]);
        $fixedDiscountProduct = $this->product('Fixed Discount Ring', 'FIXED-001', $secondChild);
        $otherCategory = Category::create(['name' => 'Other', 'slug' => 'other-offer-category', 'is_active' => true]);
        $unrelated = $this->product('Unrelated Ring', 'UNRELATED-001', $otherCategory);

        $this->actingAs($admin)
            ->post(route('admin.discount-offers.store'), [
                'reason' => 'Festival category offer',
                'start_date' => '2026-10-14',
                'end_date' => '2026-10-20',
                'scope_type' => 'category',
                'categories' => [$parent->id, $secondParent->id],
                'category_discounts' => [
                    $parent->id => ['discount_type' => 'percentage', 'discount_value' => 10],
                    $secondParent->id => ['discount_type' => 'fixed', 'discount_value' => 200],
                ],
                'excluded_products_by_category' => [
                    $parent->id => [$excluded->id],
                ],
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.discount-offers.index'))
            ->assertSessionHasNoErrors();

        $offer = DiscountOffer::firstOrFail();
        $this->assertSame('Festival category offer', $offer->reason);
        $this->assertTrue($offer->categories->contains($parent));
        $this->assertTrue($offer->excludedProducts->contains($excluded));
        $this->assertSame('percentage', $offer->categories->firstWhere('id', $parent->id)->pivot->discount_type);
        $this->assertSame('fixed', $offer->categories->firstWhere('id', $secondParent->id)->pivot->discount_type);
        $this->assertSame(900.0, $included->fresh()->final_price);
        $this->assertSame($offer->id, $included->fresh()->discount_offer_id);
        $this->assertSame(800.0, $fixedDiscountProduct->fresh()->final_price);
        $this->assertSame($offer->id, $fixedDiscountProduct->fresh()->discount_offer_id);
        $this->assertSame(1000.0, $excluded->fresh()->final_price);
        $this->assertNull($excluded->fresh()->discount_offer_id);
        $this->assertSame(1000.0, $unrelated->fresh()->final_price);

        $this->get(route('admin.discount-offers.index'))
            ->assertOk()
            ->assertSee('Festival category offer')
            ->assertSee('2 different discounts')
            ->assertSee('1 excluded')
            ->assertSee('Active');
    }

    public function test_admin_can_toggle_offer_status_from_the_status_badge(): void
    {
        $this->travelTo(Carbon::parse('2026-10-14 12:00:00'));
        $admin = $this->admin();
        [, $category] = $this->categories();
        $product = $this->product('Toggle Offer Product', 'TOGGLE-001', $category);

        $this->actingAs($admin)->post(route('admin.discount-offers.store'), [
            'reason' => 'Toggle status offer',
            'start_date' => '2026-10-14',
            'end_date' => '2026-10-20',
            'scope_type' => 'product',
            'products' => [$product->id],
            'product_discounts' => [
                $product->id => ['discount_type' => 'percentage', 'discount_value' => 10],
            ],
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $offer = DiscountOffer::firstOrFail();
        $saleProduct = StorefrontCatalog::product($product->id);

        $this->assertContains('Sale', $saleProduct['badges']);
        $this->blade('<x-ui.product-card :product="$product" />', ['product' => $saleProduct])
            ->assertSee('Sale');
        $this->get(route('admin.products.index'))
            ->assertOk()
            ->assertSeeInOrder(['TOGGLE-001', '>Sale</span>'], false);

        $this->get(route('admin.discount-offers.index'))
            ->assertOk()
            ->assertSee(route('admin.discount-offers.toggle-active', $offer), false)
            ->assertDontSee('Remove');

        $this->from(route('admin.discount-offers.index'))
            ->patch(route('admin.discount-offers.toggle-active', $offer))
            ->assertRedirect(route('admin.discount-offers.index'))
            ->assertSessionHas('success', 'Offer deactivated successfully.');

        $this->assertFalse($offer->fresh()->is_active);
        $this->assertNull($product->fresh()->discount_offer_id);
        $this->assertSame(1000.0, $product->fresh()->final_price);
        $this->assertNotContains('Sale', StorefrontCatalog::product($product->id)['badges']);
        $this->get(route('admin.products.index'))
            ->assertOk()
            ->assertDontSee('>Sale</span>', false);

        $this->from(route('admin.discount-offers.index'))
            ->patch(route('admin.discount-offers.toggle-active', $offer))
            ->assertRedirect(route('admin.discount-offers.index'))
            ->assertSessionHas('success', 'Offer activated successfully.');

        $this->assertTrue($offer->fresh()->is_active);
        $this->assertSame($offer->id, $product->fresh()->discount_offer_id);
        $this->assertSame(900.0, $product->fresh()->final_price);
        $this->assertContains('Sale', StorefrontCatalog::product($product->id)['badges']);
    }

    public function test_product_offer_only_applies_during_its_date_range_and_can_be_removed(): void
    {
        $this->travelTo(Carbon::parse('2026-10-14 12:00:00'));
        $admin = $this->admin();
        [, $category] = $this->categories();
        $selected = $this->product('Selected Product', 'SELECTED-001', $category);
        $selected->update(['selling_price' => 1000.49]);
        $secondSelected = $this->product('Second Selected Product', 'SELECTED-002', $category);
        $unselected = $this->product('Unselected Product', 'UNSELECTED-001', $category);

        $this->actingAs($admin)->post(route('admin.discount-offers.store'), [
            'reason' => 'Weekend product offer',
            'start_date' => '2026-10-16',
            'end_date' => '2026-10-18',
            'scope_type' => 'product',
            'products' => [$selected->id, $secondSelected->id],
            'product_discounts' => [
                $selected->id => ['discount_type' => 'fixed', 'discount_value' => 200],
                $secondSelected->id => ['discount_type' => 'percentage', 'discount_value' => 5],
            ],
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $offer = DiscountOffer::firstOrFail();
        $this->assertSame(1000.49, $selected->fresh()->final_price);
        $this->assertSame(1000.0, $secondSelected->fresh()->final_price);
        $this->assertSame(1000.0, $unselected->fresh()->final_price);

        $this->travelTo(Carbon::parse('2026-10-16 12:00:00'));
        $this->artisan('products:sync-prices')->expectsOutput('Synchronized 2 product prices.')->assertExitCode(0);
        $this->assertSame(800.49, $selected->fresh()->final_price);
        $this->assertSame(950.0, $secondSelected->fresh()->final_price);
        $this->assertSame(1000.0, $unselected->fresh()->final_price);
        $this->get(route('product.show', $selected->slug))
            ->assertOk()
            ->assertViewHas('product', fn (array $product) => $product['price'] === 800.49)
            ->assertSee('₹800')
            ->assertDontSee('₹800.49');
        $this->get(route('new-arrivals'))
            ->assertOk()
            ->assertSee('₹800')
            ->assertDontSee('₹800.49');
        $this->assertSame(
            'Effective price changed automatically when an offer or discount period started.',
            $selected->priceHistories()->firstOrFail()->note,
        );

        $this->travelTo(Carbon::parse('2026-10-19 00:00:00'));
        $this->assertSame(1000.49, $selected->fresh()->final_price);
        $this->assertSame(1000.0, $secondSelected->fresh()->final_price);

        $this->actingAs($admin)
            ->delete(route('admin.discount-offers.destroy', $offer))
            ->assertRedirect(route('admin.discount-offers.index'));

        $this->assertDatabaseMissing('discount_offers', ['id' => $offer->id]);
        $this->assertNull($selected->fresh()->discount_offer_id);
        $this->assertNull($selected->fresh()->discount_type);
        $this->assertNull($secondSelected->fresh()->discount_offer_id);
        $this->assertNull($secondSelected->fresh()->discount_type);
    }

    public function test_admin_can_edit_an_offer_and_change_it_from_category_to_product_wise(): void
    {
        $this->travelTo(Carbon::parse('2026-10-14 12:00:00'));
        $admin = $this->admin();
        [$parent, $category] = $this->categories();
        $first = $this->product('First Product', 'FIRST-001', $category);
        $second = $this->product('Second Product', 'SECOND-001', $category);

        $this->actingAs($admin)->post(route('admin.discount-offers.store'), [
            'reason' => 'Category launch offer',
            'start_date' => '2026-10-14',
            'end_date' => '2026-10-20',
            'scope_type' => 'category',
            'categories' => [$parent->id],
            'category_discounts' => [
                $parent->id => ['discount_type' => 'percentage', 'discount_value' => 10],
            ],
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $offer = DiscountOffer::firstOrFail();

        $this->get(route('admin.discount-offers.edit', $offer))
            ->assertOk()
            ->assertSee('Category launch offer')
            ->assertSee('2026-10-14');

        $this->put(route('admin.discount-offers.update', $offer), [
            'reason' => 'Selected product offer',
            'start_date' => '2026-10-14',
            'end_date' => '2026-10-25',
            'scope_type' => 'product',
            'products' => [$second->id],
            'product_discounts' => [
                $second->id => ['discount_type' => 'fixed', 'discount_value' => 250],
            ],
            'is_active' => 1,
        ])->assertRedirect(route('admin.discount-offers.index'))
            ->assertSessionHasNoErrors();

        $this->assertNull($first->fresh()->discount_offer_id);
        $this->assertSame(1000.0, $first->fresh()->final_price);
        $this->assertSame($offer->id, $second->fresh()->discount_offer_id);
        $this->assertSame(750.0, $second->fresh()->final_price);
        $this->assertSame('product', $offer->fresh()->scope_type);
        $this->assertSame('Selected product offer', $offer->fresh()->reason);
    }

    public function test_offer_validates_scope_selections_dates_and_percentage(): void
    {
        $parent = Category::create([
            'name' => 'Validation Parent',
            'slug' => 'validation-parent',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.discount-offers.store'), [
                'reason' => 'Invalid offer',
                'start_date' => '2026-10-20',
                'end_date' => '2026-10-19',
                'scope_type' => 'category',
                'categories' => [],
                'category_discounts' => [
                    $parent->id => ['discount_type' => 'percentage', 'discount_value' => 120],
                ],
                'is_active' => 1,
            ])
            ->assertSessionHasErrors(['end_date', "category_discounts.{$parent->id}.discount_value", 'categories']);

        $this->post(route('admin.discount-offers.store'), [
            'reason' => 'Decimal percentage offer',
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-21',
            'scope_type' => 'category',
            'categories' => [$parent->id],
            'category_discounts' => [
                $parent->id => ['discount_type' => 'percentage', 'discount_value' => 10.5],
            ],
            'is_active' => 1,
        ])->assertSessionHasErrors("category_discounts.{$parent->id}.discount_value");

        $this->assertDatabaseCount('discount_offers', 0);
    }

    public function test_active_offers_cannot_have_overlapping_date_ranges(): void
    {
        $this->travelTo(Carbon::parse('2026-10-14 12:00:00'));
        $admin = $this->admin();
        [, $category] = $this->categories();
        $first = $this->product('First Scheduled Product', 'SCHEDULED-001', $category);
        $second = $this->product('Second Scheduled Product', 'SCHEDULED-002', $category);

        $this->actingAs($admin)->post(route('admin.discount-offers.store'), [
            'reason' => 'Primary date range',
            'start_date' => '2026-10-14',
            'end_date' => '2026-10-20',
            'scope_type' => 'product',
            'products' => [$first->id],
            'product_discounts' => [
                $first->id => ['discount_type' => 'percentage', 'discount_value' => 10],
            ],
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $this->post(route('admin.discount-offers.store'), [
            'reason' => 'Overlapping date range',
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-25',
            'scope_type' => 'product',
            'products' => [$second->id],
            'product_discounts' => [
                $second->id => ['discount_type' => 'fixed', 'discount_value' => 100],
            ],
            'is_active' => 1,
        ])->assertSessionHasErrors('end_date');

        $this->assertDatabaseCount('discount_offers', 1);
    }

    public function test_non_overlapping_offer_is_activated_by_the_price_sync(): void
    {
        $this->travelTo(Carbon::parse('2026-10-14 12:00:00'));
        $admin = $this->admin();
        [, $category] = $this->categories();
        $product = $this->product('Back To Back Product', 'BACK-TO-BACK-001', $category);

        foreach ([
            ['Current offer', '2026-10-14', '2026-10-15', 100],
            ['Next offer', '2026-10-16', '2026-10-18', 200],
        ] as [$reason, $start, $end, $discount]) {
            $this->actingAs($admin)->post(route('admin.discount-offers.store'), [
                'reason' => $reason,
                'start_date' => $start,
                'end_date' => $end,
                'scope_type' => 'product',
                'products' => [$product->id],
                'product_discounts' => [
                    $product->id => ['discount_type' => 'fixed', 'discount_value' => $discount],
                ],
                'is_active' => 1,
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame(900.0, $product->fresh()->final_price);
        $this->assertSame('Current offer', $product->fresh()->discountOffer->reason);

        $this->travelTo(Carbon::parse('2026-10-16 00:01:00'));
        $this->artisan('products:sync-prices')->expectsOutput('Synchronized 1 product price.')->assertExitCode(0);

        $this->assertSame(800.0, $product->fresh()->final_price);
        $this->assertSame('Next offer', $product->fresh()->discountOffer->reason);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function categories(): array
    {
        $parent = Category::create([
            'name' => 'Offer Parent',
            'slug' => 'offer-parent',
            'is_active' => true,
        ]);
        $child = Category::create([
            'name' => 'Offer Child',
            'slug' => 'offer-child',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        return [$parent, $child];
    }

    private function product(string $name, string $sku, Category $category): Product
    {
        return Product::create([
            'name' => $name,
            'slug' => str($name)->slug(),
            'sku' => $sku,
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
        ]);
    }
}

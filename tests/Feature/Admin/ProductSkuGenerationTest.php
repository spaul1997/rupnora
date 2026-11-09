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

class ProductSkuGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_generates_independent_serials_for_each_sku_prefix(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        [$parent, $subcategory, $jewelleryType, $metalType, $collection] = $this->catalogData();

        $this->actingAs($admin);

        foreach ([
            ['name' => 'First TF Earrings', 'sku' => 'TF', 'expected' => 'RA-EAR-TF-01'],
            ['name' => 'First Plain Earrings', 'sku' => '', 'expected' => 'RA-EAR-01'],
            ['name' => 'Second TF Earrings', 'sku' => 'tf', 'expected' => 'RA-EAR-TF-02'],
            ['name' => 'Second Plain Earrings', 'sku' => '', 'expected' => 'RA-EAR-02'],
        ] as $case) {
            $response = $this->post(route('admin.products.store'), [
                'name' => $case['name'],
                'sku' => $case['sku'],
                'auto_generate_sku' => '1',
                'parent_category_id' => $parent->id,
                'category_id' => $subcategory->id,
                'collection' => [$collection->slug],
                'jewellery_type' => $jewelleryType->name,
                'metal_type' => $metalType->name,
                'mrp' => 1500,
                'selling_price' => 1200,
                'stock_quantity' => 2,
            ]);

            $response->assertRedirect()->assertSessionHasNoErrors();
            $this->assertDatabaseHas('products', [
                'name' => $case['name'],
                'sku' => $case['expected'],
            ]);
        }
    }

    public function test_create_form_explains_the_automatic_sku_format(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->catalogData();

        $this->actingAs($admin)
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('SKU Code (Optional)')
            ->assertSee('name="auto_generate_sku" value="1"', false)
            ->assertSee('RA-EAR-01');
    }

    public function test_only_the_middle_sku_code_can_be_changed_when_editing(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        [$parent, $subcategory, $jewelleryType, $metalType, $collection] = $this->catalogData();
        $product = $this->product($subcategory, $jewelleryType, $metalType, $collection, 'RN-EAR-TCH-0001');

        $this->actingAs($admin)
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('RN-EAR-')
            ->assertSee('name="sku_code"', false)
            ->assertSee('value="TCH"', false)
            ->assertSee('0001')
            ->assertSee('Changing the category generates a new prefix and serial when you save.');

        $response = $this->put(route('admin.products.update', $product), [
            ...$this->updateData($parent, $subcategory, $jewelleryType, $metalType, $collection),
            'sku' => 'TAMPERED-SKU-9999',
            'sku_code' => 'new',
        ]);

        $response->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('RN-EAR-NEW-0001', $product->fresh()->sku);
    }

    public function test_editing_the_sku_code_cannot_create_a_duplicate_sku(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        [$parent, $subcategory, $jewelleryType, $metalType, $collection] = $this->catalogData();
        $product = $this->product($subcategory, $jewelleryType, $metalType, $collection, 'RN-EAR-TCH-0001');
        $this->product($subcategory, $jewelleryType, $metalType, $collection, 'RN-EAR-NEW-0001', 'Existing New Code');

        $this->actingAs($admin)
            ->from(route('admin.products.edit', $product))
            ->put(route('admin.products.update', $product), [
                ...$this->updateData($parent, $subcategory, $jewelleryType, $metalType, $collection),
                'sku_code' => 'NEW',
            ])
            ->assertRedirect(route('admin.products.edit', $product))
            ->assertSessionHasErrors('sku_code');

        $this->assertSame('RN-EAR-TCH-0001', $product->fresh()->sku);
    }

    public function test_changing_the_main_category_generates_the_next_sku_and_preserves_the_middle_code(): void
    {
        [$parent, $subcategory, $jewelleryType, $metalType, $collection] = $this->catalogData();
        [$newParent, $newSubcategory] = $this->ringCategories();
        $product = $this->product($subcategory, $jewelleryType, $metalType, $collection, 'RA-EAR-TCH-09');
        $this->product($newSubcategory, $jewelleryType, $metalType, $collection, 'RA-RIN-TCH-07', 'Existing Ring');

        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]))
            ->put(route('admin.products.update', $product), [
                ...$this->updateData($newParent, $newSubcategory, $jewelleryType, $metalType, $collection),
                'sku' => 'TAMPERED-SKU-9999',
            ])
            ->assertRedirect(route('admin.products.edit', $product))
            ->assertSessionHasNoErrors();

        $this->assertSame('RA-RIN-TCH-08', $product->fresh()->sku);
        $this->assertSame($newSubcategory->id, $product->fresh()->category_id);

        $this->get(route('admin.products.edit', $product))->assertOk()
            ->assertSee('RA-RIN-')
            ->assertSee('value="TCH"', false);
    }

    public function test_category_and_code_can_change_together_even_if_the_old_category_has_that_code_and_serial(): void
    {
        [$parent, $subcategory, $jewelleryType, $metalType, $collection] = $this->catalogData();
        [$newParent, $newSubcategory] = $this->ringCategories();
        $product = $this->product($subcategory, $jewelleryType, $metalType, $collection, 'RA-EAR-TCH-01');
        $this->product($subcategory, $jewelleryType, $metalType, $collection, 'RA-EAR-NEW-01', 'Existing Earring');
        $this->product($newSubcategory, $jewelleryType, $metalType, $collection, 'RA-RIN-NEW-02', 'Existing Ring');

        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]))
            ->put(route('admin.products.update', $product), [
                ...$this->updateData($newParent, $newSubcategory, $jewelleryType, $metalType, $collection),
                'sku_code' => 'new',
            ])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('RA-RIN-NEW-03', $product->fresh()->sku);
    }

    public function test_changing_category_generates_a_sku_without_a_middle_code_when_it_is_blank(): void
    {
        [$parent, $subcategory, $jewelleryType, $metalType, $collection] = $this->catalogData();
        [$newParent, $newSubcategory] = $this->ringCategories();
        $product = $this->product($subcategory, $jewelleryType, $metalType, $collection, 'RA-EAR-09');

        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]))
            ->put(route('admin.products.update', $product), [
                ...$this->updateData($newParent, $newSubcategory, $jewelleryType, $metalType, $collection),
                'sku_code' => '',
            ])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('RA-RIN-01', $product->fresh()->sku);
    }

    public function test_changing_only_the_subcategory_preserves_the_sku(): void
    {
        [$parent, $subcategory, $jewelleryType, $metalType, $collection] = $this->catalogData();
        $newSubcategory = Category::query()->create([
            'name' => 'Hoops', 'slug' => 'sku-test-hoops', 'parent_id' => $parent->id, 'is_active' => true,
        ]);
        $product = $this->product($subcategory, $jewelleryType, $metalType, $collection, 'RA-EAR-TCH-09');

        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]))
            ->put(route('admin.products.update', $product), [
                ...$this->updateData($parent, $newSubcategory, $jewelleryType, $metalType, $collection),
                'sku_code' => 'TCH',
            ])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('RA-EAR-TCH-09', $product->fresh()->sku);
        $this->assertSame($newSubcategory->id, $product->fresh()->category_id);
    }

    public function test_changing_category_can_generate_a_new_sku_for_a_legacy_product(): void
    {
        [$parent, $subcategory, $jewelleryType, $metalType, $collection] = $this->catalogData();
        [$newParent, $newSubcategory] = $this->ringCategories();
        $product = $this->product($subcategory, $jewelleryType, $metalType, $collection, 'LEGACY-SKU');

        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]))
            ->put(route('admin.products.update', $product), $this->updateData(
                $newParent, $newSubcategory, $jewelleryType, $metalType, $collection
            ))
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('RA-RIN-01', $product->fresh()->sku);
    }

    private function ringCategories(): array
    {
        $parent = Category::query()->create(['name' => 'Rings', 'slug' => 'sku-test-rings', 'is_active' => true]);
        $subcategory = Category::query()->create([
            'name' => 'Band Rings', 'slug' => 'sku-test-band-rings', 'parent_id' => $parent->id, 'is_active' => true,
        ]);

        return [$parent, $subcategory];
    }

    private function catalogData(): array
    {
        $parent = Category::query()->firstOrCreate(
            ['slug' => 'earrings'],
            ['name' => 'Earrings', 'is_active' => true]
        );
        $subcategory = Category::query()->firstOrCreate(
            ['slug' => 'stud'],
            ['name' => 'Stud', 'parent_id' => $parent->id, 'is_active' => true]
        );
        $jewelleryType = JewelleryType::query()->firstOrCreate(
            ['slug' => 'stud'],
            ['name' => 'Stud', 'is_active' => true]
        );
        $metalType = MetalType::query()->firstOrCreate(
            ['slug' => 'gold'],
            ['name' => 'Gold', 'is_active' => true]
        );
        $collection = JewelleryCollection::query()->firstOrCreate(
            ['slug' => 'daily-wear'],
            ['name' => 'Daily Wear', 'is_active' => true]
        );

        return [$parent, $subcategory, $jewelleryType, $metalType, $collection];
    }

    private function product(Category $subcategory, JewelleryType $jewelleryType, MetalType $metalType, JewelleryCollection $collection, string $sku, string $name = 'Editable SKU Product'): Product
    {
        return Product::query()->create([
            'name' => $name,
            'slug' => str($name.'-'.$sku)->slug(),
            'sku' => $sku,
            'category_id' => $subcategory->id,
            'collection' => json_encode([$collection->slug]),
            'jewellery_type' => $jewelleryType->name,
            'metal_type' => $metalType->name,
            'mrp' => 1500,
            'selling_price' => 1200,
            'stock_quantity' => 2,
        ]);
    }

    private function updateData(Category $parent, Category $subcategory, JewelleryType $jewelleryType, MetalType $metalType, JewelleryCollection $collection): array
    {
        return [
            'name' => 'Editable SKU Product',
            'parent_category_id' => $parent->id,
            'category_id' => $subcategory->id,
            'collection' => [$collection->slug],
            'jewellery_type' => $jewelleryType->name,
            'metal_type' => $metalType->name,
            'mrp' => 1500,
            'selling_price' => 1200,
            'stock_quantity' => 2,
        ];
    }
}

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
            ->assertSee('The prefix and serial number are read-only.');

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

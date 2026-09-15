<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\JewelleryCollection;
use App\Models\JewelleryType;
use App\Models\MetalType;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetalTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_metal_type_master_pages(): void
    {
        $type = $this->createMetalType();

        $this->actingAs($this->createAdmin())
            ->get(route('admin.metal-types.index'))
            ->assertOk()
            ->assertViewIs('admin.metal-types.index')
            ->assertViewHas('types', fn ($types) => $types->contains('id', $type->id));

        $this->actingAs($this->createAdmin())
            ->get(route('admin.metal-types.create'))
            ->assertOk()
            ->assertViewIs('admin.metal-types.create');
    }

    public function test_admin_can_create_a_metal_type_with_a_generated_slug(): void
    {
        $response = $this->actingAs($this->createAdmin())->post(route('admin.metal-types.store'), [
            'name' => 'Recycled 18K Gold',
            'slug' => '',
            'description' => 'Responsibly sourced recycled gold.',
            'sort_order' => 7,
            'is_active' => '1',
        ]);

        $response
            ->assertRedirect(route('admin.metal-types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('metal_types', [
            'name' => 'Recycled 18K Gold',
            'slug' => 'recycled-18k-gold',
            'description' => 'Responsibly sourced recycled gold.',
            'sort_order' => 7,
            'is_active' => true,
        ]);
    }

    public function test_duplicate_metal_type_names_are_rejected(): void
    {
        $type = $this->createMetalType();

        $response = $this->actingAs($this->createAdmin())
            ->from(route('admin.metal-types.create'))
            ->post(route('admin.metal-types.store'), [
                'name' => $type->name,
                'slug' => 'palladium-alternative',
                'description' => 'A duplicate master name with a different slug.',
                'sort_order' => 11,
                'is_active' => '1',
            ]);

        $response
            ->assertRedirect(route('admin.metal-types.create'))
            ->assertSessionHasErrors('name');

        $this->assertSame(1, MetalType::query()->where('name', $type->name)->count());
        $this->assertDatabaseMissing('metal_types', ['slug' => 'palladium-alternative']);
    }

    public function test_admin_can_update_a_metal_type_and_assigned_products_follow_its_name(): void
    {
        $type = $this->createMetalType();
        $product = $this->createProductUsing($type);

        $response = $this->actingAs($this->createAdmin())->put(route('admin.metal-types.update', $type), [
            'name' => 'Palladium Alloy',
            'slug' => 'Premium Palladium Alloy',
            'description' => 'A premium palladium alloy.',
            'sort_order' => 3,
            'is_active' => '1',
        ]);

        $response
            ->assertRedirect(route('admin.metal-types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('metal_types', [
            'id' => $type->id,
            'name' => 'Palladium Alloy',
            'slug' => 'premium-palladium-alloy',
            'description' => 'A premium palladium alloy.',
            'sort_order' => 3,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'metal_type' => 'Palladium Alloy',
        ]);
    }

    public function test_master_data_seeding_preserves_an_admin_renamed_default_metal_type(): void
    {
        $type = MetalType::query()->where('slug', 'gold')->firstOrFail();

        $this->actingAs($this->createAdmin())
            ->put(route('admin.metal-types.update', $type), [
                'name' => 'Heritage Gold',
                'slug' => 'gold',
                'description' => 'The shop-specific display name for gold.',
                'sort_order' => 21,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.metal-types.index'))
            ->assertSessionHas('success');

        $this->seed(MasterDataSeeder::class);

        $this->assertDatabaseHas('metal_types', [
            'id' => $type->id,
            'name' => 'Heritage Gold',
            'slug' => 'gold',
            'description' => 'The shop-specific display name for gold.',
            'sort_order' => 21,
            'is_active' => true,
        ]);
        $this->assertSame(1, MetalType::query()->where('slug', 'gold')->count());
    }

    public function test_master_data_seeding_includes_the_requested_metal_types(): void
    {
        $this->seed(MasterDataSeeder::class);

        foreach ([
            'Brass',
            'Copper',
            'Alloy',
            'Zinc Alloy',
            'Stainless Steel',
            'German Silver',
            'Sterling Silver / 925 Silver',
            'Iron',
            'Aluminium',
            'Titanium',
            'Nickel Alloy',
            'Pewter',
            'Mixed Metal',
            'Gold-Plated Metal',
            'Silver-Plated Metal',
            'Rose Gold-Plated Metal',
            'Rhodium-Plated Metal',
            'Oxidised Metal',
            'Antique-Finish Metal',
            'Other',
        ] as $metalType) {
            $this->assertDatabaseHas('metal_types', [
                'name' => $metalType,
                'is_active' => true,
            ]);
        }
    }

    public function test_admin_can_toggle_a_metal_type_status(): void
    {
        $type = $this->createMetalType(['is_active' => true]);

        $response = $this->actingAs($this->createAdmin())
            ->from(route('admin.metal-types.index'))
            ->patch(route('admin.metal-types.toggle-active', $type));

        $response
            ->assertRedirect(route('admin.metal-types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('metal_types', [
            'id' => $type->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_an_unassigned_metal_type(): void
    {
        $type = $this->createMetalType();

        $response = $this->actingAs($this->createAdmin())
            ->delete(route('admin.metal-types.destroy', $type));

        $response
            ->assertRedirect(route('admin.metal-types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('metal_types', ['id' => $type->id]);
    }

    public function test_admin_cannot_delete_a_metal_type_assigned_to_a_product(): void
    {
        $type = $this->createMetalType();
        $this->createProductUsing($type);

        $response = $this->actingAs($this->createAdmin())
            ->from(route('admin.metal-types.index'))
            ->delete(route('admin.metal-types.destroy', $type));

        $response
            ->assertRedirect(route('admin.metal-types.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('metal_types', ['id' => $type->id]);
    }

    public function test_product_creation_rejects_an_inactive_metal_type(): void
    {
        $inactiveType = $this->createMetalType([
            'name' => 'Inactive Titanium',
            'slug' => 'inactive-titanium',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->createAdmin())
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $this->validProductData($inactiveType->name));

        $response
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('metal_type');

        $this->assertDatabaseMissing('products', ['sku' => 'METAL-TYPE-TEST-001']);
    }

    public function test_product_creation_saves_the_updated_jewellery_information_fields(): void
    {
        $type = MetalType::query()->where('name', 'Brass')->firstOrFail();
        $productData = array_merge($this->validProductData($type->name), [
            'finish_plating' => 'Gold-Plated Metal',
            'metal_colour' => 'Rose Gold',
            'gemstone_type' => 'American Diamond',
            'gemstone_colour' => 'White',
            'occasion' => 'party',
            'gender' => 'Unisex',
            'gross_weight' => '12.345',
            'is_adjustable' => '1',
            'is_water_resistant' => '1',
            'is_return_available' => '1',
            'is_refund_available' => '1',
        ]);

        $this->actingAs($this->createAdmin())
            ->post(route('admin.products.store'), $productData)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'sku' => $productData['sku'],
            'metal_type' => 'Brass',
            'finish_plating' => 'Gold-Plated Metal',
            'metal_colour' => 'Rose Gold',
            'gemstone_type' => 'American Diamond',
            'gemstone_colour' => 'White',
            'occasion' => 'party',
            'gender' => 'Unisex',
            'gross_weight' => '12.345',
            'is_adjustable' => true,
            'is_water_resistant' => true,
            'is_return_available' => true,
            'is_refund_available' => true,
        ]);
    }

    public function test_unrelated_product_updates_accept_the_currently_assigned_inactive_metal_type(): void
    {
        $type = $this->createMetalType();
        $productData = $this->validProductData($type->name);
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), $productData)
            ->assertSessionDoesntHaveErrors();

        $product = Product::query()->where('sku', $productData['sku'])->firstOrFail();
        $type->update(['is_active' => false]);
        $productData['name'] = 'Updated Metal Type Validation Product';

        $response = $this->actingAs($admin)
            ->from(route('admin.products.edit', $product))
            ->put(route('admin.products.update', $product), $productData);

        $response
            ->assertRedirect(route('admin.products.edit', $product))
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Metal Type Validation Product',
            'metal_type' => $type->name,
        ]);
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function createMetalType(array $attributes = []): MetalType
    {
        return MetalType::query()->create(array_merge([
            'name' => 'Palladium',
            'slug' => 'palladium',
            'description' => 'A durable white metal.',
            'sort_order' => 10,
            'is_active' => true,
        ], $attributes));
    }

    private function createProductUsing(MetalType $type): Product
    {
        $category = Category::query()->create([
            'name' => 'Test Rings',
            'slug' => 'test-rings',
            'is_active' => true,
        ]);

        return Product::query()->create([
            'name' => 'Palladium Test Ring',
            'slug' => 'palladium-test-ring',
            'sku' => 'PALLADIUM-TEST-001',
            'category_id' => $category->id,
            'jewellery_type' => 'Ring',
            'metal_type' => $type->name,
            'mrp' => 1500,
            'selling_price' => 1200,
            'stock_quantity' => 2,
        ]);
    }

    private function validProductData(string $metalType): array
    {
        $parent = Category::query()->create([
            'name' => 'Validation Parent',
            'slug' => 'validation-parent',
            'is_active' => true,
        ]);
        $subcategory = Category::query()->create([
            'name' => 'Validation Child',
            'slug' => 'validation-child',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);
        $jewelleryType = JewelleryType::query()->create([
            'name' => 'Validation Style',
            'slug' => 'validation-style',
            'is_active' => true,
        ]);
        $collection = JewelleryCollection::query()->create([
            'name' => 'Validation Collection',
            'slug' => 'validation-collection',
            'is_active' => true,
        ]);

        return [
            'name' => 'Metal Type Validation Product',
            'sku' => 'METAL-TYPE-TEST-001',
            'parent_category_id' => $parent->id,
            'category_id' => $subcategory->id,
            'collection' => [$collection->slug],
            'jewellery_type' => $jewelleryType->name,
            'metal_type' => $metalType,
            'mrp' => 1500,
            'selling_price' => 1200,
            'stock_quantity' => 2,
        ];
    }
}

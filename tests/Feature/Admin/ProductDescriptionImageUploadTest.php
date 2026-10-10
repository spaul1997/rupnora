<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductDescriptionImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_upload_an_image_for_the_product_description_editor(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin)->postJson(
            route('admin.products.description-images.store'),
            ['upload' => UploadedFile::fake()->image('description.jpg', 1200, 800)]
        );

        $response
            ->assertOk()
            ->assertJsonPath('uploaded', 1)
            ->assertJsonStructure(['uploaded', 'fileName', 'url']);

        $files = Storage::disk('public')->allFiles('products/descriptions');

        $this->assertNotEmpty($files);
        $this->assertNotEmpty(array_filter($files, fn (string $file) => str_ends_with($file, '-lg.webp')));
    }

    public function test_description_image_upload_rejects_non_images_and_non_admins(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $this->actingAs($admin)
            ->postJson(route('admin.products.description-images.store'), [
                'upload' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('uploaded', 0);

        $this->actingAs($customer)
            ->postJson(route('admin.products.description-images.store'), [
                'upload' => UploadedFile::fake()->image('description.jpg'),
            ])
            ->assertForbidden();

        Storage::disk('public')->assertDirectoryEmpty('products/descriptions');
    }
}

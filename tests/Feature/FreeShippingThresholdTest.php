<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class FreeShippingThresholdTest extends TestCase
{
    use DatabaseMigrations;

    public function test_admin_can_update_the_free_shipping_threshold_shown_in_the_header(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->assertSame('2999.00', WebsiteSetting::current()->free_shipping_threshold);

        $this->actingAs($admin)->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Free Shipping Order Threshold (INR)');

        $this->put(route('admin.settings.update'), [
            'free_shipping_threshold' => '4999.50',
            'express_delivery_charge' => '199',
            'cod_order_limit' => '100000',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('website_settings', [
            'id' => 1,
            'free_shipping_threshold' => '4999.50',
        ]);

        $this->get(route('home'))->assertOk()
            ->assertSee('Free Shipping on Orders Above Rs. 4,999.50');
    }

    public function test_invalid_free_shipping_threshold_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        foreach ([null, -1, 'invalid', '1.234', '100000000'] as $threshold) {
            $this->put(route('admin.settings.update'), [
                'free_shipping_threshold' => $threshold,
                'express_delivery_charge' => '199',
                'cod_order_limit' => '100000',
            ])->assertSessionHasErrors('free_shipping_threshold');
        }

        $this->assertSame('2999.00', WebsiteSetting::current()->free_shipping_threshold);
    }
}

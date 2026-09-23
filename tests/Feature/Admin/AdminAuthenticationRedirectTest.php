<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_visiting_login_is_redirected_to_the_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_authenticated_non_admin_visiting_admin_login_is_redirected_home(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->get(route('admin.login'))
            ->assertRedirect(route('home'));
    }
}

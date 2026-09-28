<?php

namespace Tests\Feature;

use App\Models\Influencer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInfluencerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_and_filter_influencers(): void
    {
        $admin = $this->admin();
        $influencer = $this->influencer();

        $this->actingAs($admin)
            ->get(route('admin.influencers.index', ['status' => 'new', 'platform' => 'instagram']))
            ->assertOk()
            ->assertSee($influencer->reference_no)
            ->assertSee('Meera Shah');

        $this->actingAs($admin)
            ->get(route('admin.influencers.index', ['platform' => 'youtube']))
            ->assertOk()
            ->assertDontSee($influencer->reference_no);
    }

    public function test_admin_can_open_all_influencer_management_pages(): void
    {
        $admin = $this->admin();
        $influencer = $this->influencer();

        $this->actingAs($admin)
            ->get(route('admin.influencers.create'))
            ->assertOk()
            ->assertSee('Add Influencer');

        $this->actingAs($admin)
            ->get(route('admin.influencers.show', $influencer))
            ->assertOk()
            ->assertSee($influencer->reference_no)
            ->assertSee('Application Message');

        $this->actingAs($admin)
            ->get(route('admin.influencers.edit', $influencer))
            ->assertOk()
            ->assertSee('Edit Influencer');
    }

    public function test_admin_can_approve_and_activate_an_influencer(): void
    {
        $admin = $this->admin();
        $influencer = $this->influencer();

        $this->actingAs($admin)
            ->patch(route('admin.influencers.update-status', $influencer), ['status' => 'approved'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $influencer->refresh();
        $this->assertSame('approved', $influencer->status);
        $this->assertTrue($influencer->is_active);
        $this->assertNotNull($influencer->approved_at);

        $this->actingAs($admin)
            ->patch(route('admin.influencers.toggle-active', $influencer))
            ->assertRedirect();

        $this->assertFalse($influencer->refresh()->is_active);
    }

    public function test_admin_can_update_partnership_details(): void
    {
        $admin = $this->admin();
        $influencer = $this->influencer(['status' => 'approved', 'is_active' => true]);

        $this->actingAs($admin)
            ->put(route('admin.influencers.update', $influencer), [
                'full_name' => 'Meera Shah',
                'email' => 'meera@example.com',
                'phone' => '+91 98765 12345',
                'location' => 'Mumbai, Maharashtra',
                'primary_platform' => 'instagram',
                'social_handle' => '@meerastyles',
                'profile_url' => 'https://www.instagram.com/meerastyles',
                'followers_count' => 50000,
                'content_niche' => 'Jewellery and slow fashion',
                'portfolio_url' => 'https://meera.example.com',
                'message' => 'I create thoughtful styling content for an audience that values craftsmanship.',
                'status' => 'approved',
                'commission_rate' => '12.50',
                'coupon_code' => 'meera12',
                'admin_notes' => 'Strong engagement and premium visual style.',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.influencers.show', $influencer))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('influencers', [
            'id' => $influencer->id,
            'followers_count' => 50000,
            'commission_rate' => 12.50,
            'coupon_code' => 'MEERA12',
            'is_active' => true,
        ]);
    }

    public function test_non_admin_cannot_manage_influencers(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $this->actingAs($customer)
            ->get(route('admin.influencers.index'))
            ->assertForbidden();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function influencer(array $overrides = []): Influencer
    {
        return Influencer::create(array_merge([
            'reference_no' => 'RPN-INF-2026-TEST0001',
            'full_name' => 'Meera Shah',
            'email' => 'meera@example.com',
            'phone' => '+91 98765 12345',
            'location' => 'Mumbai, Maharashtra',
            'primary_platform' => 'instagram',
            'social_handle' => '@meerastyles',
            'profile_url' => 'https://www.instagram.com/meerastyles',
            'followers_count' => 42000,
            'content_niche' => 'Jewellery and slow fashion',
            'portfolio_url' => 'https://meera.example.com',
            'message' => 'I create thoughtful styling content for an audience that values craftsmanship.',
            'status' => 'new',
            'is_active' => false,
        ], $overrides));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Influencer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InfluencerPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_influencer_page_contains_the_creator_application(): void
    {
        $this->get(route('influencer'))
            ->assertOk()
            ->assertSee('Creator Application')
            ->assertSee(route('influencer.apply'), escape: false);
    }

    public function test_creator_can_submit_an_influencer_application(): void
    {
        $response = $this->post(route('influencer.apply'), [
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
            'terms' => '1',
        ]);

        $response
            ->assertRedirect(route('influencer').'#apply')
            ->assertSessionHas('influencer_application_success');

        $influencer = Influencer::firstOrFail();

        $this->assertSame('Meera Shah', $influencer->full_name);
        $this->assertSame('new', $influencer->status);
        $this->assertFalse($influencer->is_active);
        $this->assertStringStartsWith('RPN-INF-', $influencer->reference_no);
    }

    public function test_application_rejects_an_unknown_social_platform(): void
    {
        $this->from(route('influencer'))->post(route('influencer.apply'), [
            'full_name' => 'Meera Shah',
            'email' => 'meera@example.com',
            'phone' => '+91 98765 12345',
            'location' => 'Mumbai, Maharashtra',
            'primary_platform' => 'unknown-network',
            'social_handle' => '@meerastyles',
            'followers_count' => 42000,
            'content_niche' => 'Jewellery and slow fashion',
            'message' => 'I create thoughtful styling content for an audience that values craftsmanship.',
            'terms' => '1',
        ])
            ->assertRedirect(route('influencer'))
            ->assertSessionHasErrors(['primary_platform'], errorBag: 'influencerApplication');

        $this->assertDatabaseCount('influencers', 0);
    }

    public function test_home_influencer_button_links_to_influencer_page(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('influencer'), escape: false);
    }
}

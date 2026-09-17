<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InfluencerPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_influencer_page_is_available_and_marked_as_coming_soon(): void
    {
        $this->get(route('influencer'))
            ->assertOk()
            ->assertSee('Coming Soon')
            ->assertSee('Rupnora Partner Program');
    }

    public function test_home_influencer_button_links_to_influencer_page(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('influencer'), escape: false);
    }
}

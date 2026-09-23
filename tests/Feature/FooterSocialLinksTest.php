<?php

namespace Tests\Feature;

use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterSocialLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_only_displays_configured_website_setting_social_links(): void
    {
        WebsiteSetting::current()->update([
            'facebook' => 'https://facebook.com/rupnora',
            'instagram' => null,
            'linkedin' => null,
            'youtube' => 'https://youtube.com/@rupnora',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('href="https://facebook.com/rupnora"', false)
            ->assertSee('aria-label="Facebook"', false)
            ->assertSee('href="https://youtube.com/@rupnora"', false)
            ->assertSee('aria-label="YouTube"', false)
            ->assertDontSee('aria-label="Instagram"', false)
            ->assertDontSee('aria-label="LinkedIn"', false);
    }

    public function test_footer_hides_the_social_section_when_all_links_are_empty(): void
    {
        WebsiteSetting::current()->update([
            'facebook' => null,
            'instagram' => null,
            'linkedin' => null,
            'youtube' => null,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('aria-label="Social media links"', false);
    }
}

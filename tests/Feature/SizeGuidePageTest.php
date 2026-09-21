<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SizeGuidePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_size_guide_page_is_available(): void
    {
        $this->get(route('size-guide'))
            ->assertOk()
            ->assertSee('Jewellery Size Guide')
            ->assertSee('Ring size');
    }

    public function test_footer_links_to_the_size_guide(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('size-guide'), escape: false);
    }
}

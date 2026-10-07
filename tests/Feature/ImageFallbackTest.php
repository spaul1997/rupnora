<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ImageFallbackTest extends TestCase
{
    public function test_optimized_image_renders_the_placeholder_when_the_source_is_blank(): void
    {
        $html = Blade::render(
            '<x-ui.optimized-image :src="$src" alt="Missing product image" class="h-20 w-20" />',
            ['src' => null]
        );

        $this->assertStringContainsString('<img', $html);
        $this->assertStringContainsString('/images/image-placeholder.svg', $html);
        $this->assertStringContainsString('data-image-fallback-src=', $html);
        $this->assertStringContainsString('alt="Missing product image"', $html);
    }

    public function test_optimized_image_exposes_a_fallback_for_a_broken_source(): void
    {
        $html = Blade::render(
            '<x-ui.optimized-image :src="$src" alt="Product image" />',
            ['src' => 'https://example.test/missing-product.webp']
        );

        $this->assertStringContainsString('src="https://example.test/missing-product.webp"', $html);
        $this->assertStringContainsString('/images/image-placeholder.svg', $html);
    }
}

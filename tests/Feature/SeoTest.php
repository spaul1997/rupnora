<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_have_indexing_canonical_social_and_structured_metadata(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('<html lang="en-IN"', false)
            ->assertSee('<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">', false)
            ->assertSee('<link rel="canonical" href="'.route('home').'">', false)
            ->assertSee('<meta property="og:site_name" content="Rupnora">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('<script type="application/ld+json">', false)
            ->assertSee('"@type":["Organization","OnlineStore"]', false)
            ->assertSee('"@type":"WebSite"', false);
    }

    public function test_private_transactional_and_search_pages_are_not_indexed(): void
    {
        $this->get(route('search', ['q' => 'ring']))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertDontSee('<script type="application/ld+json">', false);

        $this->get(route('cart'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_product_and_category_metadata_use_admin_seo_fields_and_product_schema(): void
    {
        [$category, $product] = $this->catalogProduct();

        $this->get(route('category.show', $category->slug))
            ->assertOk()
            ->assertSee('<title>Fine Earrings Online — Rupnora</title>', false)
            ->assertSee('<meta name="description" content="Shop our carefully selected fine earrings.">', false)
            ->assertSee('<h1 class="font-display text-3xl sm:text-5xl">SEO Earrings</h1>', false);

        $this->get(route('product.show', $product->slug))
            ->assertOk()
            ->assertSee('<title>SEO Gold Stud Earrings — Rupnora</title>', false)
            ->assertSee('<meta name="description" content="Shop lightweight gold stud earrings from Rupnora.">', false)
            ->assertSee('<meta property="og:type" content="product">', false)
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"priceCurrency":"INR"', false)
            ->assertSee('"availability":"https://schema.org/InStock"', false)
            ->assertSee('"@type":"BreadcrumbList"', false);

        $this->get(route('products.show', $product->slug))
            ->assertRedirect(route('product.show', $product->slug))
            ->assertStatus(301);
    }

    public function test_sitemap_and_robots_expose_only_canonical_discoverable_urls(): void
    {
        [$category, $product] = $this->catalogProduct();
        Product::query()->create([
            'name' => 'Hidden Product',
            'slug' => 'hidden-product',
            'sku' => 'SEO-HIDDEN-001',
            'category_id' => $category->id,
            'jewellery_type' => 'Earrings',
            'metal_type' => 'Gold',
            'mrp' => 1000,
            'selling_price' => 900,
            'stock_quantity' => 1,
            'is_active' => false,
        ]);

        $sitemap = $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('category.show', $category->slug), false)
            ->assertSee(route('product.show', $product->slug), false)
            ->assertDontSee(route('product.show', 'hidden-product'), false);

        $this->assertNotFalse(simplexml_load_string($sitemap->getContent()));

        $this->get(route('robots'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /admin/', false)
            ->assertSee('Sitemap: '.route('sitemap'), false);
    }

    private function catalogProduct(): array
    {
        $category = Category::query()->create([
            'name' => 'SEO Earrings',
            'slug' => 'seo-earrings',
            'description' => 'Discover earrings designed for every day.',
            'meta_title' => 'Fine Earrings Online',
            'meta_description' => 'Shop our carefully selected fine earrings.',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'name' => 'Gold Stud Earrings',
            'slug' => 'gold-stud-earrings-seo',
            'sku' => 'SEO-EAR-001',
            'category_id' => $category->id,
            'brand' => 'Rupnora',
            'jewellery_type' => 'Earrings',
            'metal_type' => 'Gold',
            'mrp' => 1500,
            'selling_price' => 1200,
            'stock_quantity' => 2,
            'meta_title' => 'SEO Gold Stud Earrings',
            'meta_description' => 'Shop lightweight gold stud earrings from Rupnora.',
            'is_active' => true,
        ]);

        return [$category, $product];
    }
}

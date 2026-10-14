<?php

namespace App\Http\Controllers;

use App\Models\HomeBanner;
use App\Support\Catalog;
use App\Support\StorefrontCatalog;

class HomeController extends Controller
{
    public function __invoke()
    {
        $heroSlides = HomeBanner::query()
            ->active()
            ->orderBy('sort_order')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (HomeBanner $banner) => [
                'eyebrow' => $banner->eyebrow,
                'heading' => $banner->heading,
                'subheading' => $banner->subheading,
                'primaryLabel' => $banner->primary_label,
                'primaryUrl' => $banner->primary_url,
                'secondaryLabel' => $banner->secondary_label,
                'secondaryUrl' => $banner->secondary_url,
                'image' => $banner->image_url ?: $banner->mobile_image_url,
                'mobileImage' => $banner->mobile_image_url,
            ])
            ->all();

        return view('pages.home', [
            'title' => null,
            'heroSlides' => $heroSlides ?: $this->defaultHeroSlides(),
            'parentCategories' => StorefrontCatalog::topLevelCategories(),
            'topSubcategories' => StorefrontCatalog::topSubcategories(),
            'categories' => StorefrontCatalog::categories(),
            'collections' => StorefrontCatalog::collections(),
            'jewelleryTypes' => StorefrontCatalog::topJewelleryTypes(),
            'newArrivals' => StorefrontCatalog::newArrivals(inStockOnly: true),
            'bestSellers' => StorefrontCatalog::bestSellers(),
            'randomInStockProducts' => StorefrontCatalog::randomInStockProducts(),
            'recipients' => Catalog::recipients(),
            'reviews' => Catalog::reviews(),
        ]);
    }

    protected function defaultHeroSlides(): array
    {
        return [
            [
                'eyebrow' => 'Premium Fashion Jewellery',
                'heading' => 'Style Made to Be Yours',
                'subheading' => 'Discover contemporary pieces designed for everyday expression, memorable gifts and every celebration in between.',
                'primaryLabel' => 'Shop Collections',
                'primaryUrl' => route('collections.index'),
                'secondaryLabel' => 'Explore New Arrivals',
                'secondaryUrl' => route('new-arrivals'),
                'image' => null,
                'mobileImage' => null,
            ],
            [
                'eyebrow' => 'The Celebration Edit',
                'heading' => 'Statement Style for Every Celebration',
                'subheading' => 'Explore wedding and occasion jewellery designed to bring colour, confidence and sparkle to every look.',
                'primaryLabel' => 'Shop Occasion Styles',
                'primaryUrl' => route('category.show', 'bridal'),
                'secondaryLabel' => 'Explore the Wedding Edit',
                'secondaryUrl' => route('collection.show', 'wedding-wear'),
                'image' => null,
                'mobileImage' => null,
            ],
            [
                'eyebrow' => 'Everyday Icons',
                'heading' => 'Fresh Looks, Endless Possibilities',
                'subheading' => 'Style your day with versatile rings, earrings, necklaces and bracelets made for mixing, matching and repeating.',
                'primaryLabel' => 'Shop Best Sellers',
                'primaryUrl' => route('best-sellers'),
                'secondaryLabel' => 'Explore New Arrivals',
                'secondaryUrl' => route('new-arrivals'),
                'image' => null,
                'mobileImage' => null,
            ],
        ];
    }
}

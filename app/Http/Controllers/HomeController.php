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
                'image' => $banner->image_url,
            ])
            ->all();

        return view('pages.home', [
            'title' => null,
            'heroSlides' => $heroSlides ?: $this->defaultHeroSlides(),
            'parentCategories' => StorefrontCatalog::topLevelCategories(),
            'categories' => StorefrontCatalog::categories(),
            'collections' => StorefrontCatalog::collections(),
            'newArrivals' => StorefrontCatalog::newArrivals(),
            'bestSellers' => StorefrontCatalog::bestSellers(),
            'occasions' => Catalog::occasions(),
            'recipients' => Catalog::recipients(),
            'reviews' => Catalog::reviews(),
        ]);
    }

    protected function defaultHeroSlides(): array
    {
        return [
            [
                'eyebrow' => 'The Aurelle Edit',
                'heading' => 'Jewellery Crafted for Every Story',
                'subheading' => "Discover timeless designs created to celebrate life's most precious moments.",
                'primaryLabel' => 'Shop Collection',
                'primaryUrl' => route('collections.index'),
                'secondaryLabel' => 'Explore New Arrivals',
                'secondaryUrl' => route('category.show', 'new-arrivals'),
                'image' => null,
            ],
            [
                'eyebrow' => 'Bridal 2026',
                'heading' => 'Heirlooms in the Making',
                'subheading' => 'Complete bridal sets, hand-finished in 22K gold with heritage craftsmanship.',
                'primaryLabel' => 'Shop Bridal',
                'primaryUrl' => route('category.show', 'bridal'),
                'secondaryLabel' => 'View Wedding Collection',
                'secondaryUrl' => route('collection.show', 'wedding-wear'),
                'image' => null,
            ],
            [
                'eyebrow' => 'Certified Brilliance',
                'heading' => 'The Diamond Collection',
                'subheading' => 'IGI certified diamonds set in 18K gold and platinum, for every occasion.',
                'primaryLabel' => 'Shop Diamonds',
                'primaryUrl' => route('category.show', 'diamond'),
                'secondaryLabel' => 'Shop Best Sellers',
                'secondaryUrl' => route('category.show', 'best-sellers'),
                'image' => null,
            ],
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\JewelleryCollection;
use App\Models\JewelleryType;
use App\Models\Product;
use App\Support\StorefrontCatalog;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = collect();
        $add = function (string $location, mixed $lastModified = null) use ($urls): void {
            $urls->push([
                'loc' => $location,
                'lastmod' => $lastModified?->toAtomString(),
            ]);
        };

        foreach ([
            'home',
            'about',
            'careers',
            'size-guide',
            'influencer',
            'privacy-policy',
            'terms-of-service',
            'refund-policy',
            'contact',
            'collections.index',
            'categories.index',
        ] as $routeName) {
            $add(route($routeName));
        }

        if (Product::query()->active()->where('is_new_arrival', true)->exists()) {
            $add(route('new-arrivals'));
        }

        if (Product::query()->active()->where('is_best_seller', true)->exists()) {
            $add(route('best-sellers'));
        }

        Category::query()
            ->active()
            ->where(function ($query) {
                $query->whereHas('products', fn ($products) => $products->active())
                    ->orWhereHas('children.products', fn ($products) => $products->active());
            })
            ->get(['slug', 'updated_at'])
            ->each(fn (Category $category) => $add(route('category.show', $category->slug), $category->updated_at));

        foreach (['gold', 'diamond', 'silver'] as $slug) {
            if (StorefrontCatalog::byCategory($slug) !== []) {
                $add(route('category.show', $slug));
            }
        }

        JewelleryCollection::query()
            ->active()
            ->get(['slug', 'updated_at'])
            ->filter(fn (JewelleryCollection $collection) => Product::query()->active()->whereCollectionSlug($collection->slug)->exists())
            ->each(fn (JewelleryCollection $collection) => $add(route('collection.show', $collection->slug), $collection->updated_at));

        JewelleryType::query()
            ->active()
            ->whereHas('products', fn ($products) => $products->active())
            ->get(['slug', 'updated_at'])
            ->each(fn (JewelleryType $type) => $add(route('jewellery-type.show', $type->slug), $type->updated_at));

        foreach (['for-her' => 'Women', 'for-him' => 'Men'] as $slug => $gender) {
            if (Product::query()->active()->where('gender', $gender)->exists()) {
                $add(route('recipient.show', $slug));
            }
        }

        Product::query()
            ->active()
            ->orderBy('id')
            ->get(['slug', 'updated_at'])
            ->each(fn (Product $product) => $add(route('product.show', $product->slug), $product->updated_at));

        return response()
            ->view('seo.sitemap', ['urls' => $this->uniqueUrls($urls)])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin/',
            'Disallow: /account/',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /order-success',
            'Disallow: /mail-preview/',
            'Disallow: /mail-test',
            'Disallow: /clear-cache',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ]);

        return response($content)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    private function uniqueUrls(Collection $urls): Collection
    {
        return $urls->unique('loc')->values();
    }
}

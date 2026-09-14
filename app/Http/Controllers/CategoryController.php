<?php

namespace App\Http\Controllers;

use App\Support\StorefrontCatalog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function index()
    {
        return view('pages.categories', [
            'title' => 'Categories',
            'categories' => StorefrontCatalog::topLevelCategories(),
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $special = [
            'new-arrivals' => 'new-arrivals',
            'best-sellers' => 'best-sellers',
        ];

        if (isset($special[$slug])) {
            return redirect()->route($special[$slug]);
        }

        $category = StorefrontCatalog::category($slug);

        abort_if(! $category, Response::HTTP_NOT_FOUND);

        return $this->categoryView($slug, $category);
    }

    public function newArrivals()
    {
        return $this->categoryView('new-arrivals', [
            'name' => 'New Arrivals',
            'blurb' => 'The latest additions to our collection, freshly crafted.',
            'art' => 'diamond',
        ], false);
    }

    public function bestSellers()
    {
        return $this->categoryView('best-sellers', [
            'name' => 'Best Sellers',
            'blurb' => 'Our most loved pieces, chosen again and again.',
            'art' => 'ring',
        ], false);
    }

    protected function categoryView(string $slug, array $category, bool $showBanner = true)
    {
        $products = StorefrontCatalog::byCategory($slug);

        return view('pages.category', [
            'title' => $category['name'],
            'slug' => $slug,
            'category' => $category,
            'products' => $products,
            'showBanner' => $showBanner,
        ]);
    }
}

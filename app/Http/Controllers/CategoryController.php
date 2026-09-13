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
            'new-arrivals' => ['name' => 'New Arrivals', 'blurb' => 'The latest additions to our collection, freshly crafted.', 'art' => 'diamond'],
            'best-sellers' => ['name' => 'Best Sellers', 'blurb' => 'Our most loved pieces, chosen again and again.', 'art' => 'ring'],
        ];

        $category = $special[$slug] ?? StorefrontCatalog::category($slug);

        abort_if(! $category, Response::HTTP_NOT_FOUND);

        $products = StorefrontCatalog::byCategory($slug);

        return view('pages.category', [
            'title' => $category['name'],
            'slug' => $slug,
            'category' => $category,
            'products' => $products,
        ]);
    }
}

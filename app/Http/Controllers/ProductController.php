<?php

namespace App\Http\Controllers;

use App\Support\Catalog;
use App\Support\StorefrontCatalog;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = StorefrontCatalog::product($slug);

        abort_if(! $product, Response::HTTP_NOT_FOUND);

        if (($product['slug'] ?? $product['id']) !== $slug) {
            return redirect()->route('product.show', $product['slug'] ?? $product['id']);
        }

        return view('pages.product', [
            'title' => $product['name'],
            'product' => $product,
            'related' => StorefrontCatalog::related($product['id'], 5),
            'bestSellersCross' => collect(StorefrontCatalog::bestSellers())->where('id', '!=', $product['id'])->take(5)->values()->all(),
            'recentlyViewed' => collect(StorefrontCatalog::products())->where('id', '!=', $product['id'])->shuffle(42)->take(5)->values()->all(),
            'reviews' => Catalog::reviews(),
        ]);
    }
}

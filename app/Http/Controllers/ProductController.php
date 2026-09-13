<?php

namespace App\Http\Controllers;

use App\Support\Catalog;
use App\Support\StorefrontCatalog;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function show(string $id)
    {
        $product = StorefrontCatalog::product($id);

        abort_if(! $product, Response::HTTP_NOT_FOUND);

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

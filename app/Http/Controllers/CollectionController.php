<?php

namespace App\Http\Controllers;

use App\Support\StorefrontCatalog;
use Symfony\Component\HttpFoundation\Response;

class CollectionController extends Controller
{
    public function index()
    {
        return view('pages.collections', [
            'title' => 'Collections',
            'collections' => StorefrontCatalog::collections(),
        ]);
    }

    public function show(string $slug)
    {
        $collection = collect(StorefrontCatalog::collections())->firstWhere('slug', $slug);

        abort_if(! $collection, Response::HTTP_NOT_FOUND);

        return view('pages.category', [
            'title' => $collection['name'],
            'slug' => $slug,
            'category' => $collection,
            'products' => StorefrontCatalog::byCollection($slug),
        ]);
    }
}

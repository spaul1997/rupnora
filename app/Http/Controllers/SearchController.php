<?php

namespace App\Http\Controllers;

use App\Support\StorefrontCatalog;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->query('q', ''));

        return view('pages.search', [
            'title' => $query !== '' ? 'Search results for "' . $query . '"' : 'Search',
            'query' => $query,
            'products' => $query !== '' ? StorefrontCatalog::search($query) : [],
        ]);
    }
}

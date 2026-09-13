<?php

namespace App\Http\Controllers;

use App\Support\StorefrontCatalog;

class CartController extends Controller
{
    public function index()
    {
        $items = [
            ['product' => StorefrontCatalog::product('eternal-bloom-diamond-ring'), 'qty' => 1, 'size' => '14'],
            ['product' => StorefrontCatalog::product('celestial-18k-gold-hoop-earrings'), 'qty' => 1, 'size' => null],
            ['product' => StorefrontCatalog::product('moonlight-silver-bracelet'), 'qty' => 2, 'size' => 'Adjustable'],
        ];
        $items = collect($items)->filter(fn ($item) => $item['product'])->values()->all();

        return view('pages.cart', [
            'title' => 'Shopping Cart',
            'items' => $items,
        ]);
    }
}

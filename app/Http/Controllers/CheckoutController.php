<?php

namespace App\Http\Controllers;

use App\Support\Catalog;
use App\Support\StorefrontCatalog;

class CheckoutController extends Controller
{
    public function index()
    {
        $items = [
            ['product' => StorefrontCatalog::product('eternal-bloom-diamond-ring'), 'qty' => 1, 'size' => '14'],
            ['product' => StorefrontCatalog::product('celestial-18k-gold-hoop-earrings'), 'qty' => 1, 'size' => null],
            ['product' => StorefrontCatalog::product('moonlight-silver-bracelet'), 'qty' => 2, 'size' => 'Adjustable'],
        ];
        $items = collect($items)->filter(fn ($item) => $item['product'])->values()->all();

        return view('pages.checkout', [
            'title' => 'Checkout',
            'items' => $items,
            'addresses' => Catalog::addresses(),
        ]);
    }

    public function success(?string $id = null)
    {
        $order = Catalog::order($id ?? 'ORD-2026-10482') ?? Catalog::orders()[0];

        return view('pages.order-success', [
            'title' => 'Order Confirmed',
            'order' => $order,
        ]);
    }
}

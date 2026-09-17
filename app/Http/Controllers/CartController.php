<?php

namespace App\Http\Controllers;

use App\Support\ShoppingCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        return view('pages.cart', [
            'title' => 'Shopping Cart',
            'items' => ShoppingCart::items(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:5'],
            'size' => ['nullable', 'string', 'max:50'],
        ]);

        $item = ShoppingCart::add($data['product_id'], (int) ($data['qty'] ?? 1), $data['size'] ?? null);

        abort_if(! $item, 404);

        return $this->cartResponse('Added to cart');
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $item = ShoppingCart::update($key, (int) $data['qty']);

        abort_if(! $item, 404);

        return $this->cartResponse();
    }

    public function destroy(string $key): JsonResponse
    {
        abort_if(! ShoppingCart::remove($key), 404);

        return $this->cartResponse('Removed from cart');
    }

    public function moveToWishlist(string $key): JsonResponse
    {
        $item = ShoppingCart::moveToWishlist($key);

        abort_if(! $item, 404);

        return $this->cartResponse('Moved to wishlist');
    }

    protected function cartResponse(?string $message = null): JsonResponse
    {
        return response()->json([
            'count' => ShoppingCart::count(),
            'summary' => ShoppingCart::summary(),
            'items' => collect(ShoppingCart::items())->map(fn (array $item) => [
                'key' => $item['key'], 'price' => $item['product']['price'],
                'mrp' => max($item['product']['mrp'], $item['product']['price']),
                'qty' => $item['qty'], 'max' => $item['max_qty'],
            ])->all(),
            'wishlistIds' => ShoppingCart::wishlistIds(),
            'message' => $message,
        ]);
    }
}

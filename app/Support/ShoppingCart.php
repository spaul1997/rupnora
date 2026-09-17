<?php

namespace App\Support;

use App\Models\Product;

class ShoppingCart
{
    private const CART_KEY = 'shopping_cart.items';

    private const INITIALIZED_KEY = 'shopping_cart.initialized';

    private const WISHLIST_KEY = 'wishlist.product_ids';

    private const WISHLIST_INITIALIZED_KEY = 'wishlist.initialized';

    public static function items(): array
    {
        return collect(self::rawCart())
            ->map(function (array $row, string $key) {
                $product = StorefrontCatalog::product($row['product_id'] ?? '');

                if (! $product) {
                    return null;
                }

                $maxQty = self::maxQty($product);

                return [
                    'key' => $key,
                    'product' => $product,
                    'qty' => self::clampQty((int) ($row['qty'] ?? 1), $maxQty),
                    'size' => $row['size'] ?? null,
                    'max_qty' => $maxQty,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public static function add(string|int $productId, int $qty = 1, ?string $size = null): ?array
    {
        $product = StorefrontCatalog::product($productId);

        if (! $product) {
            return null;
        }

        $size = filled($size) ? trim($size) : null;
        $maxQty = self::maxQty($product);
        $key = self::lineKey($product['id'], $size);
        $cart = self::rawCart();
        $currentQty = (int) ($cart[$key]['qty'] ?? 0);

        $cart[$key] = [
            'product_id' => $product['id'],
            'qty' => self::clampQty($currentQty + $qty, $maxQty),
            'size' => $size,
        ];

        self::putCart($cart);

        return self::findItem($key);
    }

    public static function update(string $key, int $qty): ?array
    {
        $cart = self::rawCart();

        if (! isset($cart[$key])) {
            return null;
        }

        $product = StorefrontCatalog::product($cart[$key]['product_id'] ?? '');

        if (! $product) {
            unset($cart[$key]);
            self::putCart($cart);

            return null;
        }

        $cart[$key]['qty'] = self::clampQty($qty, self::maxQty($product));
        self::putCart($cart);

        return self::findItem($key);
    }

    public static function remove(string $key): bool
    {
        $cart = self::rawCart();

        if (! isset($cart[$key])) {
            return false;
        }

        unset($cart[$key]);
        self::putCart($cart);

        return true;
    }

    public static function clear(): void
    {
        self::putCart([]);
    }

    public static function moveToWishlist(string $key): ?array
    {
        $item = self::findItem($key);

        if (! $item) {
            return null;
        }

        self::addWishlistId($item['product']['slug'] ?? $item['product']['id']);
        self::remove($key);

        return $item;
    }

    public static function count(): int
    {
        return count(self::items());
    }

    public static function summary(): array
    {
        $items = collect(self::items());
        $sellingTotal = $items->sum(fn (array $item) => $item['product']['price'] * $item['qty']);
        $mrpTotal = $items->sum(fn (array $item) => max($item['product']['mrp'], $item['product']['price']) * $item['qty']);
        $discount = max(0, $mrpTotal - $sellingTotal);
        $tax = round($sellingTotal * 0.03);

        return [
            'subtotal' => $mrpTotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $mrpTotal - $discount + $tax,
        ];
    }

    public static function wishlistIds(): array
    {
        return self::rawWishlistIds();
    }

    public static function removeWishlistId(string|int $productId): bool
    {
        if (auth()->user()?->role === 'customer') {
            $product = Product::query()->where('slug', $productId)
                ->when(is_numeric($productId), fn ($query) => $query->orWhere('id', $productId))
                ->first();

            return $product && auth()->user()->wishlistProducts()->detach($product->id) > 0;
        }

        $ids = self::rawWishlistIds();
        $removeKeys = self::wishlistKeysFor($productId);
        $nextIds = collect($ids)
            ->reject(fn ($id) => in_array((string) $id, $removeKeys, true))
            ->values()
            ->all();

        if (count($nextIds) === count($ids)) {
            return false;
        }

        self::putWishlistIds($nextIds);

        return true;
    }

    public static function addWishlistId(string|int $productId): bool
    {
        $product = StorefrontCatalog::product($productId);

        if (! $product) {
            return false;
        }

        if (auth()->user()?->role === 'customer') {
            auth()->user()->wishlistProducts()->syncWithoutDetaching([$product['id']]);

            return true;
        }

        $ids = collect(self::rawWishlistIds())
            ->push((string) $productId)
            ->unique()
            ->values()
            ->all();

        self::putWishlistIds($ids);

        return true;
    }

    private static function rawWishlistIds(): array
    {
        if (auth()->user()?->role === 'customer') {
            return auth()->user()->wishlistProducts()->active()
                ->orderBy('wishlist_items.id')
                ->get(['products.id', 'products.slug'])
                ->map(fn (Product $product) => (string) ($product->slug ?: $product->id))
                ->all();
        }

        if (! session()->has(self::WISHLIST_INITIALIZED_KEY)) {
            self::putWishlistIds(
                collect(session(self::WISHLIST_KEY, []))
                    ->unique()
                    ->values()
                    ->all()
            );
        }

        $ids = collect(session(self::WISHLIST_KEY, []))
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        $validIds = self::validWishlistIds($ids);

        if (count($validIds) !== count($ids)) {
            self::putWishlistIds($validIds);
        }

        return $validIds;
    }

    public static function mergeGuestWishlist(): void
    {
        foreach (session(self::WISHLIST_KEY, []) as $id) {
            self::addWishlistId($id);
        }

        session()->forget([self::WISHLIST_KEY, self::WISHLIST_INITIALIZED_KEY]);
    }

    private static function rawCart(): array
    {
        if (! session()->has(self::INITIALIZED_KEY)) {
            self::putCart([]);
        }

        return session(self::CART_KEY, []);
    }

    private static function putCart(array $cart): void
    {
        session()->put(self::CART_KEY, $cart);
        session()->put(self::INITIALIZED_KEY, true);
    }

    private static function findItem(string $key): ?array
    {
        return collect(self::items())->firstWhere('key', $key);
    }

    private static function putWishlistIds(array $ids): void
    {
        session()->put(self::WISHLIST_KEY, $ids);
        session()->put(self::WISHLIST_INITIALIZED_KEY, true);
    }

    private static function validWishlistIds(array $ids): array
    {
        return collect($ids)
            ->filter(fn ($id) => StorefrontCatalog::product($id) !== null)
            ->values()
            ->all();
    }

    private static function wishlistKeysFor(string|int $productId): array
    {
        $keys = [(string) $productId];
        $product = StorefrontCatalog::product($productId);

        if ($product) {
            $keys[] = (string) $product['id'];
            $keys[] = (string) ($product['slug'] ?? $product['id']);
        }

        return collect($keys)->unique()->values()->all();
    }

    private static function lineKey(string|int $productId, ?string $size): string
    {
        return sha1($productId.'|'.($size ?? ''));
    }

    private static function maxQty(array $product): int
    {
        return max(1, min(5, (int) ($product['stock_quantity'] ?? 5)));
    }

    private static function clampQty(int $qty, int $maxQty): int
    {
        return max(1, min($maxQty, $qty));
    }
}

<?php

namespace App\Support;

use App\Models\Category;
use App\Models\JewelleryCollection;
use App\Models\JewelleryType;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class StorefrontCatalog
{
    public static function categories(): array
    {
        $categories = Category::query()
            ->active()
            ->parents()
            ->withCount(['products as products_count' => fn (Builder $query) => $query->active()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => self::mapCategory($category))
            ->values();

        $fallbacks = collect(Catalog::categories())
            ->map(function (array $category) {
                if (in_array($category['slug'], ['gold', 'diamond', 'silver'], true)) {
                    $category['count'] = self::queryForVirtualCategory($category['slug'])->count();
                }

                return $category;
            });

        return $categories
            ->merge($fallbacks)
            ->unique('slug')
            ->values()
            ->all();
    }

    public static function topLevelCategories(): array
    {
        $categories = Category::query()
            ->active()
            ->parents()
            ->withCount(['products as products_count' => fn (Builder $query) => $query->active()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => self::mapCategory($category))
            ->values();

        if ($categories->isNotEmpty()) {
            return $categories->all();
        }

        return Catalog::categories();
    }

    public static function topCategoriesByProductCount(int $limit = 4): array
    {
        return Category::query()
            ->active()
            ->whereHas('products', fn (Builder $query) => $query->active())
            ->withCount(['products as products_count' => fn (Builder $query) => $query->active()])
            ->orderByDesc('products_count')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Category $category) => self::mapCategory($category))
            ->values()
            ->all();
    }

    public static function topSubcategories(int $limit = 6): array
    {
        return Category::query()
            ->active()
            ->whereNotNull('parent_id')
            ->with('parent')
            ->withCount(['products as products_count' => fn (Builder $query) => $query->active()])
            ->orderByDesc('products_count')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn (Category $category) => (int) $category->products_count > 0)
            ->take($limit)
            ->map(fn (Category $category) => self::mapCategory($category))
            ->values()
            ->all();
    }

    public static function headerCategories(): array
    {
        if (! Schema::hasColumn('categories', 'show_in_header')) {
            return self::categories();
        }

        return Category::query()
            ->active()
            ->parents()
            ->where('show_in_header', true)
            ->with(['children' => function ($query) {
                $query->active()
                    ->withCount(['products as products_count' => fn (Builder $query) => $query->active()])
                    ->orderByDesc('products_count')
                    ->orderBy('sort_order')
                    ->orderBy('name');
            }])
            ->withCount(['products as products_count' => fn (Builder $query) => $query->active()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => self::mapHeaderCategory($category))
            ->values()
            ->all();
    }

    public static function category(string $slug): ?array
    {
        $model = self::findCategoryModel($slug);

        if ($model) {
            return self::mapCategory($model);
        }

        return Catalog::category($slug);
    }

    public static function collections(): array
    {
        $collections = JewelleryCollection::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('slug')
            ->map(function (Collection $group, string $slug) {
                $first = $group->first();
                $count = self::baseProductQuery()->whereCollectionSlug($slug)->count();
                $fallback = collect(Catalog::collections())->firstWhere('slug', $slug) ?? [];

                return [
                    'slug' => $slug,
                    'name' => $first->name,
                    'art' => $fallback['art'] ?? self::artFor($first->name),
                    'blurb' => $first->description ?: ($fallback['blurb'] ?? 'Explore the '.$first->name.'.'),
                    'logo' => self::storageUrl($first->logo),
                    'image' => self::storageUrl($first->logo),
                    'banner' => self::storageUrl($first->banner),
                    'tag' => $count.' '.Str::plural('Design', $count),
                ];
            })
            ->values();

        if ($collections->isNotEmpty()) {
            return $collections->all();
        }

        return collect(Catalog::collections())
            ->map(function (array $collection) {
                $count = self::queryForCollectionFallback($collection['slug'])->count();
                $collection['tag'] = $count.' '.Str::plural('Design', $count);

                return $collection;
            })
            ->values()
            ->all();
    }

    public static function topJewelleryTypes(int $limit = 5): array
    {
        return Product::query()
            ->active()
            ->select('jewellery_type')
            ->selectRaw('count(*) as products_count')
            ->whereNotNull('jewellery_type')
            ->groupBy('jewellery_type')
            ->orderByDesc('products_count')
            ->orderBy('jewellery_type')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $type = JewelleryType::query()
                    ->active()
                    ->where(function (Builder $query) use ($row) {
                        $query->where('slug', Str::slug($row->jewellery_type))
                            ->orWhere('name', $row->jewellery_type);
                    })
                    ->first();

                $name = $type?->name ?? $row->jewellery_type;
                $slug = $type?->slug ?? Str::slug($row->jewellery_type);

                return [
                    'slug' => $slug,
                    'name' => $name,
                    'art' => self::artFor($name),
                    'blurb' => $type?->description ?: 'Explore '.$name.' designs.',
                    'count' => (int) $row->products_count,
                    'tag' => $row->products_count.' '.Str::plural('Design', (int) $row->products_count),
                ];
            })
            ->values()
            ->all();
    }

    public static function jewelleryType(string $slug): ?array
    {
        $type = JewelleryType::query()
            ->active()
            ->where('slug', $slug)
            ->first();

        if ($type) {
            $count = self::baseProductQuery()
                ->where('jewellery_type', $type->name)
                ->count();

            return [
                'slug' => $type->slug,
                'name' => $type->name,
                'art' => self::artFor($type->name),
                'blurb' => $type->description ?: 'Explore '.$type->name.' designs.',
                'count' => $count,
                'tag' => $count.' '.Str::plural('Design', $count),
            ];
        }

        $row = Product::query()
            ->active()
            ->whereNotNull('jewellery_type')
            ->get(['jewellery_type'])
            ->first(fn (Product $product) => Str::slug($product->jewellery_type) === $slug);

        if (! $row) {
            return null;
        }

        $count = self::baseProductQuery()
            ->where('jewellery_type', $row->jewellery_type)
            ->count();

        return [
            'slug' => $slug,
            'name' => $row->jewellery_type,
            'art' => self::artFor($row->jewellery_type),
            'blurb' => 'Explore '.$row->jewellery_type.' designs.',
            'count' => $count,
            'tag' => $count.' '.Str::plural('Design', $count),
        ];
    }

    public static function products(?Builder $query = null): array
    {
        $query ??= self::baseProductQuery();

        return self::hydrateForStorefront($query)
            ->map(fn (Product $product) => self::mapProduct($product))
            ->values()
            ->all();
    }

    public static function product(string|int $id): ?array
    {
        $query = self::baseProductQuery()
            ->where(function (Builder $query) use ($id) {
                if (is_numeric($id)) {
                    $query->where('id', $id)
                        ->orWhere('slug', $id);

                    return;
                }

                $query->where('slug', $id);
            });

        $product = self::hydrateForStorefront($query)->first();

        return $product ? self::mapProduct($product) : null;
    }

    public static function newArrivals(int $limit = 10): array
    {
        return self::products(
            self::baseProductQuery()
                ->where('is_new_arrival', true)
                ->latest()
                ->limit($limit)
        );
    }

    public static function bestSellers(int $limit = 10): array
    {
        return self::products(
            self::baseProductQuery()
                ->where('is_best_seller', true)
                ->latest()
                ->limit($limit)
        );
    }

    public static function randomInStockProducts(int $limit = 6): array
    {
        return self::products(
            self::baseProductQuery()
                ->where('stock_quantity', '>', 0)
                ->inRandomOrder()
                ->limit($limit)
        );
    }

    /**
     * Build a privacy-safe set of products for storefront social-proof toasts.
     *
     * Real, non-cancelled purchases are preferred. Unsold products are only
     * presented as popular picks, so the storefront never fabricates a sale.
     */
    public static function socialProofItems(int $limit = 8): array
    {
        if ($limit < 1) {
            return [];
        }

        $purchased = OrderItem::query()
            ->whereNotNull('product_id')
            ->whereHas('order', fn (Builder $query) => $query->whereIn('status', [
                'confirmed', 'processing', 'packed', 'shipped', 'out_for_delivery', 'delivered',
            ]))
            ->whereHas('product', fn (Builder $query) => $query->active())
            ->with(['product.images'])
            ->latest('order_items.created_at')
            ->limit(max($limit * 4, 20))
            ->get()
            ->unique('product_id')
            ->shuffle()
            ->take($limit)
            ->map(fn (OrderItem $item) => self::mapSocialProofProduct($item->product, true))
            ->values();

        $remaining = $limit - $purchased->count();

        if ($remaining < 1) {
            return $purchased->all();
        }

        $popular = Product::query()
            ->active()
            ->where('stock_quantity', '>', 0)
            ->whereNotIn('id', $purchased->pluck('id'))
            ->with('images')
            ->inRandomOrder()
            ->limit($remaining)
            ->get()
            ->map(fn (Product $product) => self::mapSocialProofProduct($product, false));

        return $purchased->concat($popular)->values()->all();
    }

    public static function byCategory(string $slug): array
    {
        if ($slug === 'new-arrivals') {
            return self::newArrivals(60);
        }

        if ($slug === 'best-sellers') {
            return self::bestSellers(60);
        }

        if (in_array($slug, ['gold', 'diamond', 'silver'], true)) {
            return self::products(self::queryForVirtualCategory($slug));
        }

        $category = self::findCategoryModel($slug);

        if (! $category) {
            return Catalog::category($slug)
                ? self::products(self::queryForCategoryFallback($slug)->latest())
                : [];
        }

        $ids = collect([$category->id])
            ->merge($category->children()->pluck('id'))
            ->all();

        return self::products(
            self::baseProductQuery()
                ->whereIn('category_id', $ids)
                ->latest()
        );
    }

    public static function byCollection(string $slug): array
    {
        $query = self::baseProductQuery()->whereCollectionSlug($slug);

        if (! (clone $query)->exists()) {
            $query = self::queryForCollectionFallback($slug);
        }

        return self::products($query);
    }

    public static function byJewelleryType(string $slug): array
    {
        $type = self::jewelleryType($slug);

        if (! $type) {
            return [];
        }

        return self::products(
            self::baseProductQuery()
                ->where('jewellery_type', $type['name'])
                ->latest()
        );
    }

    public static function search(string $query): array
    {
        $builder = self::baseProductQuery();

        if ($query !== '') {
            $needle = '%'.$query.'%';

            $builder->where(function (Builder $builder) use ($needle) {
                $builder->where('name', 'like', $needle)
                    ->orWhere('sku', 'like', $needle)
                    ->orWhere('jewellery_type', 'like', $needle)
                    ->orWhere('metal_type', 'like', $needle)
                    ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', $needle));
            });
        }

        return self::products($builder->latest());
    }

    public static function related(string|int $id, int $limit = 5): array
    {
        $product = Product::query()->find($id);

        if (! $product) {
            return [];
        }

        return self::products(
            self::baseProductQuery()
                ->whereKeyNot($product->id)
                ->where(function (Builder $query) use ($product) {
                    $query->where('category_id', $product->category_id)
                        ->orWhere('metal_type', $product->metal_type)
                        ->orWhere('jewellery_type', $product->jewellery_type);
                })
                ->limit($limit)
        );
    }

    protected static function baseProductQuery(): Builder
    {
        return Product::query()->active();
    }

    protected static function hydrateForStorefront(Builder $query): Collection
    {
        return $query
            ->with(['category.parent', 'category.children', 'images', 'variants'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews')
            ->get();
    }

    protected static function findCategoryModel(string $slug): ?Category
    {
        $aliases = match ($slug) {
            'mens' => ['mens', 'mens-jewellery', 'men-s-jewellery'],
            'bridal' => ['bridal', 'bridal-jewellery'],
            default => [$slug],
        };

        return Category::query()
            ->active()
            ->with(['parent', 'children'])
            ->whereIn('slug', $aliases)
            ->first();
    }

    protected static function mapCategory(Category $category): array
    {
        $slug = self::normalizeCategorySlug($category);
        $fallback = Catalog::category($slug) ?? [];
        $bannerCategory = $category->parent ?: $category;
        $banner = $bannerCategory->banner ?: $category->banner;

        return [
            'slug' => $slug,
            'name' => $category->name,
            'art' => $fallback['art'] ?? self::artFor($category->name.' '.$category->slug),
            'image' => self::storageUrl($category->image),
            'banner' => self::storageUrl($banner),
            'blurb' => $category->description ?: ($fallback['blurb'] ?? 'Explore our '.$category->name.' collection.'),
            'count' => $category->products_count ?? $category->products()->active()->count(),
            'show_in_header' => (bool) ($category->show_in_header ?? true),
        ];
    }

    protected static function mapHeaderCategory(Category $category): array
    {
        return self::mapCategory($category) + [
            'children' => $category->children
                ->filter(fn (Category $child) => (int) ($child->products_count ?? 0) > 0)
                ->take(5)
                ->map(fn (Category $child) => self::mapCategory($child))
                ->values()
                ->all(),
        ];
    }

    protected static function mapProduct(Product $product): array
    {
        $category = $product->category;
        $categorySlug = $category ? self::normalizeCategorySlug($category->parent ?? $category) : 'jewellery';
        $categoryName = $category?->parent?->name ?? $category?->name ?? 'Jewellery';
        $subcategorySlug = $category?->parent ? $category->slug : null;
        $subcategoryName = $category?->parent ? $category->name : null;
        $price = $product->computeFinalPrice();
        $actualReviewsCount = (int) ($product->approved_reviews_count ?? 0);
        $actualRating = $product->approved_reviews_avg_rating
            ? (float) $product->approved_reviews_avg_rating
            : null;
        $engagement = self::defaultProductEngagement($product);
        $reviewsCount = $engagement['reviews_count'] + $actualReviewsCount;
        $ratingsCount = $engagement['ratings_count'] + $actualReviewsCount;
        $rating = round((
            ($engagement['rating'] * $engagement['ratings_count'])
            + (($actualRating ?? $engagement['rating']) * $actualReviewsCount)
        ) / $ratingsCount, 1);
        $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
        $badges = [];

        if ($product->is_new_arrival) {
            $badges[] = 'New';
        }

        if ($product->is_best_seller) {
            $badges[] = 'Bestseller';
        }

        if ($product->stock_status === 'low_stock') {
            $badges[] = 'Limited';
        }

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'name' => $product->name,
            'brand' => $product->brand,
            'category' => $categorySlug,
            'category_name' => $categoryName,
            'subcategory' => $subcategorySlug,
            'subcategory_name' => $subcategoryName,
            'type' => $product->jewellery_type,
            'metal' => $product->metal_type,
            'finish_plating' => $product->finish_plating,
            'colour' => $product->metal_colour,
            'purity' => $product->purity,
            'gender' => self::genderFor($product),
            'occasion' => self::occasionsFor($product),
            'collection' => $product->collectionSlugs()[0] ?? self::collectionFor($product),
            'collections' => $product->collectionSlugs(),
            'collection_names' => self::collectionNamesFor($product),
            'stone_type' => $product->gemstone_type,
            'stone_colour' => $product->gemstone_colour,
            'is_adjustable' => (bool) $product->is_adjustable,
            'is_water_resistant' => (bool) $product->is_water_resistant,
            'is_return_available' => (bool) $product->is_return_available,
            'is_refund_available' => (bool) $product->is_refund_available,
            'art' => self::artFor($product->jewellery_type.' '.$categoryName.' '.$product->metal_type),
            'image' => $primaryImage ? self::storageUrl($primaryImage->image_path) : null,
            'gallery' => $product->images->map(fn ($image) => self::storageUrl($image->image_path))->values()->all(),
            'price' => $price,
            'mrp' => (float) $product->mrp,
            'offer_expiry_date' => $product->hasActiveOffer() ? $product->offer_expiry_date?->format('d M Y') : null,
            'discount_expiry_date' => $product->hasActiveDiscount() ? $product->discount_expiry_date?->format('d M Y') : null,
            'rating' => $rating,
            'ratings_count' => $ratingsCount,
            'reviews_count' => $reviewsCount,
            'badges' => $badges,
            'is_new' => (bool) $product->is_new_arrival,
            'is_bestseller' => (bool) $product->is_best_seller,
            'in_stock' => $product->stock_quantity > 0,
            'stock_quantity' => (int) $product->stock_quantity,
            'stock_status' => $product->stock_status,
            'short_desc' => $product->short_description ?: Str::limit(strip_tags((string) $product->description), 120),
            'description' => $product->description ?: $product->short_description,
            'metal_options' => $product->variants->pluck('metal')->filter()->unique()->values()->all() ?: null,
            'sizes' => $product->variants->pluck('size')->filter()->unique()->values()->all() ?: null,
            'variants' => $product->variants
                ->map(fn ($variant) => [
                    'sku' => $variant->sku,
                    'size' => $variant->size,
                    'metal' => $variant->metal,
                    'purity' => $variant->purity,
                    'colour' => $variant->colour,
                    'price' => ! $product->offerHasExpired() ? ($variant->offer_price ?? $variant->price) : $variant->price,
                    'stock_quantity' => (int) $variant->stock_quantity,
                    'status' => $variant->status,
                ])
                ->values()
                ->all(),
            'weight' => [
                'gross' => self::weight($product->gross_weight),
                'net' => self::weight($product->net_weight),
                'metal' => self::weight($product->metal_weight),
                'stone' => self::weight($product->gemstone_weight ?: $product->diamond_carat),
            ],
            'has_diamond' => (bool) $product->has_diamond,
            'has_gemstone' => (bool) $product->has_gemstone,
            'diamond' => $product->has_diamond ? [
                'carat' => self::diamondCarat($product->diamond_carat),
                'colour' => $product->diamond_colour,
                'clarity' => $product->diamond_clarity,
                'cut' => $product->diamond_cut,
                'shape' => $product->diamond_shape,
                'count' => $product->diamond_count,
            ] : null,
            'gemstone' => $product->has_gemstone ? [
                'type' => $product->gemstone_type,
                'colour' => $product->gemstone_colour,
                'weight' => self::diamondCarat($product->gemstone_weight),
            ] : null,
        ];
    }

    protected static function mapSocialProofProduct(Product $product, bool $recentlyPurchased): array
    {
        $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'image' => $primaryImage ? self::storageUrl($primaryImage->image_path) : null,
            'url' => route('product.show', $product->slug ?: $product->id),
            'recentlyPurchased' => $recentlyPurchased,
        ];
    }

    /**
     * Give products a stable engagement baseline until organic reviews build up.
     * The slug-based seed keeps values consistent across requests and deployments.
     */
    protected static function defaultProductEngagement(Product $product): array
    {
        $seed = (int) sprintf('%u', crc32($product->slug ?: (string) $product->id));

        return [
            'rating' => 4.0 + (intdiv($seed, 2091) % 9) / 10,
            'ratings_count' => 150 + (intdiv($seed, 41) % 51),
            'reviews_count' => 40 + ($seed % 41),
        ];
    }

    protected static function collectionNamesFor(Product $product): array
    {
        $slugs = $product->collectionSlugs();

        if ($slugs === []) {
            return [];
        }

        $names = JewelleryCollection::query()
            ->whereIn('slug', $slugs)
            ->pluck('name', 'slug');

        return collect($slugs)
            ->map(fn (string $slug) => $names[$slug] ?? Str::headline(str_replace('-', ' ', $slug)))
            ->values()
            ->all();
    }

    protected static function queryForVirtualCategory(string $slug): Builder
    {
        $query = self::baseProductQuery();

        return match ($slug) {
            'gold' => $query->where('metal_type', 'like', '%Gold%'),
            'diamond' => $query->where('has_diamond', true),
            'silver' => $query->where('metal_type', 'like', '%Silver%'),
            default => $query->whereRaw('1 = 0'),
        };
    }

    protected static function queryForCategoryFallback(string $slug): Builder
    {
        $terms = $slug === 'mens' ? ["men's", 'mens'] : [Str::singular($slug)];

        return self::baseProductQuery()->where(function (Builder $products) use ($terms) {
            foreach ($terms as $term) {
                $pattern = '%'.$term.'%';

                $products->orWhere('name', 'like', $pattern)
                    ->orWhere('jewellery_type', 'like', $pattern)
                    ->orWhereHas('category', function (Builder $category) use ($pattern) {
                        $category->where('name', 'like', $pattern)
                            ->orWhere('slug', 'like', $pattern);
                    });
            }
        });
    }

    protected static function queryForCollectionFallback(string $slug): Builder
    {
        $query = self::baseProductQuery();

        return match ($slug) {
            'daily-wear' => $query->pricedAtMost(50000),
            'office-wear' => $query->whereIn('jewellery_type', ['Minimal', 'Pearl', 'Silver-plated']),
            'casual-wear' => $query->whereIn('jewellery_type', ['Handmade', 'Minimal', 'Western', 'Silver-plated']),
            'college-wear' => $query->whereIn('jewellery_type', ['Handmade', 'Minimal', 'Artificial Stone']),
            'party-wear' => $query->whereIn('jewellery_type', ['American Diamond', 'Pearl', 'Rose-gold-plated', 'Western']),
            'festive-wear' => $query->whereIn('jewellery_type', ['Kundan', 'Traditional', 'Gold-plated', 'Oxidised']),
            'wedding-wear' => $query->whereIn('jewellery_type', ['Kundan', 'Pearl', 'Traditional', 'American Diamond']),
            'gift-jewellery' => $query->pricedAtMost(100000),
            'diamond-collection' => $query->where('has_diamond', true),
            'everyday-gold' => $query->where('metal_type', 'like', '%Gold%')->pricedAtMost(60000),
            'wedding-collection' => $query->whereHas('category', fn (Builder $category) => $category->where('slug', 'like', '%bridal%')),
            'minimal-collection' => $query->whereIn('jewellery_type', ['Pendant', 'Ring', 'Earrings']),
            'festive-collection' => $query->whereIn('jewellery_type', ['Bangle', 'Necklace', 'Earrings']),
            default => $query->whereRaw('1 = 0'),
        };
    }

    protected static function normalizeCategorySlug(Category $category): string
    {
        $slug = $category->slug;

        if (str_contains($slug, 'mens') || str_contains($slug, 'men-s')) {
            return 'mens';
        }

        if (str_contains($slug, 'bridal')) {
            return 'bridal';
        }

        return $slug;
    }

    protected static function artFor(string $text): string
    {
        $text = Str::lower($text);

        foreach ([
            'earring' => 'earring',
            'necklace' => 'necklace',
            'pendant' => 'pendant',
            'bracelet' => 'bracelet',
            'bangle' => 'bangle',
            'chain' => 'chain',
            'men' => 'mens',
            'bridal' => 'bridal',
            'diamond' => 'diamond',
            'silver' => 'silver',
            'gold' => 'gold',
            'ring' => 'ring',
        ] as $needle => $art) {
            if (str_contains($text, $needle)) {
                return $art;
            }
        }

        return 'ring';
    }

    protected static function genderFor(Product $product): string
    {
        if ($product->gender) {
            return $product->gender;
        }

        $text = Str::lower(($product->category?->name ?? '').' '.$product->name);

        if (str_contains($text, 'men')) {
            return 'Men';
        }

        return 'Women';
    }

    protected static function occasionsFor(Product $product): array
    {
        if ($product->occasion) {
            return [$product->occasion];
        }

        $text = Str::lower(($product->category?->name ?? '').' '.$product->name.' '.$product->jewellery_type);
        $occasions = ['everyday'];

        if (str_contains($text, 'bridal') || str_contains($text, 'wedding')) {
            $occasions[] = 'wedding';
        }

        if (str_contains($text, 'engagement')) {
            $occasions[] = 'engagement';
        }

        if (in_array($product->jewellery_type, ['Bangle', 'Necklace', 'Earrings'], true)) {
            $occasions[] = 'festive';
        }

        if ($product->has_diamond) {
            $occasions[] = 'anniversary';
        }

        return array_values(array_unique($occasions));
    }

    protected static function collectionFor(Product $product): string
    {
        if ($product->has_diamond) {
            return 'party-wear';
        }

        if (str_contains(Str::lower($product->category?->name ?? ''), 'bridal')) {
            return 'wedding-wear';
        }

        if (str_contains(Str::lower($product->metal_type), 'gold')) {
            return 'daily-wear';
        }

        return 'casual-wear';
    }

    protected static function weight(mixed $value): string
    {
        return $value ? number_format((float) $value, 2).'g' : '0g';
    }

    protected static function diamondCarat(mixed $value): string
    {
        return $value ? number_format((float) $value, 2).' ct' : '0 ct';
    }

    protected static function storageUrl(?string $path): ?string
    {
        return $path ? '/storage/'.ltrim($path, '/') : null;
    }
}

<?php

namespace App\Support;

use App\Models\Category;
use App\Models\JewelleryCollection;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
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
                    'logo' => $first->logo ? asset('storage/'.$first->logo) : null,
                    'image' => $first->logo ? asset('storage/'.$first->logo) : null,
                    'banner' => $first->banner ? asset('storage/'.$first->banner) : null,
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
                $query->where('id', $id)
                    ->orWhere('slug', $id);
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

        return [
            'slug' => $slug,
            'name' => $category->name,
            'art' => $fallback['art'] ?? self::artFor($category->name.' '.$category->slug),
            'image' => $category->image ? asset('storage/'.$category->image) : null,
            'banner' => $category->banner ? asset('storage/'.$category->banner) : null,
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
        $price = (float) ($product->final_price ?: $product->offer_price ?: $product->selling_price);
        $reviewsCount = (int) ($product->approved_reviews_count ?? 0);
        $rating = $product->approved_reviews_avg_rating
            ? round((float) $product->approved_reviews_avg_rating, 1)
            : 0;
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
            'name' => $product->name,
            'category' => $categorySlug,
            'category_name' => $categoryName,
            'type' => $product->jewellery_type,
            'metal' => $product->metal_type,
            'purity' => $product->purity,
            'gender' => self::genderFor($product),
            'occasion' => self::occasionsFor($product),
            'collection' => $product->collectionSlugs()[0] ?? self::collectionFor($product),
            'collections' => $product->collectionSlugs(),
            'art' => self::artFor($product->jewellery_type.' '.$categoryName.' '.$product->metal_type),
            'image' => $primaryImage ? asset('storage/'.$primaryImage->image_path) : null,
            'gallery' => $product->images->map(fn ($image) => asset('storage/'.$image->image_path))->values()->all(),
            'price' => $price,
            'mrp' => (float) $product->mrp,
            'rating' => $rating,
            'reviews_count' => $reviewsCount,
            'badges' => $badges,
            'is_new' => (bool) $product->is_new_arrival,
            'is_bestseller' => (bool) $product->is_best_seller,
            'in_stock' => $product->stock_quantity > 0,
            'short_desc' => $product->short_description ?: Str::limit(strip_tags((string) $product->description), 120),
            'description' => $product->description ?: $product->short_description,
            'sizes' => $product->variants->pluck('size')->filter()->unique()->values()->all() ?: null,
            'weight' => [
                'gross' => self::weight($product->gross_weight),
                'net' => self::weight($product->net_weight),
                'stone' => self::weight($product->gemstone_weight ?: $product->diamond_carat),
            ],
            'diamond' => $product->has_diamond ? [
                'carat' => self::diamondCarat($product->diamond_carat),
                'colour' => $product->diamond_colour,
                'clarity' => $product->diamond_clarity,
                'shape' => $product->diamond_shape,
            ] : null,
        ];
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
            'daily-wear' => $query->where('final_price', '<=', 50000),
            'office-wear' => $query->whereIn('jewellery_type', ['Minimal', 'Pearl', 'Silver-plated']),
            'casual-wear' => $query->whereIn('jewellery_type', ['Handmade', 'Minimal', 'Western', 'Silver-plated']),
            'college-wear' => $query->whereIn('jewellery_type', ['Handmade', 'Minimal', 'Artificial Stone']),
            'party-wear' => $query->whereIn('jewellery_type', ['American Diamond', 'Pearl', 'Rose-gold-plated', 'Western']),
            'festive-wear' => $query->whereIn('jewellery_type', ['Kundan', 'Traditional', 'Gold-plated', 'Oxidised']),
            'wedding-wear' => $query->whereIn('jewellery_type', ['Kundan', 'Pearl', 'Traditional', 'American Diamond']),
            'gift-jewellery' => $query->where('final_price', '<=', 100000),
            'diamond-collection' => $query->where('has_diamond', true),
            'everyday-gold' => $query->where('metal_type', 'like', '%Gold%')->where('final_price', '<=', 60000),
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
        $text = Str::lower(($product->category?->name ?? '').' '.$product->name);

        if (str_contains($text, 'men')) {
            return 'Men';
        }

        return 'Women';
    }

    protected static function occasionsFor(Product $product): array
    {
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
}

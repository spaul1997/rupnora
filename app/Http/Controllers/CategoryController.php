<?php

namespace App\Http\Controllers;

use App\Support\Catalog;
use App\Support\StorefrontCatalog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    private const PRODUCTS_PER_PAGE = 20;

    public function index()
    {
        return view('pages.categories', [
            'title' => 'Shop Jewellery by Category',
            'categories' => StorefrontCatalog::topLevelCategories(),
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $special = [
            'new-arrivals' => 'new-arrivals',
            'best-sellers' => 'best-sellers',
        ];

        if (isset($special[$slug])) {
            return redirect()->route($special[$slug], status: Response::HTTP_MOVED_PERMANENTLY);
        }

        $category = StorefrontCatalog::category($slug);

        abort_if(! $category, Response::HTTP_NOT_FOUND);

        return $this->categoryView($request, $slug, $category);
    }

    public function recipient(Request $request, string $slug)
    {
        $recipient = collect(Catalog::recipients())->firstWhere('slug', $slug);

        abort_if(! $recipient, Response::HTTP_NOT_FOUND);

        $gender = $slug === 'for-him' ? 'Men' : 'Women';
        $products = collect(StorefrontCatalog::products())
            ->whereStrict('gender', $gender)
            ->values()
            ->all();

        return $this->renderCategory(
            $request,
            $slug,
            $recipient,
            $products,
            false,
            [$gender],
        );
    }

    public function newArrivals(Request $request)
    {
        return $this->categoryView($request, 'new-arrivals', [
            'name' => 'New Arrivals',
            'blurb' => 'The latest additions to our collection, freshly crafted.',
            'art' => 'diamond',
        ], false);
    }

    public function bestSellers(Request $request)
    {
        return $this->categoryView($request, 'best-sellers', [
            'name' => 'Best Sellers',
            'blurb' => 'Our most loved pieces, chosen again and again.',
            'art' => 'ring',
        ], false);
    }

    public function jewelleryType(Request $request, string $slug)
    {
        $type = StorefrontCatalog::jewelleryType($slug);

        abort_if(! $type, Response::HTTP_NOT_FOUND);

        return $this->renderCategory(
            $request,
            $slug,
            $type,
            StorefrontCatalog::byJewelleryType($slug),
            false,
        );
    }

    protected function categoryView(Request $request, string $slug, array $category, bool $showBanner = true)
    {
        return $this->renderCategory(
            $request,
            $slug,
            $category,
            StorefrontCatalog::byCategory($slug),
            $showBanner,
        );
    }

    protected function renderCategory(
        Request $request,
        string $slug,
        array $category,
        array $products,
        bool $showBanner = true,
        array $initialGenderFilter = [],
    ) {
        $page = max(1, $request->integer('page', 1));
        $offset = ($page - 1) * self::PRODUCTS_PER_PAGE;
        $visibleProducts = array_slice($products, $offset, self::PRODUCTS_PER_PAGE);
        $totalProducts = count($products);
        $loadedCount = min($offset + count($visibleProducts), $totalProducts);
        $hasMore = $loadedCount < $totalProducts;
        $meta = StorefrontCatalog::productMeta($visibleProducts, $offset);

        abort_if(! $request->expectsJson() && $page > 1 && $visibleProducts === [], Response::HTTP_NOT_FOUND);

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('pages.partials.category-products', [
                    'products' => $visibleProducts,
                    'startIndex' => $offset,
                ])->render(),
                'meta' => $meta,
                'has_more' => $hasMore,
                'next_page' => $hasMore ? $page + 1 : null,
                'loaded_count' => $loadedCount,
                'total' => $totalProducts,
            ]);
        }

        return view('pages.category', [
            'title' => $category['name'],
            'seoTitle' => $category['meta_title'] ?? $category['name'].' Jewellery Online',
            'seoDescription' => $category['meta_description'] ?? $category['blurb'] ?? null,
            'slug' => $slug,
            'category' => $category,
            'products' => $visibleProducts,
            'filterProducts' => $products,
            'meta' => $meta,
            'totalProducts' => $totalProducts,
            'hasMore' => $hasMore,
            'nextPage' => $hasMore ? $page + 1 : null,
            'currentPage' => $page,
            'loadUrl' => $request->url(),
            'showBanner' => $showBanner,
            'initialGenderFilter' => $initialGenderFilter,
        ]);
    }
}

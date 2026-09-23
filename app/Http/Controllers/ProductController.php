<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Support\ProductReviewFallbacks;
use App\Support\StorefrontCatalog;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function show(string $slug, ProductReviewFallbacks $fallbackReviews)
    {
        $product = StorefrontCatalog::product($slug);

        abort_if(! $product, Response::HTTP_NOT_FOUND);

        if (($product['slug'] ?? $product['id']) !== $slug) {
            return redirect()->route('product.show', $product['slug'] ?? $product['id']);
        }

        $approvedReviews = Review::query()
            ->approved()
            ->where('product_id', $product['id'])
            ->with('customer')
            ->latest('approved_at')
            ->limit(6)
            ->get()
            ->map(fn (Review $review) => [
                'name' => $review->customer?->name ?? 'Rupnora Customer',
                'location' => 'Rupnora customer',
                'rating' => $review->rating,
                'verified' => $review->order_id !== null,
                'date' => ($review->approved_at ?? $review->created_at)->format('M Y'),
                'text' => $review->review,
            ]);

        $displayReviews = $approvedReviews->isNotEmpty()
            ? $approvedReviews->all()
            : $fallbackReviews->forProduct($product);

        $customerReview = auth()->check() && auth()->user()->role === 'customer'
            ? Review::query()
                ->where('customer_id', auth()->id())
                ->where('product_id', $product['id'])
                ->first()
            : null;

        return view('pages.product', [
            'title' => $product['name'],
            'product' => $product,
            'related' => StorefrontCatalog::related($product['id'], 5),
            'bestSellersCross' => collect(StorefrontCatalog::bestSellers())->where('id', '!=', $product['id'])->take(5)->values()->all(),
            'recentlyViewed' => collect(StorefrontCatalog::products())->where('id', '!=', $product['id'])->shuffle(42)->take(5)->values()->all(),
            'reviews' => $displayReviews,
            'customerReview' => $customerReview,
        ]);
    }
}

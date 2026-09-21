<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $data = $request->validateWithBag('reviewSubmission', [
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:120'],
            'review' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $review = Review::query()->firstOrNew([
            'customer_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);
        $isNew = ! $review->exists;

        $review->fill([
            ...$data,
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ])->save();

        return redirect()
            ->to(route('product.show', $product->slug).'#customer-reviews')
            ->with('review_success', $isNew
                ? 'Thank you. Your review was submitted for approval.'
                : 'Your review was updated and submitted for approval.');
    }
}

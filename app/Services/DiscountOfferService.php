<?php

namespace App\Services;

use App\Models\Category;
use App\Models\DiscountOffer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DiscountOfferService
{
    public function assertDateRangeAvailable(array $data, ?DiscountOffer $currentOffer = null): void
    {
        if (! $data['is_active']) {
            return;
        }

        $overlap = DiscountOffer::query()
            ->where('is_active', true)
            ->when($currentOffer, fn (Builder $query) => $query->whereKeyNot($currentOffer->getKey()))
            ->whereDate('start_date', '<=', $data['end_date'])
            ->whereDate('end_date', '>=', $data['start_date'])
            ->lockForUpdate()
            ->first(['id', 'reason', 'start_date', 'end_date']);

        if ($overlap) {
            throw ValidationException::withMessages([
                'end_date' => "The date range overlaps the active offer \"{$overlap->reason}\" ({$overlap->start_date->format('d M Y')} to {$overlap->end_date->format('d M Y')}).",
            ]);
        }
    }

    public function apply(DiscountOffer $offer, ?Collection $previousProductIds = null, bool $automatic = false): Collection
    {
        $previousProductIds ??= collect();
        $targetDiscounts = $this->targetDiscounts($offer);
        $targetProductIds = $targetDiscounts->keys();
        $changedProductIds = $this->clearProducts(
            Product::query()
                ->whereIn('id', $previousProductIds->diff($targetProductIds))
                ->where('discount_offer_id', $offer->id)
                ->get(),
            $automatic ? 'Offer assignment synchronized automatically.' : 'Offer assignment removed: '.$offer->reason,
        );

        if (! $offer->isActiveNow()) {
            return $changedProductIds->merge($this->clearProducts(
                Product::query()->where('discount_offer_id', $offer->id)->get(),
                $automatic
                    ? 'Effective price changed automatically after an offer or discount period ended.'
                    : 'Offer is not currently active: '.$offer->reason,
            ))->unique()->values();
        }

        $conflictingProduct = Product::query()
            ->whereIn('id', $targetProductIds)
            ->where(function (Builder $query) use ($offer) {
                $query->where(function (Builder $query) {
                    $query->whereNull('discount_offer_id')
                        ->whereNotNull('discount_type')
                        ->where('discount_value', '>', 0)
                        ->where(fn (Builder $query) => $query
                            ->whereNull('discount_start_date')
                            ->orWhereDate('discount_start_date', '<=', today()))
                        ->where(fn (Builder $query) => $query
                            ->whereNull('discount_expiry_date')
                            ->orWhereDate('discount_expiry_date', '>=', today()));
                })->orWhere(function (Builder $query) use ($offer) {
                    $query->whereNotNull('discount_offer_id')
                        ->where('discount_offer_id', '!=', $offer->id)
                        ->whereHas('discountOffer', fn (Builder $query) => $query->activeNow());
                });
            })
            ->first(['id', 'name']);

        if ($conflictingProduct) {
            throw ValidationException::withMessages([
                'scope_type' => "{$conflictingProduct->name} already has an active discount.",
            ]);
        }

        Product::query()
            ->whereIn('id', $targetProductIds)
            ->get()
            ->each(function (Product $product) use ($offer, $targetDiscounts, $automatic, $changedProductIds) {
                $discount = $targetDiscounts->get($product->id);
                $product->fill([
                    'discount_offer_id' => $offer->id,
                    'discount_type' => $discount['discount_type'],
                    'discount_value' => $discount['discount_value'],
                    'discount_start_date' => $offer->start_date,
                    'discount_expiry_date' => $offer->end_date,
                ]);

                if (! $product->isDirty()) {
                    return;
                }

                $product->priceChangeNote = $automatic
                    ? 'Effective price changed automatically when an offer or discount period started.'
                    : 'Offer applied: '.$offer->reason;
                $product->save();
                $changedProductIds->push($product->id);
            });

        return $changedProductIds->unique()->values();
    }

    public function synchronizeScheduledOffers(): int
    {
        $today = today()->toDateString();
        $changedProductIds = collect();
        $staleProducts = Product::query()
            ->whereNotNull('discount_offer_id')
            ->whereHas('discountOffer', function (Builder $query) use ($today) {
                $query->where('is_active', false)
                    ->orWhereDate('start_date', '>', $today)
                    ->orWhereDate('end_date', '<', $today);
            })
            ->get();

        $changedProductIds->push(...$this->clearProducts(
            $staleProducts,
            'Effective price changed automatically after an offer or discount period ended.',
        ));

        DiscountOffer::query()
            ->activeNow()
            ->orderBy('start_date')
            ->orderBy('id')
            ->get()
            ->each(function (DiscountOffer $offer) use ($changedProductIds) {
                $changedProductIds->push(...$this->apply(
                    $offer,
                    $offer->assignedProducts()->pluck('id'),
                    automatic: true,
                ));
            });

        return $changedProductIds->unique()->count();
    }

    public function targetDiscounts(DiscountOffer $offer): Collection
    {
        if ($offer->scope_type === 'product') {
            return $offer->includedProducts()
                ->get(['products.id'])
                ->mapWithKeys(fn (Product $product) => [
                    $product->id => [
                        'discount_type' => $product->pivot->discount_type,
                        'discount_value' => $product->pivot->discount_value,
                    ],
                ]);
        }

        $categories = $offer->categories()->get(['categories.id']);

        if ($categories->isEmpty()) {
            return collect();
        }

        $categoryDiscounts = $categories->mapWithKeys(fn (Category $category) => [
            $category->id => [
                'discount_type' => $category->pivot->discount_type,
                'discount_value' => $category->pivot->discount_value,
            ],
        ]);
        $categoryIds = $categoryDiscounts->keys();
        $excludedIds = $offer->excludedProducts()->pluck('products.id');

        return Product::query()
            ->with('category:id,parent_id')
            ->where(function (Builder $query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds)
                    ->orWhereHas('category', fn (Builder $query) => $query->whereIn('parent_id', $categoryIds));
            })
            ->when($excludedIds->isNotEmpty(), fn (Builder $query) => $query->whereNotIn('id', $excludedIds))
            ->get(['id', 'category_id'])
            ->mapWithKeys(function (Product $product) use ($categoryDiscounts) {
                $selectedCategoryId = $categoryDiscounts->has($product->category_id)
                    ? $product->category_id
                    : $product->category?->parent_id;

                return [$product->id => $categoryDiscounts->get($selectedCategoryId)];
            });
    }

    public function clearProducts(Collection $products, string $note): Collection
    {
        $changedProductIds = collect();

        $products->each(function (Product $product) use ($note, $changedProductIds) {
            $product->fill([
                'discount_offer_id' => null,
                'discount_type' => null,
                'discount_value' => null,
                'discount_start_date' => null,
                'discount_expiry_date' => null,
            ]);

            if (! $product->isDirty()) {
                return;
            }

            $product->priceChangeNote = $note;
            $product->save();
            $changedProductIds->push($product->id);
        });

        return $changedProductIds;
    }
}

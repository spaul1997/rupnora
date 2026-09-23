<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductPriceHistory;

class ProductObserver
{
    public function created(Product $product): void
    {
        $this->record($product, 'initial', null, ProductPriceHistory::TRACKED_FIELDS, 'Product price created.');
    }

    public function updated(Product $product): void
    {
        $changedFields = array_values(array_intersect(
            ProductPriceHistory::TRACKED_FIELDS,
            array_keys($product->getChanges()),
        ));

        if ($changedFields === []) {
            return;
        }

        $previousFinalPrice = (float) ($product->getRawOriginal('final_price') ?? 0);
        $finalPrice = $product->computeFinalPrice();
        $changeType = match (true) {
            $finalPrice > $previousFinalPrice => 'increase',
            $finalPrice < $previousFinalPrice => 'decrease',
            default => 'unchanged',
        };

        $note = $product->priceChangeNote;

        if (! $note && $changedFields === ['final_price']) {
            $note = 'Effective price changed automatically after an offer or discount period ended.';
        }

        $this->record($product, $changeType, $previousFinalPrice, $changedFields, $note);
        $product->priceChangeNote = null;
    }

    private function record(Product $product, string $changeType, ?float $previousFinalPrice, array $changedFields, ?string $note): void
    {
        $actor = auth()->user();
        $admin = $actor?->role === 'admin' ? $actor : null;

        $product->priceHistories()->create([
            'changed_by' => $admin?->id,
            'source' => $admin ? 'admin' : (app()->runningInConsole() ? 'automatic' : 'system'),
            'change_type' => $changeType,
            'changed_fields' => $changedFields,
            'note' => filled($note) ? trim($note) : null,
            'mrp' => $product->mrp,
            'selling_price' => $product->selling_price,
            'offer_price' => $product->offer_price,
            'offer_expiry_date' => $product->offer_expiry_date,
            'discount_type' => $product->discount_type,
            'discount_value' => $product->discount_value,
            'discount_expiry_date' => $product->discount_expiry_date,
            'making_charge' => $product->making_charge ?? 0,
            'gst_percentage' => $product->gst_percentage ?? 0,
            'previous_final_price' => $previousFinalPrice,
            'final_price' => $product->computeFinalPrice(),
            'recorded_at' => now(),
        ]);
    }
}

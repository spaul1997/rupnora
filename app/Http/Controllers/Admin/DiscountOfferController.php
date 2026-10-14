<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveDiscountOfferRequest;
use App\Models\Category;
use App\Models\DiscountOffer;
use App\Models\Product;
use App\Services\DiscountOfferService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DiscountOfferController extends Controller
{
    public function __construct(private readonly DiscountOfferService $offerService) {}

    public function index(Request $request): View
    {
        $offers = DiscountOffer::query()
            ->with(['categories:id,name', 'includedProducts:id,name', 'excludedProducts:id,name'])
            ->when($request->search, fn (Builder $query, string $search) => $query->where('reason', 'like', "%{$search}%"))
            ->when(in_array($request->scope_type, ['category', 'product'], true), fn (Builder $query) => $query->where('scope_type', $request->scope_type))
            ->when($request->status, function (Builder $query, string $status) {
                $today = today()->toDateString();

                match ($status) {
                    'active' => $query->activeNow(),
                    'upcoming' => $query->where('is_active', true)->whereDate('start_date', '>', $today),
                    'expired' => $query->where('is_active', true)->whereDate('end_date', '<', $today),
                    'inactive' => $query->where('is_active', false),
                    default => null,
                };
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $offers->getCollection()->each(function (DiscountOffer $offer) {
            $offer->setAttribute('target_products_count', $offer->targetProductIds()->count());
        });

        return view('admin.discount-offers.index', compact('offers'));
    }

    public function create(): View
    {
        return view('admin.discount-offers.create', $this->formData());
    }

    public function store(SaveDiscountOfferRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();
            $this->offerService->assertDateRangeAvailable($data);
            $offer = DiscountOffer::create([
                ...$this->offerData($data),
                'created_by' => $request->user()->id,
            ]);

            $this->syncSelections($offer, $data);
            $this->offerService->apply($offer);
        });

        return redirect()->route('admin.discount-offers.index')->with('success', 'Offer created successfully.');
    }

    public function edit(DiscountOffer $discountOffer): View
    {
        $discountOffer->load(['categories:id', 'includedProducts:id', 'excludedProducts:id']);

        return view('admin.discount-offers.edit', [
            ...$this->formData(),
            'offer' => $discountOffer,
        ]);
    }

    public function update(SaveDiscountOfferRequest $request, DiscountOffer $discountOffer): RedirectResponse
    {
        DB::transaction(function () use ($request, $discountOffer) {
            $previousProductIds = $discountOffer->assignedProducts()->pluck('id');
            $data = $request->validated();

            $this->offerService->assertDateRangeAvailable($data, $discountOffer);
            $discountOffer->update($this->offerData($data));
            $this->syncSelections($discountOffer, $data);
            $this->offerService->apply($discountOffer, $previousProductIds);
        });

        return redirect()->route('admin.discount-offers.index')->with('success', 'Offer updated successfully.');
    }

    public function toggleActive(DiscountOffer $discountOffer): RedirectResponse
    {
        $activating = ! $discountOffer->is_active;

        try {
            DB::transaction(function () use ($discountOffer, $activating) {
                if ($activating) {
                    $this->offerService->assertDateRangeAvailable([
                        'is_active' => true,
                        'start_date' => $discountOffer->start_date,
                        'end_date' => $discountOffer->end_date,
                    ], $discountOffer);
                }

                $discountOffer->update(['is_active' => $activating]);
                $this->offerService->apply($discountOffer);
            });
        } catch (ValidationException $exception) {
            return back()->with(
                'error',
                collect($exception->errors())->flatten()->first() ?? 'The offer status could not be changed.',
            );
        }

        return back()->with('success', $activating
            ? 'Offer activated successfully.'
            : 'Offer deactivated successfully.');
    }

    public function destroy(DiscountOffer $discountOffer): RedirectResponse
    {
        DB::transaction(function () use ($discountOffer) {
            $this->offerService->clearProducts(
                $discountOffer->assignedProducts()->get(),
                'Offer removed: '.$discountOffer->reason,
            );
            $discountOffer->delete();
        });

        return redirect()->route('admin.discount-offers.index')->with('success', 'Offer removed successfully.');
    }

    private function formData(): array
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $products = Product::query()
            ->with('category:id,name,parent_id')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'category_id', 'selling_price', 'discount_type', 'discount_offer_id']);

        return compact('categories', 'products');
    }

    private function offerData(array $data): array
    {
        return [
            'reason' => $data['reason'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'scope_type' => $data['scope_type'],
            'is_active' => $data['is_active'],
        ];
    }

    private function syncSelections(DiscountOffer $offer, array $data): void
    {
        if ($offer->scope_type === 'category') {
            $offer->categories()->sync(collect($data['categories'])->mapWithKeys(function ($id) use ($data) {
                $discount = $data['category_discounts'][$id];

                return [$id => [
                    'discount_type' => $discount['discount_type'],
                    'discount_value' => $discount['discount_value'],
                ]];
            }));
            $offer->products()->sync(collect($data['excluded_products'] ?? [])->mapWithKeys(
                fn ($id) => [$id => [
                    'is_excluded' => true,
                    'discount_type' => null,
                    'discount_value' => null,
                ]]
            ));
        } else {
            $offer->categories()->detach();
            $offer->products()->sync(collect($data['products'])->mapWithKeys(function ($id) use ($data) {
                $discount = $data['product_discounts'][$id];

                return [$id => [
                    'is_excluded' => false,
                    'discount_type' => $discount['discount_type'],
                    'discount_value' => $discount['discount_value'],
                ]];
            }));
        }

        $offer->unsetRelations();
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Models\DiscountOffer;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveDiscountOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $groupedExclusions = collect((array) $this->input('excluded_products_by_category', []))
            ->flatten()
            ->all();
        $excludedProducts = array_merge(
            (array) $this->input('excluded_products', []),
            $groupedExclusions,
        );

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'categories' => array_values(array_filter((array) $this->input('categories', []))),
            'products' => array_values(array_filter((array) $this->input('products', []))),
            'excluded_products' => array_values(array_unique(array_filter($excludedProducts))),
            'category_discounts' => (array) $this->input('category_discounts', []),
            'product_discounts' => (array) $this->input('product_discounts', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'scope_type' => ['required', 'in:category,product'],
            'categories' => ['exclude_unless:scope_type,category', 'required_if:scope_type,category', 'array', 'min:1'],
            'categories.*' => [
                'integer',
                'distinct',
                Rule::exists('categories', 'id')
                    ->whereNull('parent_id')
                    ->where('is_active', true),
            ],
            'category_discounts' => ['exclude_unless:scope_type,category', 'required_if:scope_type,category', 'array'],
            'category_discounts.*.discount_type' => ['required', 'in:percentage,fixed'],
            'category_discounts.*.discount_value' => ['required', 'numeric', 'gt:0'],
            'excluded_products' => ['exclude_unless:scope_type,category', 'nullable', 'array'],
            'excluded_products.*' => ['integer', 'distinct', 'exists:products,id'],
            'products' => ['exclude_unless:scope_type,product', 'required_if:scope_type,product', 'array', 'min:1'],
            'products.*' => ['integer', 'distinct', 'exists:products,id'],
            'product_discounts' => ['exclude_unless:scope_type,product', 'required_if:scope_type,product', 'array'],
            'product_discounts.*.discount_type' => ['required', 'in:percentage,fixed'],
            'product_discounts.*.discount_value' => ['required', 'numeric', 'gt:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->validateTargetDiscounts($validator);
                $this->validateDateOverlap($validator);

                if ($this->input('scope_type') !== 'category' || $validator->errors()->has('categories')) {
                    return;
                }

                $categoryIds = collect($this->input('categories', []))->map(fn ($id) => (int) $id);
                $invalidExclusions = Product::query()
                    ->whereIn('id', $this->input('excluded_products', []))
                    ->where(function ($query) use ($categoryIds) {
                        $query->whereNotIn('category_id', $categoryIds)
                            ->whereDoesntHave('category', fn ($query) => $query->whereIn('parent_id', $categoryIds));
                    })
                    ->exists();

                if ($invalidExclusions) {
                    $validator->errors()->add('excluded_products', 'Excluded products must belong to one of the selected categories.');
                }
            },
        ];
    }

    private function validateTargetDiscounts(Validator $validator): void
    {
        $selectionKey = $this->input('scope_type') === 'product' ? 'products' : 'categories';
        $discountKey = $this->input('scope_type') === 'product' ? 'product_discounts' : 'category_discounts';
        $selectedIds = collect($this->input($selectionKey, []))->map(fn ($id) => (string) $id);
        $discounts = collect($this->input($discountKey, []));

        foreach ($selectedIds as $id) {
            if (! $discounts->has($id)) {
                $validator->errors()->add($discountKey, 'Set a discount type and value for every selected item.');
            }
        }

        foreach ($discounts as $id => $discount) {
            if (! $selectedIds->contains((string) $id)) {
                $validator->errors()->add($discountKey, 'Discount settings may only be submitted for selected items.');
            }

            if (($discount['discount_type'] ?? null) === 'percentage' && (float) ($discount['discount_value'] ?? 0) > 100) {
                $validator->errors()->add(
                    $discountKey.'.'.$id.'.discount_value',
                    'A percentage discount cannot be greater than 100%.',
                );
            }

            if (($discount['discount_type'] ?? null) === 'percentage'
                && filter_var($discount['discount_value'] ?? null, FILTER_VALIDATE_INT) === false) {
                $validator->errors()->add(
                    $discountKey.'.'.$id.'.discount_value',
                    'A percentage discount must be a whole number.',
                );
            }
        }
    }

    private function validateDateOverlap(Validator $validator): void
    {
        if (! $this->boolean('is_active') || $validator->errors()->hasAny(['start_date', 'end_date'])) {
            return;
        }

        $currentOffer = $this->route('discountOffer');
        $overlap = DiscountOffer::query()
            ->where('is_active', true)
            ->when(
                $currentOffer instanceof DiscountOffer,
                fn ($query) => $query->whereKeyNot($currentOffer->getKey()),
            )
            ->whereDate('start_date', '<=', $this->input('end_date'))
            ->whereDate('end_date', '>=', $this->input('start_date'))
            ->first(['id', 'reason', 'start_date', 'end_date']);

        if ($overlap) {
            $validator->errors()->add(
                'end_date',
                "The date range overlaps the active offer \"{$overlap->reason}\" ({$overlap->start_date->format('d M Y')} to {$overlap->end_date->format('d M Y')}).",
            );
        }
    }
}

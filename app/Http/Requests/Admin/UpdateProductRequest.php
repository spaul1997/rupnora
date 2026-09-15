<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $slugSource = $this->filled('slug') ? $this->input('slug') : $this->input('name');

        if ($slugSource) {
            $this->merge([
                'slug' => Str::slug($slugSource),
            ]);
        }
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $metalTypeRule = Rule::exists('metal_types', 'name');

        if ($this->input('metal_type') !== $product->metal_type) {
            $metalTypeRule->where(fn ($query) => $query->where('is_active', true));
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($product)],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product)],
            'barcode' => ['nullable', 'string', 'max:100'],
            'parent_category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->whereNull('parent_id')),
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')
                    ->where(fn ($query) => $query->where('parent_id', $this->input('parent_category_id'))),
            ],
            'collection' => ['required', 'array', 'min:1'],
            'collection.*' => [
                'required',
                'string',
                'max:255',
                Rule::exists('jewellery_collections', 'slug')
                    ->where(fn ($query) => $query->where('is_active', true)),
            ],
            'brand' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],

            'jewellery_type' => [
                'required',
                'string',
                'max:100',
                Rule::exists('jewellery_types', 'name')
                    ->where(fn ($query) => $query->where('is_active', true)),
            ],
            'metal_type' => [
                'required',
                'string',
                'max:255',
                $metalTypeRule,
            ],
            'finish_plating' => ['nullable', 'string', 'max:100'],
            'metal_colour' => ['nullable', 'string', 'max:100'],
            'purity' => ['nullable', 'string', 'max:20'],
            'gross_weight' => ['nullable', 'numeric', 'min:0'],
            'net_weight' => ['nullable', 'numeric', 'min:0'],
            'metal_weight' => ['nullable', 'numeric', 'min:0'],

            'has_diamond' => ['boolean'],
            'diamond_carat' => ['nullable', 'numeric', 'min:0'],
            'diamond_colour' => ['nullable', 'string', 'max:50'],
            'diamond_clarity' => ['nullable', 'string', 'max:50'],
            'diamond_cut' => ['nullable', 'string', 'max:50'],
            'diamond_shape' => ['nullable', 'string', 'max:50'],
            'diamond_count' => ['nullable', 'integer', 'min:0'],

            'has_gemstone' => ['boolean'],
            'gemstone_type' => ['nullable', 'string', 'max:100'],
            'gemstone_weight' => ['nullable', 'numeric', 'min:0'],
            'gemstone_colour' => ['nullable', 'string', 'max:50'],
            'occasion' => ['nullable', 'string', Rule::in(array_keys(Product::OCCASIONS))],
            'gender' => ['nullable', 'string', Rule::in(array_keys(Product::GENDERS))],
            'is_adjustable' => ['boolean'],
            'is_water_resistant' => ['boolean'],
            'is_return_available' => ['boolean'],
            'is_refund_available' => ['boolean'],

            'mrp' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'offer_price' => ['nullable', 'numeric', 'min:0'],
            'discount_type' => ['nullable', 'in:percentage,fixed'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'making_charge' => ['nullable', 'numeric', 'min:0'],
            'gst_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'stock_quantity' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],

            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_new_arrival' => ['boolean'],
            'is_best_seller' => ['boolean'],
            'is_trending' => ['boolean'],
            'is_on_sale' => ['boolean'],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],

            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp,image/avif', 'max:5120'],

            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.size' => ['nullable', 'string', 'max:50'],
            'variants.*.metal' => ['nullable', 'string', 'max:100'],
            'variants.*.purity' => ['nullable', 'string', 'max:20'],
            'variants.*.colour' => ['nullable', 'string', 'max:50'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.offer_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

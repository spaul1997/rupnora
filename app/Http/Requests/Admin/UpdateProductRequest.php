<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('sku_code')) {
            $this->merge(['sku_code' => trim((string) $this->input('sku_code'))]);
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
            'sku_code' => ['sometimes', 'nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9]+$/'],
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
            'has_warranty' => ['boolean'],
            'warranty_months' => ['nullable', 'required_if:has_warranty,1', 'integer', 'min:1', 'max:120'],

            'mrp' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'offer_price' => ['nullable', 'numeric', 'min:0'],
            'offer_expiry_date' => ['nullable', 'date_format:Y-m-d'],
            'discount_type' => ['nullable', 'in:percentage,fixed'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'discount_expiry_date' => ['nullable', 'date_format:Y-m-d'],
            'making_charge' => ['nullable', 'numeric', 'min:0'],
            'gst_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'price_change_note' => ['nullable', 'string', 'max:500'],

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
            'images.*' => ['bail', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/avif', 'max:5120'],

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

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->has('sku_code') || $validator->errors()->has('sku_code')) {
                    return;
                }

                /** @var Product $product */
                $product = $this->route('product');
                $sku = $product->skuWithCode($this->input('sku_code'));

                if ($sku === null) {
                    $validator->errors()->add('sku_code', 'This SKU format does not have an editable code section.');

                    return;
                }

                if (Str::length($sku) > 100) {
                    $validator->errors()->add('sku_code', 'The resulting SKU must not exceed 100 characters.');

                    return;
                }

                if (Product::query()->where('sku', $sku)->where('id', '!=', $product->getKey())->exists()) {
                    $validator->errors()->add('sku_code', 'A product with the resulting SKU already exists.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'sku_code.regex' => 'The SKU code may contain letters and numbers only.',
            'images.*.uploaded' => 'One or more product images could not be uploaded. The live server may still have a lower upload_max_filesize or post_max_size limit.',
            'images.*.max' => 'Each product image must not be greater than 5MB.',
        ];
    }
}

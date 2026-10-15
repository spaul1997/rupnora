<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShiprocketAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $phone = preg_replace('/\D+/', '', (string) $this->input('shipping_phone')) ?? '';

        if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
            $phone = substr($phone, 2);
        }

        $this->merge([
            'shipping_phone' => $phone,
            'shipping_country' => trim((string) $this->input('shipping_country', 'India')),
        ]);
    }

    public function rules(): array
    {
        return [
            'shipping_customer_name' => ['required', 'string', 'max:100'],
            'shipping_phone' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
            'shipping_address' => ['required', 'string', 'max:180'],
            'shipping_address_2' => ['nullable', 'string', 'max:180'],
            'shipping_city' => ['required', 'string', 'max:100'],
            'shipping_state' => ['required', 'string', 'max:100'],
            'shipping_country' => ['required', Rule::in(['India'])],
            'shipping_pincode' => ['required', 'regex:/^[1-9][0-9]{5}$/'],
        ];
    }
}

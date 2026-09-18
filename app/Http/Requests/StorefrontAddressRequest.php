<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorefrontAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->routeIs('checkout.addresses.*')) {
            $this->merge(['country' => 'India']);
        }
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['Home', 'Office', 'Other'])],
            'name' => ['required', 'string', 'max:100'],
            'email' => [$this->routeIs('checkout.addresses.*') ? 'required' : 'nullable', 'email', 'max:254'],
            'phone' => ['required', 'string', 'max:30'],
            'line1' => ['required', 'string', 'max:180'],
            'line2' => ['nullable', 'string', 'max:180'],
            'landmark' => ['nullable', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:100'],
            'district' => [$this->routeIs('checkout.addresses.*') ? 'required' : 'nullable', 'string', 'max:100'],
            'state' => [
                'required', 'string', 'max:100',
                ...($this->routeIs('checkout.addresses.*') ? [Rule::in(config('checkout.states'))] : []),
            ],
            'pincode' => ['required', 'regex:/^[1-9][0-9]{5}$/'],
            'country' => ['required', 'string', 'max:80'],
            'default' => ['sometimes', 'boolean'],
        ];
    }
}

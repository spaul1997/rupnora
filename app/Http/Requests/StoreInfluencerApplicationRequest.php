<?php

namespace App\Http\Requests;

use App\Models\Influencer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInfluencerApplicationRequest extends FormRequest
{
    protected $errorBag = 'influencerApplication';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'location' => ['required', 'string', 'max:120'],
            'primary_platform' => ['required', Rule::in(array_keys(Influencer::PLATFORMS))],
            'social_handle' => ['required', 'string', 'max:120'],
            'profile_url' => ['nullable', 'url:http,https', 'max:500'],
            'followers_count' => ['required', 'integer', 'min:0', 'max:2000000000'],
            'content_niche' => ['required', 'string', 'max:150'],
            'portfolio_url' => ['nullable', 'url:http,https', 'max:500'],
            'message' => ['required', 'string', 'min:20', 'max:3000'],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'primary_platform.in' => 'Please select a valid primary platform.',
            'followers_count.required' => 'Please enter your current audience size.',
            'message.min' => 'Please tell us a little more about your content and partnership goals.',
            'terms.accepted' => 'Please agree to the application terms before submitting.',
        ];
    }
}

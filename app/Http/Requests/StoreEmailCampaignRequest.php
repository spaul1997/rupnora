<?php

namespace App\Http\Requests;

use App\Models\EmailCampaign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class StoreEmailCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'template_type' => ['required', Rule::in(array_keys(EmailCampaign::TEMPLATES))],
            'subject' => ['required', 'string', 'max:255'],
            'preheader' => ['nullable', 'string', 'max:255'],
            'eyebrow' => ['nullable', 'string', 'max:100'],
            'headline' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:20', 'max:10000'],
            'highlight_text' => ['nullable', 'string', 'max:255'],
            'cta_label' => ['nullable', 'required_with:cta_url', 'string', 'max:100'],
            'cta_url' => ['nullable', 'required_with:cta_label', 'url:http,https', 'max:500'],
            'image' => ['nullable', File::image()->types(['jpeg', 'jpg', 'png', 'webp', 'avif'])->max(5 * 1024)],
            'customer_ids' => ['nullable', 'array'],
            'customer_ids.*' => ['integer', 'distinct', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'customer')->where('is_active', true))],
            'influencer_ids' => ['nullable', 'array'],
            'influencer_ids.*' => ['integer', 'distinct', Rule::exists('influencers', 'id')],
            'manual_emails' => ['nullable', 'string', 'max:50000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $manualEmails = $this->manualEmails();
                $selectedCount = count((array) $this->input('customer_ids', []))
                    + count((array) $this->input('influencer_ids', []))
                    + count($manualEmails);

                if ($selectedCount === 0) {
                    $validator->errors()->add('recipients', 'Select at least one customer or influencer, or enter a manual email address.');
                }

                $invalid = array_filter($manualEmails, fn (string $email) => ! filter_var($email, FILTER_VALIDATE_EMAIL));

                if ($invalid !== []) {
                    $validator->errors()->add('manual_emails', 'These email addresses are invalid: '.implode(', ', array_slice($invalid, 0, 5)));
                }

                if ($selectedCount > config('marketing.max_recipients_per_campaign', 2000)) {
                    $validator->errors()->add('recipients', 'A campaign can contain at most '.number_format(config('marketing.max_recipients_per_campaign', 2000)).' recipients.');
                }
            },
        ];
    }

    public function manualEmails(): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn (string $email) => strtolower(trim($email)),
            preg_split('/[\s,;]+/', (string) $this->input('manual_emails'), -1, PREG_SPLIT_NO_EMPTY) ?: []
        ))));
    }
}

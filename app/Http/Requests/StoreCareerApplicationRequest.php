<?php

namespace App\Http\Requests;

use App\Models\CareerApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreCareerApplicationRequest extends FormRequest
{
    protected $errorBag = 'careerApplication';

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
            'area_of_interest' => ['required', Rule::in(array_keys(CareerApplication::AREAS))],
            'current_role' => ['nullable', 'string', 'max:150'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:50'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:500'],
            'portfolio_url' => ['nullable', 'url:http,https', 'max:500'],
            'message' => ['required', 'string', 'min:20', 'max:3000'],
            'cv' => ['required', File::types(['pdf', 'doc', 'docx'])->max(5 * 1024)],
        ];
    }

    public function messages(): array
    {
        return [
            'area_of_interest.in' => 'Please select a valid area of interest.',
            'message.min' => 'Please tell us a little more about yourself (at least 20 characters).',
            'cv.required' => 'Please attach your CV.',
            'cv.file' => 'The CV must be a valid file.',
            'cv.mimes' => 'Upload your CV as a PDF, DOC or DOCX file.',
            'cv.max' => 'Your CV must not be larger than 5 MB.',
        ];
    }
}

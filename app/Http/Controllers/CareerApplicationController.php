<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCareerApplicationRequest;
use App\Models\CareerApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CareerApplicationController extends Controller
{
    public function store(StoreCareerApplicationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $cv = $request->file('cv');
        $cvPath = $cv->store('career-applications/'.now()->format('Y/m'), 'local');

        abort_unless($cvPath, 500, 'The CV could not be stored. Please try again.');

        try {
            $application = CareerApplication::create([
                ...Arr::except($data, ['cv']),
                'reference_no' => CareerApplication::generateReferenceNumber(),
                'user_id' => $request->user()?->id,
                'cv_path' => $cvPath,
                'cv_original_name' => $cv->getClientOriginalName(),
                'cv_mime_type' => $cv->getMimeType() ?: $cv->getClientMimeType(),
                'cv_size' => $cv->getSize(),
                'status' => 'new',
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($cvPath);

            throw $exception;
        }

        return redirect()
            ->to(route('careers').'#openings')
            ->with('career_application_success', 'Thank you for introducing yourself. Your application reference is '.$application->reference_no.'.');
    }
}

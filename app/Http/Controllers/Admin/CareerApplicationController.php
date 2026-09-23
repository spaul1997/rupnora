<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CareerApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $area = (string) $request->query('area');
        $status = (string) $request->query('status');

        $applications = CareerApplication::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('reference_no', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('current_role', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists($area, CareerApplication::AREAS), fn ($query) => $query->where('area_of_interest', $area))
            ->when(array_key_exists($status, CareerApplication::STATUSES), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.career-applications.index', [
            'applications' => $applications,
            'areas' => CareerApplication::AREAS,
            'statuses' => CareerApplication::STATUSES,
        ]);
    }

    public function show(CareerApplication $careerApplication): View
    {
        $careerApplication->load('user');

        return view('admin.career-applications.show', [
            'application' => $careerApplication,
            'statuses' => CareerApplication::STATUSES,
        ]);
    }

    public function updateStatus(Request $request, CareerApplication $careerApplication): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(CareerApplication::STATUSES))],
        ]);

        $careerApplication->update($data);

        return back()->with('success', 'Career application status updated successfully.');
    }

    public function downloadCv(CareerApplication $careerApplication): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($careerApplication->cv_path), 404);

        return Storage::disk('local')->download(
            $careerApplication->cv_path,
            $careerApplication->cv_original_name,
            ['Content-Type' => $careerApplication->cv_mime_type]
        );
    }
}

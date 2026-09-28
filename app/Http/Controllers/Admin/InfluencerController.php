<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Influencer;
use App\Support\ProductImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InfluencerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status');
        $platform = (string) $request->query('platform');
        $partner = (string) $request->query('partner');

        $influencers = Influencer::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('reference_no', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('social_handle', 'like', "%{$search}%")
                        ->orWhere('coupon_code', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists($status, Influencer::STATUSES), fn ($query) => $query->where('status', $status))
            ->when(array_key_exists($platform, Influencer::PLATFORMS), fn ($query) => $query->where('primary_platform', $platform))
            ->when(in_array($partner, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $partner === 'active'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.influencers.index', [
            'influencers' => $influencers,
            'statuses' => Influencer::STATUSES,
            'platforms' => Influencer::PLATFORMS,
            'counts' => [
                'total' => Influencer::count(),
                'new' => Influencer::where('status', 'new')->count(),
                'approved' => Influencer::where('status', 'approved')->count(),
                'active' => Influencer::active()->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.influencers.create', [
            'statuses' => Influencer::STATUSES,
            'platforms' => Influencer::PLATFORMS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data = $this->prepareData($request, $data);
        $data['reference_no'] = Influencer::generateReferenceNumber();

        if ($request->hasFile('profile_image')) {
            $data['profile_image_path'] = ProductImageOptimizer::store($request->file('profile_image'), 'influencers');
        }

        $influencer = Influencer::create($data);

        return redirect()->route('admin.influencers.show', $influencer)
            ->with('success', 'Influencer created successfully.');
    }

    public function show(Influencer $influencer): View
    {
        $influencer->load('user');

        return view('admin.influencers.show', [
            'influencer' => $influencer,
            'statuses' => Influencer::STATUSES,
        ]);
    }

    public function edit(Influencer $influencer): View
    {
        return view('admin.influencers.edit', [
            'influencer' => $influencer,
            'statuses' => Influencer::STATUSES,
            'platforms' => Influencer::PLATFORMS,
        ]);
    }

    public function update(Request $request, Influencer $influencer): RedirectResponse
    {
        $data = $this->validatedData($request, $influencer);
        $data = $this->prepareData($request, $data, $influencer);

        if ($request->hasFile('profile_image')) {
            ProductImageOptimizer::delete($influencer->profile_image_path);
            $data['profile_image_path'] = ProductImageOptimizer::store($request->file('profile_image'), 'influencers');
        }

        $influencer->update($data);

        return redirect()->route('admin.influencers.show', $influencer)
            ->with('success', 'Influencer updated successfully.');
    }

    public function destroy(Influencer $influencer): RedirectResponse
    {
        ProductImageOptimizer::delete($influencer->profile_image_path);
        $influencer->delete();

        return redirect()->route('admin.influencers.index')
            ->with('success', 'Influencer deleted successfully.');
    }

    public function updateStatus(Request $request, Influencer $influencer): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Influencer::STATUSES))],
        ]);

        if ($data['status'] === 'approved') {
            $data['approved_at'] = $influencer->approved_at ?? now();
            $data['is_active'] = true;
        } else {
            $data['is_active'] = false;
        }

        $influencer->update($data);

        return back()->with('success', 'Influencer status updated successfully.');
    }

    public function toggleActive(Influencer $influencer): RedirectResponse
    {
        if ($influencer->status !== 'approved') {
            return back()->with('error', 'Only approved influencers can be activated.');
        }

        $influencer->update(['is_active' => ! $influencer->is_active]);

        return back()->with('success', 'Influencer partner status updated successfully.');
    }

    protected function validatedData(Request $request, ?Influencer $influencer = null): array
    {
        return $request->validate([
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
            'message' => ['nullable', 'string', 'max:3000'],
            'profile_image' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/avif', 'max:5120'],
            'status' => ['required', Rule::in(array_keys(Influencer::STATUSES))],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'coupon_code' => [
                'nullable',
                'string',
                'max:50',
                'alpha_dash:ascii',
                Rule::unique('influencers', 'coupon_code')->ignore($influencer),
            ],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['boolean'],
        ]);
    }

    protected function prepareData(Request $request, array $data, ?Influencer $influencer = null): array
    {
        $data = Arr::except($data, ['profile_image']);
        $data['is_active'] = $request->boolean('is_active') && $data['status'] === 'approved';
        $data['coupon_code'] = filled($data['coupon_code'] ?? null)
            ? strtoupper((string) $data['coupon_code'])
            : null;

        if ($data['status'] === 'approved') {
            $data['approved_at'] = $influencer?->approved_at ?? now();
        } elseif ($data['status'] === 'new') {
            $data['approved_at'] = null;
        }

        return $data;
    }
}

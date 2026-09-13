<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeBanner;
use App\Support\ProductImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeBannerController extends Controller
{
    public function index(): View
    {
        $banners = HomeBanner::query()
            ->orderBy('sort_order')
            ->latest()
            ->paginate(20);

        return view('admin.home-banners.index', compact('banners'));
    }

    public function create(): View
    {
        return view('admin.home-banners.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            $data['image_path'] = ProductImageOptimizer::store($request->file('image'), 'banners');
        }

        HomeBanner::create($data);

        return redirect()->route('admin.home-banners.index')->with('success', 'Home banner created successfully.');
    }

    public function edit(HomeBanner $homeBanner): View
    {
        return view('admin.home-banners.edit', compact('homeBanner'));
    }

    public function update(Request $request, HomeBanner $homeBanner): RedirectResponse
    {
        $data = $this->validatedData($request);
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            ProductImageOptimizer::delete($homeBanner->image_path);
            $data['image_path'] = ProductImageOptimizer::store($request->file('image'), 'banners');
        }

        $homeBanner->update($data);

        return redirect()->route('admin.home-banners.index')->with('success', 'Home banner updated successfully.');
    }

    public function destroy(HomeBanner $homeBanner): RedirectResponse
    {
        ProductImageOptimizer::delete($homeBanner->image_path);
        $homeBanner->delete();

        return redirect()->route('admin.home-banners.index')->with('success', 'Home banner deleted successfully.');
    }

    public function toggleActive(HomeBanner $homeBanner): RedirectResponse
    {
        $homeBanner->update(['is_active' => ! $homeBanner->is_active]);

        return back()->with('success', 'Home banner status updated successfully.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'heading' => ['required', 'string', 'max:255'],
            'subheading' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/avif', 'max:5120'],
            'primary_label' => ['nullable', 'string', 'max:100'],
            'primary_url' => ['nullable', 'string', 'max:255'],
            'secondary_label' => ['nullable', 'string', 'max:100'],
            'secondary_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
    }
}

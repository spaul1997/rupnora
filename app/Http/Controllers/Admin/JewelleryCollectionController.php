<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JewelleryCollection;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JewelleryCollectionController extends Controller
{
    public function index(Request $request): View
    {
        $collections = JewelleryCollection::query()
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $collections->getCollection()->each(function (JewelleryCollection $collection) {
            $collection->setAttribute('products_count', Product::query()->whereCollectionSlug($collection->slug)->count());
        });

        return view('admin.jewellery-collections.index', compact('collections'));
    }

    public function create(): View
    {
        return view('admin.jewellery-collections.create');
    }

    public function store(Request $request): RedirectResponse
    {
        JewelleryCollection::create($this->validatedData($request));

        return redirect()->route('admin.jewellery-collections.index')->with('success', 'Collection created successfully.');
    }

    public function edit(JewelleryCollection $jewelleryCollection): View
    {
        return view('admin.jewellery-collections.edit', [
            'collection' => $jewelleryCollection,
        ]);
    }

    public function update(Request $request, JewelleryCollection $jewelleryCollection): RedirectResponse
    {
        $jewelleryCollection->update($this->validatedData($request, $jewelleryCollection));

        return redirect()->route('admin.jewellery-collections.index')->with('success', 'Collection updated successfully.');
    }

    public function destroy(JewelleryCollection $jewelleryCollection): RedirectResponse
    {
        $hasProducts = Product::query()
            ->whereCollectionSlug($jewelleryCollection->slug)
            ->exists();

        if ($hasProducts) {
            return back()->with('error', 'Cannot delete a collection assigned to products.');
        }

        $jewelleryCollection->delete();

        return redirect()->route('admin.jewellery-collections.index')->with('success', 'Collection deleted successfully.');
    }

    public function toggleActive(JewelleryCollection $jewelleryCollection): RedirectResponse
    {
        $jewelleryCollection->update(['is_active' => ! $jewelleryCollection->is_active]);

        return back()->with('success', 'Collection status updated successfully.');
    }

    protected function validatedData(Request $request, ?JewelleryCollection $collection = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('jewellery_collections', 'slug')->ignore($collection),
            ],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $data['slug'] = $data['slug'] ?? '' ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}

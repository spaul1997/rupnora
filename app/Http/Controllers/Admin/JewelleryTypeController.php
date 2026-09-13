<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JewelleryType;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JewelleryTypeController extends Controller
{
    public function index(Request $request): View
    {
        $types = JewelleryType::query()
            ->withCount('products')
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.jewellery-types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.jewellery-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        JewelleryType::create($this->validatedData($request));

        return redirect()->route('admin.jewellery-types.index')->with('success', 'Jewellery type created successfully.');
    }

    public function edit(JewelleryType $jewelleryType): View
    {
        return view('admin.jewellery-types.edit', [
            'type' => $jewelleryType,
        ]);
    }

    public function update(Request $request, JewelleryType $jewelleryType): RedirectResponse
    {
        $jewelleryType->update($this->validatedData($request, $jewelleryType));

        return redirect()->route('admin.jewellery-types.index')->with('success', 'Jewellery type updated successfully.');
    }

    public function destroy(JewelleryType $jewelleryType): RedirectResponse
    {
        $hasProducts = Product::query()
            ->where('jewellery_type', $jewelleryType->name)
            ->exists();

        if ($hasProducts) {
            return back()->with('error', 'Cannot delete a jewellery type assigned to products.');
        }

        $jewelleryType->delete();

        return redirect()->route('admin.jewellery-types.index')->with('success', 'Jewellery type deleted successfully.');
    }

    public function toggleActive(JewelleryType $jewelleryType): RedirectResponse
    {
        $jewelleryType->update(['is_active' => ! $jewelleryType->is_active]);

        return back()->with('success', 'Jewellery type status updated successfully.');
    }

    protected function validatedData(Request $request, ?JewelleryType $type = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('jewellery_types', 'slug')->ignore($type),
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

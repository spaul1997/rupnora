<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MetalType;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MetalTypeController extends Controller
{
    public function index(Request $request): View
    {
        $types = MetalType::query()
            ->withCount('products')
            ->when($request->search, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.metal-types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.metal-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        MetalType::create($this->validatedData($request));

        return redirect()->route('admin.metal-types.index')->with('success', 'Metal type created successfully.');
    }

    public function edit(MetalType $metalType): View
    {
        return view('admin.metal-types.edit', [
            'type' => $metalType,
        ]);
    }

    public function update(Request $request, MetalType $metalType): RedirectResponse
    {
        $data = $this->validatedData($request, $metalType);

        DB::transaction(function () use ($metalType, $data) {
            $previousName = $metalType->name;

            $metalType->update($data);

            if ($previousName !== $metalType->name) {
                Product::query()
                    ->where('metal_type', $previousName)
                    ->update(['metal_type' => $metalType->name]);
            }
        });

        return redirect()->route('admin.metal-types.index')->with('success', 'Metal type updated successfully.');
    }

    public function destroy(MetalType $metalType): RedirectResponse
    {
        $hasProducts = Product::query()
            ->where('metal_type', $metalType->name)
            ->exists();

        if ($hasProducts) {
            return back()->with('error', 'Cannot delete a metal type assigned to products.');
        }

        $metalType->delete();

        return redirect()->route('admin.metal-types.index')->with('success', 'Metal type deleted successfully.');
    }

    public function toggleActive(MetalType $metalType): RedirectResponse
    {
        $metalType->update(['is_active' => ! $metalType->is_active]);

        return back()->with('success', 'Metal type status updated successfully.');
    }

    protected function validatedData(Request $request, ?MetalType $type = null): array
    {
        $request->merge([
            'slug' => Str::slug($request->filled('slug') ? $request->string('slug')->toString() : $request->string('name')->toString()),
        ]);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('metal_types', 'name')->ignore($type),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('metal_types', 'slug')->ignore($type),
            ],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}

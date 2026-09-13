<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Support\ProductImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->with('parent')
            ->withCount('products')
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->when($request->parent_id === 'parents', function ($q) {
                $q->whereNull('parent_id');
            })
            ->when($request->parent_id && $request->parent_id !== 'parents', fn ($q) => $q->where('parent_id', $request->parent_id))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $parents = Category::parents()->orderBy('name')->get(['id', 'name']);

        return view('admin.categories.index', compact('categories', 'parents'));
    }

    public function create(): View
    {
        $parents = Category::parents()->orderBy('name')->get(['id', 'name']);

        return view('admin.categories.create', compact('parents'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['show_in_header'] = $request->boolean('show_in_header');

        if ($request->hasFile('image')) {
            $data['image'] = ProductImageOptimizer::store($request->file('image'), 'categories');
        }
        if ($request->hasFile('banner')) {
            $data['banner'] = ProductImageOptimizer::store($request->file('banner'), 'categories');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category): View
    {
        $parents = Category::parents()->where('id', '!=', $category->id)->orderBy('name')->get(['id', 'name']);

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['show_in_header'] = $request->boolean('show_in_header');

        if ($request->hasFile('image')) {
            ProductImageOptimizer::delete($category->image);
            $data['image'] = ProductImageOptimizer::store($request->file('image'), 'categories');
        }
        if ($request->hasFile('banner')) {
            ProductImageOptimizer::delete($category->banner);
            $data['banner'] = ProductImageOptimizer::store($request->file('banner'), 'categories');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists()) {
            return back()->with('error', 'Cannot delete a category that has subcategories.');
        }

        if ($category->products()->exists()) {
            return back()->with('error', 'Cannot delete a category that has products assigned to it.');
        }

        ProductImageOptimizer::delete($category->image);
        ProductImageOptimizer::delete($category->banner);

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }

    public function toggleActive(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('success', 'Category status updated successfully.');
    }
}

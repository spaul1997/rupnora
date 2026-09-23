<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\JewelleryCollection;
use App\Models\JewelleryType;
use App\Models\MetalType;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Support\ProductImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public const PURITIES = ['14K', '18K', '22K', '24K'];

    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('category')
            ->withCount('reviews')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->metal_type, fn ($q, $metal) => $q->where('metal_type', $metal))
            ->when($request->purity, fn ($q, $purity) => $q->where('purity', $purity))
            ->when($request->stock_status, fn ($q, $status) => $q->where('stock_status', $status))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->filled('is_featured'), fn ($q) => $q->where('is_featured', $request->boolean('is_featured')))
            ->when($request->filled('is_new_arrival'), fn ($q) => $q->where('is_new_arrival', $request->boolean('is_new_arrival')))
            ->when($request->filled('is_best_seller'), fn ($q) => $q->where('is_best_seller', $request->boolean('is_best_seller')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = Category::orderBy('name')->get(['id', 'name']);
        $metalTypes = MetalType::query()->orderBy('sort_order')->orderBy('name')->get(['name']);

        return view('admin.products.index', compact('products', 'categories', 'metalTypes'));
    }

    public function create(): View
    {
        $parentCategories = Category::parents()->orderBy('name')->get(['id', 'name']);
        $subcategories = Category::query()->whereNotNull('parent_id')->orderBy('name')->get(['id', 'name', 'parent_id']);
        $jewelleryTypes = JewelleryType::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']);
        $metalTypes = MetalType::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug', 'is_active']);
        $collections = JewelleryCollection::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']);

        return view('admin.products.create', [
            'parentCategories' => $parentCategories,
            'subcategories' => $subcategories,
            'jewelleryTypes' => $jewelleryTypes,
            'collections' => $collections,
            'metalTypes' => $metalTypes,
            'purities' => self::PURITIES,
            'genderOptions' => Product::GENDERS,
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        try {
            $product = DB::transaction(function () use ($request) {
                $data = $this->prepareData($request);

                $product = Product::create($data);

                $this->syncImages($product, $request);
                $this->syncVariants($product, $request->input('variants', []));

                return $product;
            });
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'The product could not be created. Please verify the details and uploaded images, then try again.');
        }

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'images', 'variants', 'reviews.customer']);
        $priceHistory = $product->priceHistories()
            ->with('changedBy:id,name')
            ->limit(100)
            ->get();
        $priceHistoryTotal = $product->priceHistories()->count();

        return view('admin.products.show', compact('product', 'priceHistory', 'priceHistoryTotal'));
    }

    public function edit(Product $product): View
    {
        $product->load(['category.parent', 'images', 'variants']);
        $parentCategories = Category::parents()->orderBy('name')->get(['id', 'name']);
        $subcategories = Category::query()->whereNotNull('parent_id')->orderBy('name')->get(['id', 'name', 'parent_id']);
        $jewelleryTypes = JewelleryType::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']);
        $metalTypes = MetalType::query()
            ->where(fn ($query) => $query->where('is_active', true)->orWhere('name', $product->metal_type))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'is_active']);
        $collections = JewelleryCollection::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']);

        return view('admin.products.edit', [
            'product' => $product,
            'parentCategories' => $parentCategories,
            'subcategories' => $subcategories,
            'jewelleryTypes' => $jewelleryTypes,
            'collections' => $collections,
            'metalTypes' => $metalTypes,
            'purities' => self::PURITIES,
            'genderOptions' => Product::GENDERS,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($request, $product) {
            $data = $this->prepareData($request);

            $product->priceChangeNote = $request->input('price_change_note');
            $product->update($data);

            $this->syncImages($product, $request);
            $this->syncVariants($product, $request->input('variants', []));
        });

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        DB::transaction(function () use ($product) {
            foreach ($product->images as $image) {
                ProductImageOptimizer::delete($image->image_path);
            }
            $product->delete();
        });

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    public function duplicate(Product $product): RedirectResponse
    {
        $copy = DB::transaction(function () use ($product) {
            $new = $product->replicate(['slug', 'sku']);
            $new->name = $product->name.' (Copy)';
            $new->slug = Str::slug($new->name).'-'.Str::random(5);
            $new->sku = $product->sku.'-COPY-'.Str::upper(Str::random(4));
            $new->is_active = false;
            $new->save();

            foreach ($product->variants as $variant) {
                $newVariant = $variant->replicate(['sku']);
                $newVariant->product_id = $new->id;
                $newVariant->sku = $variant->sku.'-COPY';
                $newVariant->save();
            }

            return $new;
        });

        return redirect()->route('admin.products.edit', $copy)->with('success', 'Product duplicated successfully. Review and activate it when ready.');
    }

    public function toggleActive(Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('success', 'Product status updated successfully.');
    }

    public function toggleFeatured(Product $product): RedirectResponse
    {
        $product->update(['is_featured' => ! $product->is_featured]);

        return back()->with('success', 'Product featured flag updated successfully.');
    }

    public function destroyImage(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        ProductImageOptimizer::delete($image->image_path);
        $image->delete();

        return back()->with('success', 'Image removed successfully.');
    }

    public function setPrimaryImage(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        DB::transaction(function () use ($product, $image) {
            $product->images()->update(['is_primary' => false]);
            $image->update(['is_primary' => true]);
        });

        return back()->with('success', 'Primary image updated successfully.');
    }

    public function export(): StreamedResponse
    {
        $filename = 'products-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['SKU', 'Name', 'Category', 'Metal', 'Purity', 'Selling Price', 'Stock', 'Status']);

            Product::with('category')->orderBy('name')->chunk(200, function ($products) use ($handle) {
                foreach ($products as $product) {
                    fputcsv($handle, [
                        $product->sku,
                        $product->name,
                        $product->category?->name,
                        $product->metal_type,
                        $product->purity,
                        $product->selling_price,
                        $product->stock_quantity,
                        $product->is_active ? 'Active' : 'Inactive',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function prepareData(Request $request): array
    {
        $data = $request->only([
            'name', 'slug', 'sku', 'barcode', 'category_id', 'brand', 'short_description', 'description',
            'jewellery_type', 'metal_type', 'finish_plating', 'metal_colour', 'purity', 'gross_weight', 'net_weight', 'metal_weight',
            'diamond_carat', 'diamond_colour', 'diamond_clarity', 'diamond_cut', 'diamond_shape', 'diamond_count',
            'gemstone_type', 'gemstone_weight', 'gemstone_colour',
            'occasion', 'gender',
            'mrp', 'selling_price', 'offer_price', 'offer_expiry_date', 'discount_type', 'discount_value', 'discount_expiry_date', 'making_charge', 'gst_percentage',
            'stock_quantity', 'minimum_stock',
            'meta_title', 'meta_description', 'meta_keywords',
        ]);

        $data['slug'] = $data['slug'] ?? '' ?: Str::slug($data['name']);
        $data['collection'] = json_encode(array_values(array_unique($request->input('collection', []))));
        $data['has_diamond'] = $request->boolean('has_diamond');
        $data['has_gemstone'] = $request->boolean('has_gemstone');
        $data['is_adjustable'] = $request->boolean('is_adjustable');
        $data['is_water_resistant'] = $request->boolean('is_water_resistant');
        $data['is_return_available'] = $request->boolean('is_return_available');
        $data['is_refund_available'] = $request->boolean('is_refund_available');
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_new_arrival'] = $request->boolean('is_new_arrival');
        $data['is_best_seller'] = $request->boolean('is_best_seller');
        $data['is_trending'] = $request->boolean('is_trending');
        $data['is_on_sale'] = $request->boolean('is_on_sale');
        $data['minimum_stock'] = $this->defaultWhenBlank($data['minimum_stock'] ?? null, 5);
        $data['gst_percentage'] = $this->defaultWhenBlank($data['gst_percentage'] ?? null, 0);
        $data['making_charge'] = $this->defaultWhenBlank($data['making_charge'] ?? null, 0);

        return $data;
    }

    protected function defaultWhenBlank(mixed $value, mixed $default): mixed
    {
        return $value === null || $value === '' ? $default : $value;
    }

    protected function syncImages(Product $product, Request $request): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $existingCount = $product->images()->count();

        foreach ($request->file('images') as $i => $file) {
            $path = ProductImageOptimizer::store($file);

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'is_primary' => $existingCount === 0 && $i === 0,
                'sort_order' => $existingCount + $i,
            ]);
        }
    }

    protected function syncVariants(Product $product, array $variants): void
    {
        $product->variants()->delete();

        foreach ($variants as $variant) {
            if (empty(array_filter($variant))) {
                continue;
            }

            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $product->sku.'-'.Str::upper(Str::random(4)),
                'size' => $variant['size'] ?? null,
                'metal' => $variant['metal'] ?? null,
                'purity' => $variant['purity'] ?? null,
                'colour' => $variant['colour'] ?? null,
                'price' => $variant['price'] ?? null,
                'offer_price' => $variant['offer_price'] ?? null,
                'stock_quantity' => $variant['stock_quantity'] ?? 0,
                'status' => 'active',
            ]);
        }
    }
}

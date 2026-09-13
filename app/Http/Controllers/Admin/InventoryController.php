<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('category')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($request->stock_status, fn ($q, $status) => $q->where('stock_status', $status))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->orderBy('stock_quantity')
            ->paginate(20)
            ->withQueryString();

        return view('admin.inventory.index', compact('products'));
    }

    public function adjust(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'type' => ['required', 'in:add,remove,correction'],
            'quantity' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
            'remark' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            $variant = ! empty($data['product_variant_id']) ? ProductVariant::lockForUpdate()->find($data['product_variant_id']) : null;

            $target = $variant ?: $product;
            $previousStock = $target->stock_quantity;

            $newStock = match ($data['type']) {
                'add' => $previousStock + $data['quantity'],
                'remove' => max(0, $previousStock - $data['quantity']),
                'correction' => $data['quantity'],
            };

            $target->update(['stock_quantity' => $newStock]);

            if ($variant) {
                $product->update(['stock_quantity' => $product->variants()->sum('stock_quantity')]);
            }

            StockAdjustment::create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'type' => $data['type'],
                'quantity' => $data['quantity'],
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $data['reason'] ?? null,
                'remark' => $data['remark'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Stock adjusted successfully.');
    }

    public function history(Product $product): View
    {
        $adjustments = $product->stockAdjustments()
            ->with(['createdBy', 'variant'])
            ->latest()
            ->paginate(20);

        return view('admin.inventory.history', compact('product', 'adjustments'));
    }
}

@extends('admin.layouts.app')

@section('title', 'Inventory')

@section('content')
    <x-admin.page-header title="Inventory" description="Track stock levels and make adjustments." :breadcrumb="[['label' => 'Products', 'url' => route('admin.products.index')], ['label' => 'Inventory']]" />

    <form method="GET" class="admin-card mb-5 flex flex-wrap items-center gap-3 p-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or SKU..." class="admin-input max-w-xs">
        <select name="stock_status" class="admin-select max-w-[160px]">
            <option value="">All Stock</option>
            <option value="in_stock" @selected(request('stock_status') === 'in_stock')>In Stock</option>
            <option value="low_stock" @selected(request('stock_status') === 'low_stock')>Low Stock</option>
            <option value="out_of_stock" @selected(request('stock_status') === 'out_of_stock')>Out of Stock</option>
        </select>
        <button type="submit" class="admin-btn-secondary">Filter</button>
        @if (request()->hasAny(['search', 'stock_status']))
            <a href="{{ route('admin.inventory.index') }}" class="admin-btn-ghost">Clear</a>
        @endif
    </form>

    <x-admin.table :headers="['Product', 'SKU', 'Current Stock', 'Minimum Stock', 'Status', '!Actions']">
        @foreach ($products as $product)
            <tr x-data="{ adjustOpen: false, type: 'add' }">
                <td class="font-medium text-gray-900">{{ $product->name }}</td>
                <td class="text-gray-500">{{ $product->sku }}</td>
                <td>{{ $product->stock_quantity }}</td>
                <td>{{ $product->minimum_stock }}</td>
                <td><x-admin.status-badge :status="$product->stock_status" /></td>
                <td class="text-right">
                    <div class="flex items-center justify-end gap-3">
                        <button @click="adjustOpen = true" class="text-sm font-medium text-champagne-dark hover:underline">Adjust Stock</button>
                        <a href="{{ route('admin.inventory.history', $product) }}" class="text-sm font-medium text-gray-500 hover:text-gray-800">History</a>
                    </div>

                    <x-admin.modal model="adjustOpen" title="Adjust Stock — {{ $product->name }}" max-width="max-w-md">
                        <form method="POST" action="{{ route('admin.inventory.adjust') }}" class="space-y-4 text-left">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <div>
                                <label class="admin-label">Adjustment Type</label>
                                <div class="flex gap-2">
                                    <label class="flex-1"><input type="radio" name="type" value="add" x-model="type" class="peer sr-only"><span class="block cursor-pointer rounded-lg border border-gray-300 py-2 text-center text-sm peer-checked:border-champagne-dark peer-checked:bg-champagne-light/30">Add</span></label>
                                    <label class="flex-1"><input type="radio" name="type" value="remove" x-model="type" class="peer sr-only"><span class="block cursor-pointer rounded-lg border border-gray-300 py-2 text-center text-sm peer-checked:border-champagne-dark peer-checked:bg-champagne-light/30">Remove</span></label>
                                    <label class="flex-1"><input type="radio" name="type" value="correction" x-model="type" class="peer sr-only"><span class="block cursor-pointer rounded-lg border border-gray-300 py-2 text-center text-sm peer-checked:border-champagne-dark peer-checked:bg-champagne-light/30">Correction</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="admin-label" x-text="type === 'correction' ? 'New Stock Quantity' : 'Quantity'"></label>
                                <input type="number" name="quantity" min="0" required class="admin-input">
                            </div>
                            <div>
                                <label class="admin-label">Reason</label>
                                <input type="text" name="reason" class="admin-input" placeholder="e.g. New stock received, Damaged item">
                            </div>
                            <div>
                                <label class="admin-label">Remark</label>
                                <textarea name="remark" rows="2" class="admin-textarea resize-none"></textarea>
                            </div>
                            <button type="submit" class="admin-btn-primary w-full">Save Adjustment</button>
                        </form>
                    </x-admin.modal>
                </td>
            </tr>
        @endforeach

        <x-slot:footer>
            <x-admin.pagination :paginator="$products" />
        </x-slot:footer>
    </x-admin.table>
@endsection

<input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or SKU..." class="admin-input !py-2 lg:min-w-[200px] lg:flex-1">

<x-admin.searchable-select
    name="category_id"
    :options="$categories->pluck('name', 'id')"
    :selected="request('category_id')"
    placeholder="All Categories"
    search-placeholder="Search categories..."
    compact
    class="lg:w-44"
/>

<x-admin.searchable-select
    name="metal_type"
    :options="$metalTypes->pluck('name', 'name')"
    :selected="request('metal_type')"
    placeholder="All Metals"
    search-placeholder="Search metals..."
    compact
    class="lg:w-36"
/>

<x-admin.searchable-select
    name="stock_status"
    :options="[
        'in_stock' => 'In Stock',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
    ]"
    :selected="request('stock_status')"
    placeholder="All Stock"
    search-placeholder="Search stock..."
    compact
    class="lg:w-36"
/>

<x-admin.searchable-select
    name="is_active"
    :options="['1' => 'Active', '0' => 'Inactive']"
    :selected="request('is_active')"
    placeholder="All Status"
    search-placeholder="Search status..."
    compact
    class="lg:w-32"
/>

<div class="flex flex-wrap items-center gap-x-4 gap-y-2">
    <label class="flex items-center gap-1.5 whitespace-nowrap text-sm text-gray-600">
        <input type="checkbox" name="is_featured" value="1" @checked(request('is_featured')) class="h-4 w-4 rounded border-gray-300 text-champagne-dark">
        Featured
    </label>
    <label class="flex items-center gap-1.5 whitespace-nowrap text-sm text-gray-600">
        <input type="checkbox" name="is_new_arrival" value="1" @checked(request('is_new_arrival')) class="h-4 w-4 rounded border-gray-300 text-champagne-dark">
        New Arrival
    </label>
    <label class="flex items-center gap-1.5 whitespace-nowrap text-sm text-gray-600">
        <input type="checkbox" name="is_best_seller" value="1" @checked(request('is_best_seller')) class="h-4 w-4 rounded border-gray-300 text-champagne-dark">
        Best Seller
    </label>
</div>

<div class="flex items-center gap-2">
    <button type="submit" class="admin-btn-secondary">Apply</button>
    @if (request()->hasAny(['search', 'category_id', 'metal_type', 'stock_status', 'is_active', 'is_featured', 'is_new_arrival', 'is_best_seller']))
        <a href="{{ route('admin.products.index') }}" class="admin-btn-ghost">Clear</a>
    @endif
</div>

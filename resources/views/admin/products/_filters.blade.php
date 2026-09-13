<input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or SKU..." class="admin-input lg:max-w-xs">

<select name="category_id" class="admin-select lg:max-w-[160px]">
    <option value="">All Categories</option>
    @foreach ($categories as $cat)
        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
    @endforeach
</select>

<select name="metal_type" class="admin-select lg:max-w-[140px]">
    <option value="">All Metals</option>
    @foreach (\App\Http\Controllers\Admin\ProductController::METAL_TYPES as $metal)
        <option value="{{ $metal }}" @selected(request('metal_type') === $metal)>{{ $metal }}</option>
    @endforeach
</select>

<select name="purity" class="admin-select lg:max-w-[120px]">
    <option value="">All Purity</option>
    @foreach (\App\Http\Controllers\Admin\ProductController::PURITIES as $purity)
        <option value="{{ $purity }}" @selected(request('purity') === $purity)>{{ $purity }}</option>
    @endforeach
</select>

<select name="stock_status" class="admin-select lg:max-w-[140px]">
    <option value="">All Stock</option>
    <option value="in_stock" @selected(request('stock_status') === 'in_stock')>In Stock</option>
    <option value="low_stock" @selected(request('stock_status') === 'low_stock')>Low Stock</option>
    <option value="out_of_stock" @selected(request('stock_status') === 'out_of_stock')>Out of Stock</option>
</select>

<select name="is_active" class="admin-select lg:max-w-[130px]">
    <option value="">Active/Inactive</option>
    <option value="1" @selected(request('is_active') === '1')>Active</option>
    <option value="0" @selected(request('is_active') === '0')>Inactive</option>
</select>

<label class="flex items-center gap-1.5 text-sm text-gray-600">
    <input type="checkbox" name="is_featured" value="1" @checked(request('is_featured')) class="h-4 w-4 rounded border-gray-300 text-champagne-dark">
    Featured
</label>
<label class="flex items-center gap-1.5 text-sm text-gray-600">
    <input type="checkbox" name="is_new_arrival" value="1" @checked(request('is_new_arrival')) class="h-4 w-4 rounded border-gray-300 text-champagne-dark">
    New Arrival
</label>
<label class="flex items-center gap-1.5 text-sm text-gray-600">
    <input type="checkbox" name="is_best_seller" value="1" @checked(request('is_best_seller')) class="h-4 w-4 rounded border-gray-300 text-champagne-dark">
    Best Seller
</label>

<button type="submit" class="admin-btn-secondary">Apply</button>
@if (request()->hasAny(['search', 'category_id', 'metal_type', 'purity', 'stock_status', 'is_active', 'is_featured', 'is_new_arrival', 'is_best_seller']))
    <a href="{{ route('admin.products.index') }}" class="admin-btn-ghost">Clear</a>
@endif

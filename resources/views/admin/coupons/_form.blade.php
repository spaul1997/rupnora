@php($coupon = $coupon ?? null)

@csrf
@if ($coupon)
    @method('PUT')
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <div class="admin-card space-y-5 p-6">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-admin.form.input label="Coupon Code" name="code" :value="$coupon?->code" required help="Will be saved in uppercase." />
                <x-admin.form.select label="Discount Type" name="discount_type" required :value="$coupon?->discount_type" :options="['percentage' => 'Percentage', 'fixed' => 'Fixed Amount']" />
            </div>
            <x-admin.form.textarea label="Description" name="description" :value="$coupon?->description" :rows="2" />
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-admin.form.input label="Discount Value" name="discount_value" type="number" step="0.01" required :value="$coupon?->discount_value" />
                <x-admin.form.input label="Maximum Discount (₹)" name="maximum_discount" type="number" step="0.01" :value="$coupon?->maximum_discount" help="Applicable for percentage discounts." />
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-admin.form.input label="Minimum Order Value (₹)" name="minimum_order" type="number" step="0.01" :value="$coupon?->minimum_order" />
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-admin.form.input label="Start Date" name="start_date" type="date" required :value="$coupon?->start_date?->format('Y-m-d')" />
                <x-admin.form.input label="End Date" name="end_date" type="date" required :value="$coupon?->end_date?->format('Y-m-d')" />
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-admin.form.input label="Usage Limit (Total)" name="usage_limit" type="number" :value="$coupon?->usage_limit" help="Leave blank for unlimited." />
                <x-admin.form.input label="Usage Limit (Per Customer)" name="usage_per_customer" type="number" :value="$coupon?->usage_per_customer ?? 1" />
            </div>
        </div>

        <div class="admin-card space-y-4 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Applicable Categories</h3>
            <p class="text-xs text-gray-400">Leave empty to apply to all categories.</p>
            <div class="grid max-h-48 grid-cols-2 gap-2 overflow-y-auto sm:grid-cols-3">
                @php($selectedCategories = old('categories', $coupon?->categories->pluck('id')->all() ?? []))
                @foreach ($categories as $cat)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="categories[]" value="{{ $cat->id }}" @checked(in_array($cat->id, $selectedCategories)) class="h-4 w-4 rounded border-gray-300 text-champagne-dark">
                        {{ $cat->name }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="admin-card space-y-4 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Applicable Products</h3>
            <p class="text-xs text-gray-400">Leave empty to apply to all products.</p>
            <div class="grid max-h-64 grid-cols-1 gap-2 overflow-y-auto sm:grid-cols-2">
                @php($selectedProducts = old('products', $coupon?->products->pluck('id')->all() ?? []))
                @foreach ($products as $product)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="products[]" value="{{ $product->id }}" @checked(in_array($product->id, $selectedProducts)) class="h-4 w-4 rounded border-gray-300 text-champagne-dark">
                        {{ $product->name }}
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="admin-card space-y-4 p-6">
            <label class="flex items-center gap-2.5 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon?->is_active ?? true)) class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                Active
            </label>
        </div>
        <button type="submit" class="admin-btn-primary w-full">{{ $coupon ? 'Update Coupon' : 'Create Coupon' }}</button>
    </div>
</div>

@csrf
@if (isset($category))
    @method('PUT')
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <div class="admin-card space-y-5 p-6">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-admin.form.input label="Category Name" name="name" :value="$category->name ?? null" required />
                <x-admin.form.input label="Slug" name="slug" :value="$category->slug ?? null" help="Leave blank to auto-generate from name." />
            </div>
            <x-admin.form.select label="Parent Category" name="parent_id" :value="$category->parent_id ?? null" :options="$parents->pluck('name', 'id')" placeholder="None (Top-level category)" />
            <x-admin.form.textarea label="Description" name="description" :value="$category->description ?? null" :rows="4" />
        </div>

        <div class="admin-card space-y-5 p-6">
            <h3 class="text-sm font-semibold text-gray-900">SEO</h3>
            <x-admin.form.input label="Meta Title" name="meta_title" :value="$category->meta_title ?? null" />
            <x-admin.form.textarea label="Meta Description" name="meta_description" :value="$category->meta_description ?? null" :rows="3" />
        </div>
    </div>

    <div class="space-y-6">
        <div class="admin-card space-y-5 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Images</h3>
            <div>
                <label class="admin-label">Category Image</label>
                @if (! empty($category?->image))
                    <x-ui.optimized-image :src="asset('storage/'.$category->image)" alt="" sizes="96px" class="mb-2 h-24 w-24 rounded-lg border border-gray-200 object-cover" />
                @endif
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/avif" class="admin-input">
                <p class="mt-1 text-xs text-gray-400">JPG, PNG, WebP or AVIF. Max 5MB. Optimized to responsive AVIF/WebP files.</p>
            </div>
            <div>
                <label class="admin-label">Banner Image</label>
                @if (! empty($category?->banner))
                    <x-ui.optimized-image :src="asset('storage/'.$category->banner)" alt="" sizes="320px" class="mb-2 h-16 w-full rounded-lg border border-gray-200 object-cover" />
                @endif
                <input type="file" name="banner" accept="image/jpeg,image/png,image/webp,image/avif" class="admin-input">
                <p class="mt-1 text-xs text-gray-400">JPG, PNG, WebP or AVIF. Max 5MB. Wide images work best.</p>
            </div>
        </div>

        <div class="admin-card space-y-5 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Display Settings</h3>
            <x-admin.form.input label="Sort Order" name="sort_order" type="number" :value="$category->sort_order ?? 0" />
            <label class="flex items-center gap-2.5 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true)) class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                Active (visible on storefront)
            </label>
            <label class="flex items-center gap-2.5 text-sm text-gray-700">
                <input type="checkbox" name="show_in_header" value="1" @checked(old('show_in_header', $category->show_in_header ?? true)) class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                Show in header
            </label>
        </div>

        <button type="submit" class="admin-btn-primary w-full">{{ isset($category) ? 'Update Category' : 'Create Category' }}</button>
    </div>
</div>

@csrf
@if (isset($collection))
    @method('PUT')
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <div class="admin-card space-y-5 p-6">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-admin.form.input label="Collection Name" name="name" :value="$collection->name ?? null" required />
                <x-admin.form.input label="Slug" name="slug" :value="$collection->slug ?? null" help="Leave blank to auto-generate from name." />
            </div>
            <x-admin.form.textarea label="Description" name="description" :value="$collection->description ?? null" :rows="4" />
        </div>
    </div>

    <div class="space-y-6">
        <div class="admin-card space-y-5 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Display Settings</h3>
            <x-admin.form.input label="Sort Order" name="sort_order" type="number" :value="$collection->sort_order ?? 0" />
            <label class="flex items-center gap-2.5 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $collection->is_active ?? true)) class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                Active
            </label>
        </div>

        <button type="submit" class="admin-btn-primary w-full">{{ isset($collection) ? 'Update Collection' : 'Create Collection' }}</button>
    </div>
</div>

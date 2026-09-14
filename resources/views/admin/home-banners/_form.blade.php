@php($banner = $banner ?? null)

@csrf
@if ($banner)
    @method('PUT')
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <div class="admin-card space-y-5 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Banner Content</h3>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-admin.form.input label="Eyebrow" name="eyebrow" :value="$banner?->eyebrow" placeholder="e.g. Festive Edit" />
                <x-admin.form.input label="Heading" name="heading" :value="$banner?->heading" required />
            </div>
            <x-admin.form.textarea label="Subheading" name="subheading" :value="$banner?->subheading" :rows="3" />
        </div>

        <div class="admin-card space-y-5 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Buttons</h3>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-admin.form.input label="Primary Button Label" name="primary_label" :value="$banner?->primary_label" placeholder="Shop Collection" />
                <x-admin.form.input label="Primary Button URL" name="primary_url" :value="$banner?->primary_url" placeholder="/collections" />
                <x-admin.form.input label="Secondary Button Label" name="secondary_label" :value="$banner?->secondary_label" placeholder="New Arrivals" />
                <x-admin.form.input label="Secondary Button URL" name="secondary_url" :value="$banner?->secondary_url" placeholder="/new-arrivals" />
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="admin-card space-y-5 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Banner Images</h3>

            @if ($banner?->image_url)
                <div>
                    <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-400">Desktop</p>
                    <x-ui.optimized-image :src="$banner->image_url" alt="" sizes="320px" class="aspect-video w-full rounded-lg border border-gray-200 object-cover" />
                </div>
            @endif

            <div>
                <label class="admin-label">Desktop Banner</label>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/avif" class="admin-input">
                <p class="mt-1 text-xs text-gray-400">JPG, PNG, WebP or AVIF. Max 5MB. Wide images work best.</p>
            </div>

            @if ($banner?->mobile_image_url)
                <div>
                    <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-400">Mobile</p>
                    <x-ui.optimized-image :src="$banner->mobile_image_url" alt="" sizes="180px" class="mx-auto aspect-[9/16] max-h-80 w-auto rounded-lg border border-gray-200 object-cover" />
                </div>
            @endif

            <div>
                <label class="admin-label">Mobile Banner</label>
                <input type="file" name="mobile_image" accept="image/jpeg,image/png,image/webp,image/avif" class="admin-input">
                <p class="mt-1 text-xs text-gray-400">Optional portrait image for phones. Desktop image is used when empty.</p>
            </div>
        </div>

        <div class="admin-card space-y-5 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Display Settings</h3>
            <x-admin.form.input label="Sort Order" name="sort_order" type="number" :value="$banner?->sort_order ?? 0" />
            <label class="flex items-center gap-2.5 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner?->is_active ?? true)) class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                Active
            </label>
        </div>

        <button type="submit" class="admin-btn-primary w-full">{{ $banner ? 'Update Banner' : 'Create Banner' }}</button>
    </div>
</div>

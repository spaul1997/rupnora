@php($influencer = $influencer ?? null)

@csrf
@if ($influencer)
    @method('PUT')
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <div class="admin-card space-y-5 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Contact Details</h3>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-admin.form.input label="Full Name" name="full_name" :value="$influencer?->full_name" required />
                <x-admin.form.input label="Email Address" name="email" type="email" :value="$influencer?->email" required />
                <x-admin.form.input label="Phone Number" name="phone" :value="$influencer?->phone" required />
                <x-admin.form.input label="Location" name="location" :value="$influencer?->location" required placeholder="City, State" />
            </div>
        </div>

        <div class="admin-card space-y-5 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Creator Profile</h3>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-admin.form.select label="Primary Platform" name="primary_platform" :options="$platforms" :value="$influencer?->primary_platform" required />
                <x-admin.form.input label="Social Handle" name="social_handle" :value="$influencer?->social_handle" required placeholder="@creator" />
                <x-admin.form.input label="Profile URL" name="profile_url" type="url" :value="$influencer?->profile_url" placeholder="https://instagram.com/creator" />
                <x-admin.form.input label="Audience Size" name="followers_count" type="number" min="0" :value="$influencer?->followers_count ?? 0" required />
                <x-admin.form.input label="Content Niche" name="content_niche" :value="$influencer?->content_niche" required placeholder="Jewellery, fashion, lifestyle" />
                <x-admin.form.input label="Portfolio URL" name="portfolio_url" type="url" :value="$influencer?->portfolio_url" placeholder="https://creator.example.com" />
            </div>
            <x-admin.form.textarea label="Application Message" name="message" :value="$influencer?->message" :rows="4" />
        </div>

        <div class="admin-card space-y-5 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Partnership Details</h3>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-admin.form.input label="Commission Rate (%)" name="commission_rate" type="number" min="0" max="100" step="0.01" :value="$influencer?->commission_rate" placeholder="10.00" />
                <x-admin.form.input label="Coupon Code" name="coupon_code" :value="$influencer?->coupon_code" placeholder="CREATOR10" help="Letters, numbers, dashes and underscores only." />
            </div>
            <x-admin.form.textarea label="Internal Notes" name="admin_notes" :value="$influencer?->admin_notes" :rows="5" help="Visible only to administrators." />
        </div>
    </div>

    <div class="space-y-6">
        <div class="admin-card space-y-5 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Profile Image</h3>
            @if ($influencer?->profile_image_url)
                <x-ui.optimized-image :src="$influencer->profile_image_url" :alt="$influencer->full_name" sizes="240px" class="aspect-square w-full rounded-xl border border-gray-200 object-cover" />
            @endif
            <div>
                <label for="profile_image" class="admin-label">{{ $influencer?->profile_image_url ? 'Replace Image' : 'Upload Image' }}</label>
                <input id="profile_image" type="file" name="profile_image" accept="image/jpeg,image/png,image/webp,image/avif" class="admin-input">
                <p class="mt-1 text-xs text-gray-400">JPG, PNG, WebP or AVIF. Maximum 5 MB.</p>
                @error('profile_image') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="admin-card space-y-5 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Status</h3>
            <x-admin.form.select label="Application Status" name="status" :options="$statuses" :value="$influencer?->status ?? 'new'" :placeholder="null" required />
            <label class="flex items-start gap-2.5 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $influencer?->is_active ?? false)) class="mt-0.5 h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                <span>
                    <span class="font-medium">Active partner</span>
                    <span class="mt-0.5 block text-xs text-gray-400">Only approved influencers can be active.</span>
                </span>
            </label>
        </div>

        <button type="submit" class="admin-btn-primary w-full">{{ $influencer ? 'Update Influencer' : 'Create Influencer' }}</button>
    </div>
</div>

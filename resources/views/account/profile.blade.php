<x-layouts.app :title="$title">
    <x-account.shell active="profile">
        <h1 class="font-display text-2xl text-charcoal sm:text-3xl">Profile</h1>

        <div class="mt-6 flex items-center gap-4">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-beige font-display text-2xl text-champagne-dark">
                {{ Str::of($customer['name'])->explode(' ')->map(fn($n) => $n[0])->take(2)->implode('') }}
            </div>
            <div>
                <button class="btn-secondary !px-4 !py-2 text-[11px]">Change Photo</button>
                <p class="mt-1.5 text-xs text-muted">JPG or PNG, max 2MB.</p>
            </div>
        </div>

        <form class="mt-8 max-w-xl space-y-5" onsubmit="event.preventDefault(); Alpine.store('ui').notify('Profile updated successfully', 'success')">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label-luxe">First Name</label>
                    <input type="text" value="{{ $customer['first_name'] }}" class="input-luxe">
                </div>
                <div>
                    <label class="label-luxe">Last Name</label>
                    <input type="text" value="{{ $customer['last_name'] }}" class="input-luxe">
                </div>
            </div>
            <div>
                <label class="label-luxe">Email</label>
                <input type="email" value="{{ $customer['email'] }}" class="input-luxe">
            </div>
            <div>
                <label class="label-luxe">Mobile Number</label>
                <input type="tel" value="{{ $customer['phone'] }}" class="input-luxe">
            </div>
            <div>
                <label class="label-luxe">Date of Birth</label>
                <input type="date" class="input-luxe">
            </div>
            <button type="submit" class="btn-primary">Save Changes</button>
        </form>
    </x-account.shell>
</x-layouts.app>

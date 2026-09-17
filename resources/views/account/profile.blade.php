<x-layouts.app :title="$title">
    <x-account.shell active="profile">
        <h1 class="font-display text-2xl text-charcoal sm:text-3xl">Profile</h1>

        <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data" class="mt-6 max-w-xl space-y-5">
            @csrf
            @method('PATCH')
            <div class="flex items-center gap-4">
                @if ($customer['has_photo'])
                    <img src="{{ route('account.photo') }}" alt="Profile photo" class="h-20 w-20 rounded-full object-cover">
                @else
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-beige font-display text-2xl text-champagne-dark">
                        {{ Str::of($customer['name'])->explode(' ')->filter()->map(fn($n) => mb_substr($n, 0, 1))->take(2)->implode('') }}
                    </div>
                @endif
                <div>
                    <label for="profile-photo" class="label-luxe">Change Photo</label>
                    <input id="profile-photo" type="file" name="photo" accept="image/jpeg,image/png" class="block max-w-full text-xs text-muted">
                    <p class="mt-1.5 text-xs text-muted">JPG or PNG, max 2MB.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label-luxe">First Name</label>
                    <input type="text" name="first_name" required maxlength="60" autocomplete="given-name" value="{{ old('first_name', $customer['first_name']) }}" class="input-luxe">
                </div>
                <div>
                    <label class="label-luxe">Last Name</label>
                    <input type="text" name="last_name" maxlength="60" autocomplete="family-name" value="{{ old('last_name', $customer['last_name']) }}" class="input-luxe">
                </div>
            </div>
            <div>
                <label class="label-luxe">Email</label>
                <input type="email" name="email" required autocomplete="email" value="{{ old('email', $customer['email']) }}" class="input-luxe">
            </div>
            <div>
                <label class="label-luxe">Mobile Number</label>
                <input type="tel" name="phone" maxlength="30" autocomplete="tel" value="{{ old('phone', $customer['phone']) }}" class="input-luxe">
            </div>
            <div>
                <label class="label-luxe">Date of Birth</label>
                <input type="date" name="date_of_birth" max="{{ now()->format('Y-m-d') }}" value="{{ old('date_of_birth', $customer['date_of_birth']) }}" class="input-luxe">
            </div>
            <button type="submit" class="btn-primary">Save Changes</button>
        </form>
    </x-account.shell>
</x-layouts.app>

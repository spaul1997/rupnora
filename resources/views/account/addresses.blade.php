<x-layouts.app :title="$title">
    <x-account.shell active="addresses">
        <div x-data="{ modalOpen: {{ $editingAddress || ($errors->any() && old('address_form')) ? 'true' : 'false' }} }">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="font-display text-2xl text-charcoal sm:text-3xl">Saved Addresses</h1>
                @if ($editingAddress)
                    <a href="{{ route('account.addresses') }}" class="btn-primary !px-5 !py-2.5 text-[12px]">Back to Addresses</a>
                @else
                    <button type="button" @click="modalOpen = true" class="btn-primary !px-5 !py-2.5 text-[12px]">+ Add Address</button>
                @endif
            </div>

            @if (count($addresses) === 0)
                <x-ui.empty-state icon="map" title="No saved addresses" description="Add an address to speed up checkout next time." />
            @else
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @foreach ($addresses as $address)
                        <x-ui.address-card :address="$address" manage />
                    @endforeach
                </div>
            @endif

            {{-- Add address modal --}}
            <div x-cloak x-show="modalOpen" x-transition.opacity class="fixed inset-0 z-[100] flex items-end justify-center bg-charcoal/50 p-0 sm:items-center sm:p-6" @click.self="modalOpen = false">
                <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-t-2xl bg-paper p-6 sm:rounded-2xl sm:p-8">
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="font-display text-xl text-charcoal">{{ $editingAddress ? 'Edit Address' : 'Add New Address' }}</h3>
                        <button @click="modalOpen = false" class="icon-btn"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg></button>
                    </div>
                    <form method="POST" action="{{ $editingAddress ? route('account.addresses.update', $editingAddress['id']) : route('account.addresses.store') }}" class="space-y-4">
                        @csrf
                        @if ($editingAddress)
                            @method('PUT')
                        @endif
                        <input type="hidden" name="address_form" value="1">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2 sm:col-span-1">
                                <label class="label-luxe">Full Name</label>
                                <input type="text" name="name" required maxlength="100" value="{{ old('name', $editingAddress['name'] ?? $customer['name']) }}" class="input-luxe" placeholder="Full name">
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="label-luxe">Mobile</label>
                                <input type="tel" name="phone" required maxlength="30" value="{{ old('phone', $editingAddress['phone'] ?? $customer['phone']) }}" class="input-luxe" placeholder="+91 98765 43210">
                            </div>
                        </div>
                        <div>
                            <label class="label-luxe">Address Line 1</label>
                            <input type="text" name="line1" required maxlength="180" value="{{ old('line1', $editingAddress['line1'] ?? '') }}" class="input-luxe" placeholder="House / Flat / Street">
                        </div>
                        <div>
                            <label class="label-luxe">Address Line 2</label>
                            <input type="text" name="line2" maxlength="180" value="{{ old('line2', $editingAddress['line2'] ?? '') }}" class="input-luxe" placeholder="Area / Locality">
                        </div>
                        <div>
                            <label class="label-luxe">Landmark</label>
                            <input type="text" name="landmark" maxlength="120" value="{{ old('landmark', $editingAddress['landmark'] ?? '') }}" class="input-luxe" placeholder="Nearby landmark (optional)">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label-luxe">City</label>
                                <input type="text" name="city" required maxlength="100" value="{{ old('city', $editingAddress['city'] ?? '') }}" class="input-luxe">
                            </div>
                            <div>
                                <label class="label-luxe">State</label>
                                <input type="text" name="state" required maxlength="100" value="{{ old('state', $editingAddress['state'] ?? '') }}" class="input-luxe">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label-luxe">PIN Code</label>
                                <input type="text" name="pincode" required maxlength="6" pattern="[1-9][0-9]{5}" inputmode="numeric" value="{{ old('pincode', $editingAddress['pincode'] ?? '') }}" class="input-luxe">
                            </div>
                            <div>
                                <label class="label-luxe">Country</label>
                                <input type="text" name="country" required maxlength="80" value="{{ old('country', $editingAddress['country'] ?? 'India') }}" class="input-luxe">
                            </div>
                        </div>
                        <div>
                            <label class="label-luxe">Address Type</label>
                            <div class="flex gap-2" x-data="{ type: {{ Illuminate\Support\Js::from(old('type', $editingAddress['type'] ?? 'Home')) }} }">
                                <input type="hidden" name="type" :value="type">
                                @foreach (['Home', 'Office', 'Other'] as $type)
                                    <button type="button" @click="type = '{{ $type }}'" class="rounded-full border px-4 py-2 text-sm transition-colors" :class="type === '{{ $type }}' ? 'border-charcoal bg-charcoal text-ivory' : 'border-line text-charcoal'">{{ $type }}</button>
                                @endforeach
                            </div>
                        </div>
                        <label class="flex items-center gap-2 text-sm text-charcoal">
                            <input type="checkbox" name="default" value="1" @checked(old('default', $editingAddress['default'] ?? false))>
                            Use as my default address
                        </label>
                        <button type="submit" class="btn-primary w-full">Save Address</button>
                    </form>
                </div>
            </div>
        </div>
    </x-account.shell>
</x-layouts.app>

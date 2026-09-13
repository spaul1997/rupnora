<x-layouts.app :title="$title">
    <x-account.shell active="addresses">
        <div x-data="{ modalOpen: false }">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="font-display text-2xl text-charcoal sm:text-3xl">Saved Addresses</h1>
                <button @click="modalOpen = true" class="btn-primary !px-5 !py-2.5 text-[12px]">+ Add Address</button>
            </div>

            @if (count($addresses) === 0)
                <x-ui.empty-state icon="map" title="No saved addresses" description="Add an address to speed up checkout next time." actionLabel="Add Address" />
            @else
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @foreach ($addresses as $address)
                        <x-ui.address-card :address="$address" />
                    @endforeach
                </div>
            @endif

            {{-- Add address modal --}}
            <div x-cloak x-show="modalOpen" x-transition.opacity class="fixed inset-0 z-[100] flex items-end justify-center bg-charcoal/50 p-0 sm:items-center sm:p-6" @click.self="modalOpen = false">
                <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-t-2xl bg-paper p-6 sm:rounded-2xl sm:p-8">
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="font-display text-xl text-charcoal">Add New Address</h3>
                        <button @click="modalOpen = false" class="icon-btn"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg></button>
                    </div>
                    <form class="space-y-4" @submit.prevent="modalOpen = false; $store.ui.notify('Address saved successfully', 'success')">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2 sm:col-span-1">
                                <label class="label-luxe">Full Name</label>
                                <input type="text" required class="input-luxe" placeholder="Full name">
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="label-luxe">Mobile</label>
                                <input type="tel" required class="input-luxe" placeholder="+91 98765 43210">
                            </div>
                        </div>
                        <div>
                            <label class="label-luxe">Address Line 1</label>
                            <input type="text" required class="input-luxe" placeholder="House / Flat / Street">
                        </div>
                        <div>
                            <label class="label-luxe">Address Line 2</label>
                            <input type="text" class="input-luxe" placeholder="Area / Locality">
                        </div>
                        <div>
                            <label class="label-luxe">Landmark</label>
                            <input type="text" class="input-luxe" placeholder="Nearby landmark (optional)">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label-luxe">City</label>
                                <input type="text" required class="input-luxe">
                            </div>
                            <div>
                                <label class="label-luxe">State</label>
                                <input type="text" required class="input-luxe">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label-luxe">PIN Code</label>
                                <input type="text" required maxlength="6" class="input-luxe">
                            </div>
                            <div>
                                <label class="label-luxe">Country</label>
                                <input type="text" required value="India" class="input-luxe">
                            </div>
                        </div>
                        <div>
                            <label class="label-luxe">Address Type</label>
                            <div class="flex gap-2" x-data="{ type: 'Home' }">
                                @foreach (['Home', 'Office', 'Other'] as $type)
                                    <button type="button" @click="type = '{{ $type }}'" class="rounded-full border px-4 py-2 text-sm transition-colors" :class="type === '{{ $type }}' ? 'border-charcoal bg-charcoal text-ivory' : 'border-line text-charcoal'">{{ $type }}</button>
                                @endforeach
                            </div>
                        </div>
                        <button type="submit" class="btn-primary w-full">Save Address</button>
                    </form>
                </div>
            </div>
        </div>
    </x-account.shell>
</x-layouts.app>

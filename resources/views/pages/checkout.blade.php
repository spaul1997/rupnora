@php
    $mrpTotal = collect($items)->sum(fn ($i) => max($i['product']['mrp'], $i['product']['price']) * $i['qty']);
    $sellingTotal = collect($items)->sum(fn ($i) => $i['product']['price'] * $i['qty']);
    $subtotal = $mrpTotal;
    $discount = max(0, $mrpTotal - $sellingTotal);
    $tax = round($sellingTotal * 0.03);
    $steps = ['Address', 'Delivery', 'Payment', 'Confirmation'];
    $checkoutAddresses = collect($addresses)->map(fn ($address) => [
        'id' => $address['id'],
        'type' => $address['type'],
        'default' => (bool) $address['default'],
        'name' => $address['name'],
        'phone' => $address['phone'],
        'line1' => $address['line1'],
        'line2' => $address['line2'],
        'landmark' => $address['landmark'] ?? '',
        'city' => $address['city'],
        'state' => $address['state'],
        'pincode' => $address['pincode'],
        'country' => $address['country'],
    ])->values()->all();
@endphp

<x-layouts.app title="Checkout">
    <div
        x-data="checkoutPage({
            addresses: {{ Illuminate\Support\Js::from($checkoutAddresses) }},
            selectedAddressId: {{ Illuminate\Support\Js::from($checkoutAddresses[0]['id'] ?? null) }},
            addAddressUrl: {{ Illuminate\Support\Js::from(route('checkout.addresses.store')) }},
            placeOrderUrl: {{ Illuminate\Support\Js::from(route('checkout.order.store')) }},
        })"
        class="container-luxe py-8"
    >
        {{-- Stepper --}}
        <div class="mx-auto mb-10 flex max-w-2xl items-center justify-between">
            @foreach ($steps as $i => $label)
                <div class="flex flex-1 items-center">
                    <div class="flex flex-col items-center gap-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full border-2 text-sm font-semibold transition-colors" :class="step > {{ $i + 1 }} ? 'border-champagne-dark bg-champagne-dark text-ivory' : (step === {{ $i + 1 }} ? 'border-charcoal text-charcoal' : 'border-line text-muted-light')">
                            <template x-if="step > {{ $i + 1 }}">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </template>
                            <span x-show="step <= {{ $i + 1 }}">{{ $i + 1 }}</span>
                        </div>
                        <span class="hidden text-xs font-medium sm:block" :class="step >= {{ $i + 1 }} ? 'text-charcoal' : 'text-muted-light'">{{ $label }}</span>
                    </div>
                    @if (! $loop->last)
                        <div class="mx-2 h-px flex-1" :class="step > {{ $i + 1 }} ? 'bg-champagne-dark' : 'bg-line'"></div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-10 lg:grid-cols-3 lg:gap-14">
            <div class="lg:col-span-2">

                {{-- Step 1: Address --}}
                <div x-show="step === 1" x-cloak>
                    <h2 class="font-display text-xl text-charcoal">Select Delivery Address</h2>
                    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <template x-for="address in addresses" :key="address.id">
                            <button
                                type="button"
                                @click="selectAddress(address.id)"
                                class="card-luxe relative p-5 text-left transition-colors"
                                :class="selectedAddress === address.id ? 'border-champagne-dark ring-1 ring-champagne-dark' : ''"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full border-2" :class="selectedAddress === address.id ? 'border-champagne-dark' : 'border-line'">
                                            <span x-show="selectedAddress === address.id" class="h-2 w-2 rounded-full bg-champagne-dark"></span>
                                        </span>
                                        <span class="badge-luxe bg-beige text-charcoal-soft" x-text="address.type"></span>
                                        <span x-show="address.default" x-cloak class="badge-luxe bg-champagne text-charcoal">Default</span>
                                    </div>
                                </div>
                                <p class="mt-3 text-sm font-semibold text-charcoal" x-text="address.name"></p>
                                <p class="mt-1 text-sm leading-relaxed text-muted" x-text="fullAddress(address)"></p>
                                <p class="mt-2 text-sm text-charcoal" x-text="address.phone"></p>
                            </button>
                        </template>
                    </div>
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <button type="button" @click="addingAddress = !addingAddress; addressError = ''" class="text-left text-sm font-medium text-champagne-dark hover:underline" x-text="addingAddress ? 'Cancel New Address' : '+ Add New Address'">+ Add New Address</button>
                        <button @click="continueToDelivery()" :disabled="!selectedAddressRecord" class="btn-primary w-full sm:ml-auto sm:w-auto">Continue to Delivery</button>
                    </div>
                    <form @submit.prevent="saveAddress()" x-show="addingAddress" x-collapse x-cloak class="mt-4 grid grid-cols-1 gap-4 rounded-xl border border-line p-5 sm:grid-cols-2">
                        <select x-model="newAddress.type" class="input-luxe">
                            <option>Home</option>
                            <option>Office</option>
                            <option>Other</option>
                        </select>
                        <input type="text" x-model.trim="newAddress.name" placeholder="Full Name" class="input-luxe">
                        <input type="tel" x-model.trim="newAddress.phone" placeholder="Mobile Number" class="input-luxe">
                        <input type="text" x-model.trim="newAddress.line1" placeholder="Address Line 1" class="input-luxe">
                        <input type="text" x-model.trim="newAddress.line2" placeholder="Address Line 2" class="input-luxe">
                        <input type="text" x-model.trim="newAddress.landmark" placeholder="Landmark" class="input-luxe">
                        <input type="text" x-model.trim="newAddress.city" placeholder="City" class="input-luxe">
                        <input type="text" x-model.trim="newAddress.state" placeholder="State" class="input-luxe">
                        <input type="text" x-model.trim="newAddress.pincode" maxlength="6" placeholder="PIN Code" class="input-luxe">
                        <input type="text" x-model.trim="newAddress.country" placeholder="Country" class="input-luxe">
                        <div class="flex flex-col gap-2 sm:col-span-2 sm:flex-row sm:items-center">
                            <button type="submit" class="btn-primary w-full sm:w-auto" :disabled="!canSaveAddress || savingAddress">
                                <span x-show="!savingAddress">Save Address</span>
                                <span x-show="savingAddress" x-cloak>Saving...</span>
                            </button>
                            <button type="button" @click="addingAddress = false; resetNewAddress()" class="btn-secondary w-full sm:w-auto">Cancel</button>
                        </div>
                    </form>
                    <p x-show="addressError" x-cloak class="mt-3 text-sm text-error" x-text="addressError"></p>
                </div>

                {{-- Step 2: Delivery --}}
                <div x-show="step === 2" x-cloak>
                    <h2 class="font-display text-xl text-charcoal">Choose Delivery Option</h2>
                    <div class="mt-5 space-y-3">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition-colors" :class="delivery === 'standard' ? 'border-champagne-dark ring-1 ring-champagne-dark' : 'border-line'">
                            <input type="radio" x-model="delivery" value="standard" class="mt-1 h-4 w-4 text-champagne-dark focus:ring-champagne-dark/40">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-charcoal">Standard Delivery</span>
                                    <span class="text-sm font-semibold text-success">Free</span>
                                </div>
                                <p class="mt-1 text-xs text-muted">Arrives in 5&ndash;7 business days, fully insured.</p>
                            </div>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition-colors" :class="delivery === 'express' ? 'border-champagne-dark ring-1 ring-champagne-dark' : 'border-line'">
                            <input type="radio" x-model="delivery" value="express" class="mt-1 h-4 w-4 text-champagne-dark focus:ring-champagne-dark/40">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-charcoal">Express Delivery</span>
                                    <span class="text-sm font-semibold text-charcoal">₹199</span>
                                </div>
                                <p class="mt-1 text-xs text-muted">Arrives in 2&ndash;3 business days, fully insured.</p>
                            </div>
                        </label>
                    </div>
                    <div class="mt-6 flex gap-3">
                        <button @click="step = 1" class="btn-secondary">Back</button>
                        <button @click="step = 3" class="btn-primary flex-1 sm:flex-initial">Continue to Payment</button>
                    </div>
                </div>

                {{-- Step 3: Payment --}}
                <div x-show="step === 3" x-cloak>
                    <h2 class="font-display text-xl text-charcoal">Payment Method</h2>
                    <div class="mt-5 space-y-3">
                        @foreach ([
                            ['key' => 'upi', 'label' => 'UPI', 'desc' => 'Pay via Google Pay, PhonePe, Paytm & more'],
                            ['key' => 'card', 'label' => 'Credit / Debit Card', 'desc' => 'Visa, Mastercard, RuPay accepted'],
                            ['key' => 'netbanking', 'label' => 'Net Banking', 'desc' => 'All major Indian banks supported'],
                            ['key' => 'wallet', 'label' => 'Wallet', 'desc' => 'Paytm, Amazon Pay, Mobikwik'],
                            ['key' => 'cod', 'label' => 'Cash on Delivery', 'desc' => 'Available on orders below ₹1,00,000'],
                        ] as $method)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition-colors" :class="payment === '{{ $method['key'] }}' ? 'border-champagne-dark ring-1 ring-champagne-dark' : 'border-line'">
                                <input type="radio" x-model="payment" value="{{ $method['key'] }}" class="mt-1 h-4 w-4 text-champagne-dark focus:ring-champagne-dark/40">
                                <div>
                                    <span class="text-sm font-medium text-charcoal">{{ $method['label'] }}</span>
                                    <p class="mt-1 text-xs text-muted">{{ $method['desc'] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <div x-show="payment === 'card'" x-cloak x-collapse class="mt-4 grid grid-cols-1 gap-4 rounded-xl border border-line p-5 sm:grid-cols-2">
                        <input type="text" placeholder="Card Number" class="input-luxe sm:col-span-2">
                        <input type="text" placeholder="MM / YY" class="input-luxe">
                        <input type="text" placeholder="CVV" class="input-luxe">
                    </div>
                    <div class="mt-6 flex gap-3">
                        <button @click="step = 2" class="btn-secondary">Back</button>
                        <button @click="step = 4" class="btn-primary flex-1 sm:flex-initial">Review Order</button>
                    </div>
                </div>

                {{-- Step 4: Confirmation --}}
                <div x-show="step === 4" x-cloak>
                    <h2 class="font-display text-xl text-charcoal">Review &amp; Confirm</h2>
                    <div class="mt-5 space-y-4">
                        <div class="rounded-xl border border-line p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-muted">Delivery Address</p>
                            <p class="mt-1.5 text-sm text-charcoal" x-text="compactAddress(selectedAddressRecord)"></p>
                            <p class="mt-1 text-xs text-muted" x-text="selectedAddressRecord?.phone || ''"></p>
                        </div>
                        <div class="rounded-xl border border-line p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-muted">Delivery Option</p>
                            <p class="mt-1.5 text-sm text-charcoal" x-text="delivery === 'standard' ? 'Standard Delivery (Free, 5–7 days)' : 'Express Delivery (₹199, 2–3 days)'"></p>
                        </div>
                        <div class="rounded-xl border border-line p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-muted">Payment Method</p>
                            <p class="mt-1.5 text-sm capitalize text-charcoal" x-text="payment === 'cod' ? 'Cash on Delivery' : payment.toUpperCase()"></p>
                        </div>
                        <div class="rounded-xl border border-line p-4">
                            <p class="mb-3 text-xs font-medium uppercase tracking-wide text-muted">Items</p>
                            <div class="divide-y divide-line">
                                @foreach ($items as $item)
                                    @php
                                        $product = $item['product'];
                                    @endphp
                                    <div class="flex items-center gap-3 py-2.5">
                                        @if (! empty($product['image']))
                                            <x-ui.optimized-image :src="$product['image']" :alt="$product['name']" sizes="56px" class="h-14 w-14 flex-shrink-0 rounded-lg bg-ivory-soft object-cover" />
                                        @else
                                            <x-ui.product-art :art="$product['art']" class="h-14 w-14 flex-shrink-0 rounded-lg" />
                                        @endif
                                        <span class="min-w-0 flex-1 truncate text-sm text-charcoal">{{ $product['name'] }} &times; {{ $item['qty'] }}</span>
                                        <span class="flex-shrink-0 text-sm font-medium text-charcoal">₹{{ number_format($product['price'] * $item['qty']) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-3">
                        <button @click="step = 3" class="btn-secondary">Back</button>
                        <button type="button" @click="placeOrder()" :disabled="placingOrder || !selectedAddressRecord" class="btn-primary flex-1 text-center sm:flex-initial">
                            <span x-show="!placingOrder">Place Order</span>
                            <span x-show="placingOrder" x-cloak>Placing...</span>
                        </button>
                    </div>
                    <p x-show="orderError" x-cloak class="mt-3 text-sm text-error" x-text="orderError"></p>
                </div>
            </div>

            <div>
                <div class="lg:sticky lg:top-28">
                    <x-ui.order-summary :subtotal="$subtotal" :discount="$discount" :shipping="0" :tax="$tax" :showCoupon="false" ctaLabel="" />
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

<footer
    x-data="{ sizeGuideOpen: false, sizeGuideTab: 'ring' }"
    @keydown.escape.window="sizeGuideOpen = false"
    class="border-t border-line bg-charcoal text-ivory"
>
    <div class="container-luxe py-16">
        <div class="grid grid-cols-2 gap-10 sm:grid-cols-3 lg:grid-cols-6">
            <div class="col-span-2 sm:col-span-3 lg:col-span-2">
                <img src="{{ asset('logo-w.png') }}" alt="Rupnora" class="h-14 w-auto object-contain">
                <p class="mt-3 max-w-xs text-sm leading-relaxed text-ivory/60">
                    Certified fine jewellery crafted for life's most meaningful moments &mdash; gold, diamond &amp; silver pieces made to be passed down.
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-champagne-light">Shop</p>
                <ul class="mt-4 space-y-2.5 text-sm text-ivory/65">
                    @foreach ($footerCategories as $category)
                        <li><a href="{{ route('category.show', $category['slug']) }}" class="hover:text-ivory">{{ $category['name'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-champagne-light">Customer Service</p>
                <ul class="mt-4 space-y-2.5 text-sm text-ivory/65">
                    <li><a href="{{ route('contact') }}" class="hover:text-ivory">Contact Us</a></li>
                    <li><a href="{{ route('careers') }}" class="hover:text-ivory">Careers</a></li>
                    <li>
                        <button type="button" @click="sizeGuideOpen = true" class="hover:text-ivory">Size Guide</button>
                    </li>
                    <li><a href="{{ route('contact') }}#faq" class="hover:text-ivory">FAQ</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-champagne-light">About</p>
                <ul class="mt-4 space-y-2.5 text-sm text-ivory/65">
                    <li><a href="{{ route('about') }}" class="hover:text-ivory">Our Story</a></li>
                    <li><a href="{{ route('privacy-policy') }}" class="hover:text-ivory">Privacy Policy</a></li>
                    <li><a href="{{ route('terms-of-service') }}" class="hover:text-ivory">Terms of Service</a></li>
                    <li><a href="{{ route('refund-policy') }}" class="hover:text-ivory">Refund Policy</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-champagne-light">Account</p>
                <ul class="mt-4 space-y-2.5 text-sm text-ivory/65">
                    <li><a href="{{ route('account.dashboard') }}" class="hover:text-ivory">My Account</a></li>
                    <li><a href="{{ route('account.orders') }}" class="hover:text-ivory">Orders</a></li>
                    <li><a href="{{ route('account.wishlist') }}" class="hover:text-ivory">Wishlist</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-ivory">Sign In</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-14 rounded-2xl border border-ivory/15 bg-ivory/5 p-6 sm:p-8">
            <div class="flex w-full flex-col gap-5 text-center sm:flex-row sm:flex-nowrap sm:items-center sm:justify-between sm:text-left">
                <div class="min-w-0 flex-1">
                    <h3 class="font-display text-xl text-ivory">Join Our World of Jewellery</h3>
                    <p class="mt-1 text-sm text-ivory/60">Be first to know about new collections &amp; exclusive offers.</p>
                </div>
                <form
                    x-data="newsletterForm({{ Illuminate\Support\Js::from(route('newsletter-subscriptions.store')) }})"
                    action="{{ route('newsletter-subscriptions.store') }}"
                    method="POST"
                    @submit.prevent="subscribe"
                    class="flex w-full max-w-md flex-shrink-0 gap-2 sm:w-auto"
                >
                    @csrf
                    <input
                        type="email"
                        name="email"
                        x-model.trim="email"
                        :disabled="submitting || subscribed"
                        :aria-invalid="error ? 'true' : 'false'"
                        required
                        autocomplete="email"
                        placeholder="Your email address"
                        class="min-w-0 flex-1 rounded-full border border-ivory/25 bg-transparent px-4 py-2.5 text-sm text-ivory placeholder:text-ivory/40 focus:border-champagne-light focus:outline-none disabled:opacity-60 sm:w-64"
                    >
                    <button type="submit" :disabled="submitting || subscribed" class="btn-light flex-shrink-0 !px-6 !py-2.5 disabled:cursor-not-allowed disabled:opacity-70">
                        <span x-show="!submitting && !subscribed">Subscribe</span>
                        <span x-cloak x-show="submitting">Subscribing...</span>
                        <span x-cloak x-show="subscribed">Subscribed</span>
                    </button>
                    <span class="sr-only" aria-live="polite" x-text="error || statusMessage"></span>
                </form>
                <div class="flex flex-shrink-0 items-center justify-center gap-2 sm:justify-end" aria-label="Social media links">
                    @foreach (['instagram' => 'M12 2c2.7 0 3 0 4.1.06 1.1.05 1.8.22 2.5.47.7.27 1.2.6 1.8 1.16.6.6.9 1.1 1.16 1.8.25.7.42 1.4.47 2.5.06 1.1.06 1.4.06 4.1s0 3-.06 4.1c-.05 1.1-.22 1.8-.47 2.5a5 5 0 01-1.16 1.8 5 5 0 01-1.8 1.16c-.7.25-1.4.42-2.5.47-1.1.06-1.4.06-4.1.06s-3 0-4.1-.06c-1.1-.05-1.8-.22-2.5-.47a5 5 0 01-1.8-1.16 5 5 0 01-1.16-1.8c-.25-.7-.42-1.4-.47-2.5C2 15 2 14.7 2 12s0-3 .06-4.1c.05-1.1.22-1.8.47-2.5.27-.7.6-1.2 1.16-1.8.6-.6 1.1-.9 1.8-1.16.7-.25 1.4-.42 2.5-.47C9 2 9.3 2 12 2zm0 5a5 5 0 100 10 5 5 0 000-10zm0 8.2a3.2 3.2 0 110-6.4 3.2 3.2 0 010 6.4zm5.3-8.4a1.2 1.2 0 100-2.4 1.2 1.2 0 000 2.4z', 'facebook' => 'M14 9h3V6h-3c-1.7 0-3 1.3-3 3v2H9v3h2v7h3v-7h3l1-3h-4V9c0-.6.4-1 1-1z', 'pinterest' => 'M12 2a10 10 0 00-3.6 19.3c0-.8 0-1.8.2-2.6l1.4-6s-.4-.7-.4-1.8c0-1.7 1-3 2.2-3 1 0 1.5.8 1.5 1.7 0 1-.7 2.6-1 4-.3 1.2.6 2.2 1.8 2.2 2.1 0 3.7-2.3 3.7-5.5 0-2.9-2-5-4.9-5-3.4 0-5.3 2.5-5.3 5.1 0 1 .4 2.1.9 2.7a.4.4 0 01.1.4l-.3 1.4c-.1.2-.2.3-.4.2-1.6-.7-2.6-3-2.6-4.8 0-3.9 2.9-7.6 8.3-7.6 4.3 0 7.7 3.1 7.7 7.2 0 4.3-2.7 7.7-6.4 7.7-1.3 0-2.5-.6-2.9-1.5l-.8 3c-.3 1-1 2.4-1.5 3.1A10 10 0 1012 2z'] as $name => $path)
                        <a href="#" aria-label="{{ ucfirst($name) }}" class="flex h-10 w-10 items-center justify-center rounded-full border border-ivory/20 text-ivory/70 transition-colors hover:border-champagne-light hover:text-champagne-light">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $path }}" /></svg>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-ivory/10 pt-8 text-xs text-ivory/50 sm:flex-row">
            <p>&copy; {{ date('Y') }} Rupnora Jewellery. All rights reserved.</p>
            <div class="flex items-center gap-3">
                <span class="rounded border border-ivory/20 px-2 py-1">VISA</span>
                <span class="rounded border border-ivory/20 px-2 py-1">Mastercard</span>
                <span class="rounded border border-ivory/20 px-2 py-1">UPI</span>
                <span class="rounded border border-ivory/20 px-2 py-1">RuPay</span>
            </div>
            <div class="flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5 text-champagne-light" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6l8-4z" /></svg>
                <span>100% Secure Payments</span>
            </div>
        </div>
    </div>

    {{-- Compact size guide --}}
    <div
        x-cloak
        x-show="sizeGuideOpen"
        x-transition.opacity
        class="fixed inset-0 z-[120] flex items-center justify-center bg-charcoal/70 p-4"
        @click.self="sizeGuideOpen = false"
        role="dialog"
        aria-modal="true"
        aria-labelledby="footer-size-guide-title"
    >
        <div x-show="sizeGuideOpen" x-transition.scale.origin.bottom class="w-full max-w-lg overflow-hidden rounded-2xl bg-paper text-charcoal shadow-card">
            <div class="flex items-center justify-between border-b border-line px-5 py-4">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-champagne-dark">Find your fit</p>
                    <h2 id="footer-size-guide-title" class="font-display mt-0.5 text-xl">Quick Size Guide</h2>
                </div>
                <button type="button" @click="sizeGuideOpen = false" class="icon-btn" aria-label="Close size guide">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <div class="p-5">
                <div class="grid grid-cols-3 rounded-full bg-ivory-soft p-1" role="tablist" aria-label="Jewellery type">
                    @foreach (['ring' => 'Rings', 'bracelet' => 'Bracelets', 'necklace' => 'Necklaces'] as $tab => $label)
                        <button
                            type="button"
                            @click="sizeGuideTab = '{{ $tab }}'"
                            :class="sizeGuideTab === '{{ $tab }}' ? 'bg-charcoal text-ivory shadow-sm' : 'text-muted hover:text-charcoal'"
                            class="rounded-full px-3 py-2 text-xs font-semibold transition-colors"
                            role="tab"
                            :aria-selected="sizeGuideTab === '{{ $tab }}'"
                        >{{ $label }}</button>
                    @endforeach
                </div>

                <div x-show="sizeGuideTab === 'ring'" class="mt-5">
                    <p class="mb-3 text-xs leading-5 text-muted">Measure the inner diameter of a ring that fits, then choose the closest match.</p>
                    <div class="overflow-hidden rounded-xl border border-line">
                        <table class="w-full text-xs">
                            <thead class="bg-ivory-soft text-left uppercase tracking-wide text-muted">
                                <tr><th class="px-3 py-2">Size</th><th class="px-3 py-2">Diameter</th><th class="px-3 py-2">Around finger</th></tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ([['10', '16.5', '51.9'], ['12', '17.3', '54.4'], ['14', '18.2', '57.2'], ['16', '19.0', '59.7'], ['18', '19.8', '62.1']] as $ring)
                                    <tr><td class="px-3 py-2 font-semibold">{{ $ring[0] }}</td><td class="px-3 py-2">{{ $ring[1] }} mm</td><td class="px-3 py-2">{{ $ring[2] }} mm</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-cloak x-show="sizeGuideTab === 'bracelet'" class="mt-5">
                    <p class="text-xs leading-5 text-muted">Measure around your wrist just below the wrist bone, then add the allowance for your preferred fit.</p>
                    <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                        @foreach ([['Close', '+1 cm'], ['Comfort', '+1.5 cm'], ['Relaxed', '+2 cm']] as $fit)
                            <div class="rounded-xl border border-line p-3">
                                <p class="text-[10px] uppercase tracking-wide text-muted">{{ $fit[0] }}</p>
                                <p class="mt-1 font-display text-lg">{{ $fit[1] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div x-cloak x-show="sizeGuideTab === 'necklace'" class="mt-5">
                    <p class="text-xs leading-5 text-muted">Use a string to preview where each length will sit. Pendant size and body shape can change the final position.</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach (['35–40 cm · Choker', '45 cm · Collarbone', '50–55 cm · Mid chest', '60+ cm · Long'] as $length)
                            <span class="rounded-full border border-line bg-ivory-soft px-3 py-2 text-xs">{{ $length }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between border-t border-line pt-4">
                    <p class="text-[11px] text-muted">Between sizes? Choose the larger one.</p>
                    <a href="{{ route('size-guide') }}" class="text-xs font-semibold text-champagne-dark hover:underline">Full guide &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</footer>

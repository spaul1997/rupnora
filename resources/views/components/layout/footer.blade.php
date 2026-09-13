<footer class="border-t border-line bg-charcoal text-ivory">
    <div class="container-luxe py-16">
        <div class="grid grid-cols-2 gap-10 sm:grid-cols-3 lg:grid-cols-6">
            <div class="col-span-2 sm:col-span-3 lg:col-span-2">
                <img src="{{ asset('logo-w.png') }}" alt="Rupnora" class="h-14 w-auto object-contain">
                <p class="mt-3 max-w-xs text-sm leading-relaxed text-ivory/60">
                    Certified fine jewellery crafted for life's most meaningful moments &mdash; gold, diamond &amp; silver pieces made to be passed down.
                </p>
                <div class="mt-5 flex items-center gap-3">
                    @foreach (['instagram' => 'M12 2c2.7 0 3 0 4.1.06 1.1.05 1.8.22 2.5.47.7.27 1.2.6 1.8 1.16.6.6.9 1.1 1.16 1.8.25.7.42 1.4.47 2.5.06 1.1.06 1.4.06 4.1s0 3-.06 4.1c-.05 1.1-.22 1.8-.47 2.5a5 5 0 01-1.16 1.8 5 5 0 01-1.8 1.16c-.7.25-1.4.42-2.5.47-1.1.06-1.4.06-4.1.06s-3 0-4.1-.06c-1.1-.05-1.8-.22-2.5-.47a5 5 0 01-1.8-1.16 5 5 0 01-1.16-1.8c-.25-.7-.42-1.4-.47-2.5C2 15 2 14.7 2 12s0-3 .06-4.1c.05-1.1.22-1.8.47-2.5.27-.7.6-1.2 1.16-1.8.6-.6 1.1-.9 1.8-1.16.7-.25 1.4-.42 2.5-.47C9 2 9.3 2 12 2zm0 5a5 5 0 100 10 5 5 0 000-10zm0 8.2a3.2 3.2 0 110-6.4 3.2 3.2 0 010 6.4zm5.3-8.4a1.2 1.2 0 100-2.4 1.2 1.2 0 000 2.4z', 'facebook' => 'M14 9h3V6h-3c-1.7 0-3 1.3-3 3v2H9v3h2v7h3v-7h3l1-3h-4V9c0-.6.4-1 1-1z', 'pinterest' => 'M12 2a10 10 0 00-3.6 19.3c0-.8 0-1.8.2-2.6l1.4-6s-.4-.7-.4-1.8c0-1.7 1-3 2.2-3 1 0 1.5.8 1.5 1.7 0 1-.7 2.6-1 4-.3 1.2.6 2.2 1.8 2.2 2.1 0 3.7-2.3 3.7-5.5 0-2.9-2-5-4.9-5-3.4 0-5.3 2.5-5.3 5.1 0 1 .4 2.1.9 2.7a.4.4 0 01.1.4l-.3 1.4c-.1.2-.2.3-.4.2-1.6-.7-2.6-3-2.6-4.8 0-3.9 2.9-7.6 8.3-7.6 4.3 0 7.7 3.1 7.7 7.2 0 4.3-2.7 7.7-6.4 7.7-1.3 0-2.5-.6-2.9-1.5l-.8 3c-.3 1-1 2.4-1.5 3.1A10 10 0 1012 2z'] as $name => $path)
                        <a href="#" aria-label="{{ ucfirst($name) }}" class="flex h-9 w-9 items-center justify-center rounded-full border border-ivory/20 text-ivory/70 transition-colors hover:border-champagne-light hover:text-champagne-light">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="{{ $path }}" /></svg>
                        </a>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-champagne-light">Shop</p>
                <ul class="mt-4 space-y-2.5 text-sm text-ivory/65">
                    <li><a href="{{ route('category.show', 'rings') }}" class="hover:text-ivory">Rings</a></li>
                    <li><a href="{{ route('category.show', 'earrings') }}" class="hover:text-ivory">Earrings</a></li>
                    <li><a href="{{ route('category.show', 'necklaces') }}" class="hover:text-ivory">Necklaces</a></li>
                    <li><a href="{{ route('category.show', 'bangles') }}" class="hover:text-ivory">Bangles</a></li>
                    <li><a href="{{ route('category.show', 'new-arrivals') }}" class="hover:text-ivory">New Arrivals</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-champagne-light">Customer Service</p>
                <ul class="mt-4 space-y-2.5 text-sm text-ivory/65">
                    <li><a href="{{ route('contact') }}" class="hover:text-ivory">Contact Us</a></li>
                    <li><a href="{{ route('contact') }}#shipping" class="hover:text-ivory">Shipping</a></li>
                    <li><a href="{{ route('contact') }}#returns" class="hover:text-ivory">Returns</a></li>
                    <li><a href="#" class="hover:text-ivory">Size Guide</a></li>
                    <li><a href="{{ route('contact') }}#faq" class="hover:text-ivory">FAQ</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-champagne-light">About</p>
                <ul class="mt-4 space-y-2.5 text-sm text-ivory/65">
                    <li><a href="{{ route('about') }}" class="hover:text-ivory">Our Story</a></li>
                    <li><a href="#" class="hover:text-ivory">Careers</a></li>
                    <li><a href="#" class="hover:text-ivory">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-ivory">Terms of Service</a></li>
                    <li><a href="#" class="hover:text-ivory">Refund Policy</a></li>
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
            <div class="flex flex-col items-center gap-4 text-center sm:flex-row sm:justify-between sm:text-left">
                <div>
                    <h3 class="font-display text-xl text-ivory">Join Our World of Jewellery</h3>
                    <p class="mt-1 text-sm text-ivory/60">Be first to know about new collections &amp; exclusive offers.</p>
                </div>
                <form class="flex w-full max-w-sm gap-2 sm:w-auto" onsubmit="event.preventDefault()">
                    <input type="email" required placeholder="Your email address" class="w-full rounded-full border border-ivory/25 bg-transparent px-4 py-2.5 text-sm text-ivory placeholder:text-ivory/40 focus:border-champagne-light focus:outline-none">
                    <button type="submit" class="btn-light !px-6 !py-2.5 flex-shrink-0">Subscribe</button>
                </form>
            </div>
        </div>

        <div class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-ivory/10 pt-8 text-xs text-ivory/50 sm:flex-row">
            <p>&copy; {{ date('Y') }} Aurelle Jewellery. All rights reserved.</p>
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
</footer>

<x-layouts.app title="Sign In">
    <div class="grid min-h-[calc(100vh-1px)] grid-cols-1 lg:grid-cols-2">
        {{-- Promo panel --}}
        <div class="relative hidden overflow-hidden bg-gradient-to-br from-beige via-ivory to-champagne-light/40 lg:block">
            <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 22px 22px; color: var(--color-charcoal);"></div>
            <div class="absolute inset-0 opacity-60">
                <x-ui.product-art art="bridal" class="aspect-auto h-full" />
            </div>
            <div class="relative flex h-full flex-col justify-end p-12 xl:p-16">
                <span class="eyebrow">Aurelle Rewards</span>
                <h2 class="font-display mt-3 max-w-sm text-4xl leading-tight text-charcoal">Welcome Back to Timeless Elegance</h2>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-muted">Sign in to track orders, manage your wishlist, and enjoy early access to new collections.</p>
            </div>
        </div>

        {{-- Form --}}
        <div class="flex items-center justify-center px-5 py-16 sm:px-10" x-data="{ mode: 'password' }">
            <div class="w-full max-w-sm">
                <a href="{{ route('home') }}" class="font-display text-2xl text-charcoal">Aurelle</a>
                <h1 class="font-display mt-6 text-3xl text-charcoal">Sign In</h1>
                <p class="mt-2 text-sm text-muted">Enter your details to access your account.</p>

                @if (session('success'))
                    <div class="mt-5 rounded-xl bg-success/10 px-4 py-3 text-sm text-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="mt-5 rounded-xl bg-error/10 px-4 py-3 text-sm text-error">{{ session('error') }}</div>
                @endif

                <div class="mt-6 flex rounded-full border border-line p-1">
                    <button @click="mode = 'password'" class="flex-1 rounded-full py-2 text-xs font-semibold uppercase tracking-wide transition-colors" :class="mode === 'password' ? 'bg-charcoal text-ivory' : 'text-muted'">Password</button>
                    <button @click="mode = 'otp'" class="flex-1 rounded-full py-2 text-xs font-semibold uppercase tracking-wide transition-colors" :class="mode === 'otp' ? 'bg-charcoal text-ivory' : 'text-muted'">Login with OTP</button>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
                    @csrf
                    <div>
                        <label class="label-luxe">Email / Mobile Number</label>
                        <input type="text" name="login" value="{{ old('login') }}" required class="input-luxe" placeholder="you@example.com or 98765 43210">
                        @error('login') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="mode === 'password'" x-cloak x-data="{ show: false }">
                        <label class="label-luxe">Password</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password" :required="mode === 'password'" class="input-luxe pr-11" placeholder="Enter your password">
                            <button type="button" @click="show = !show" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-muted hover:text-charcoal">
                                <svg x-show="!show" class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" /><circle cx="12" cy="12" r="3" /></svg>
                                <svg x-show="show" x-cloak class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 3l18 18M10.6 10.6a3 3 0 004.2 4.2M9.9 4.24A11 11 0 0112 4c7 0 11 7 11 7a13.2 13.2 0 01-3.1 3.9M6.1 6.1A13.3 13.3 0 001 11s4 7 11 7a10.9 10.9 0 004.9-1.1" stroke-linecap="round" /></svg>
                            </button>
                        </div>
                    </div>

                    <div x-show="mode === 'otp'" x-cloak class="rounded-xl bg-ivory-soft p-4 text-sm text-muted">
                        We'll send a one-time password to your registered mobile number or email.
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-charcoal-soft">
                            <input type="checkbox" class="h-4 w-4 rounded border-line text-champagne-dark focus:ring-champagne-dark/40">
                            Remember Me
                        </label>
                        <a href="{{ route('password.request') }}" class="font-medium text-champagne-dark hover:underline">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn-primary w-full" x-text="mode === 'password' ? 'Login' : 'Send OTP'"></button>
                </form>

                <div class="my-6 flex items-center gap-3">
                    <div class="h-px flex-1 bg-line"></div>
                    <span class="text-xs uppercase tracking-wide text-muted-light">Or</span>
                    <div class="h-px flex-1 bg-line"></div>
                </div>

                <button type="button" class="flex w-full items-center justify-center gap-2.5 rounded-full border border-line py-3.5 text-sm font-medium text-charcoal transition-colors hover:border-charcoal/40">
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24"><path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5a5.6 5.6 0 01-2.4 3.6v3h3.9c2.3-2.1 3.5-5.2 3.5-8.8z"/><path fill="#34A853" d="M12 24c3.2 0 6-1 8-2.9l-3.9-3c-1 .7-2.5 1.2-4.1 1.2-3.2 0-5.9-2.1-6.8-5H1.2v3.1A12 12 0 0012 24z"/><path fill="#FBBC05" d="M5.2 14.3a7.2 7.2 0 010-4.6V6.6H1.2a12 12 0 000 10.8z"/><path fill="#EA4335" d="M12 4.7c1.7 0 3.3.6 4.5 1.8l3.4-3.4A11.6 11.6 0 0012 0 12 12 0 001.2 6.6l4 3.1c.9-2.9 3.6-5 6.8-5z"/></svg>
                    Continue with Google
                </button>

                <p class="mt-8 text-center text-sm text-muted">
                    Don't have an account? <a href="{{ route('register') }}" class="font-semibold text-champagne-dark hover:underline">Create Account</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>

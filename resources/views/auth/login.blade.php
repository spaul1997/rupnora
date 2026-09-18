<x-layouts.app title="Sign In">
    <section class="container-luxe py-6 sm:py-8 lg:py-10">
        <div class="grid overflow-hidden rounded-3xl border border-line bg-paper shadow-card lg:grid-cols-[1.05fr_0.95fr]">
        <div class="relative hidden min-h-[480px] overflow-hidden bg-gradient-to-br from-beige via-ivory to-champagne-light/40 lg:block">
            <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 22px 22px; color: var(--color-charcoal);"></div>
            <div class="absolute inset-0">
                <x-ui.optimized-image :src="asset('images/login.png')" alt="Rupnora" sizes="50vw" class="h-full w-full object-cover" />
            </div>
            <div class="relative flex h-full flex-col justify-end p-8 xl:p-10">
                <span class="eyebrow">Rupnora Rewards</span>
                <h2 class="font-display mt-2 max-w-sm text-2xl leading-tight text-charcoal xl:text-3xl">Welcome Back to Timeless Elegance</h2>
                <p class="mt-2.5 max-w-sm text-sm leading-relaxed text-muted">Sign in to track orders, manage your wishlist, and enjoy early access to new collections.</p>
            </div>
        </div>

        <div class="flex items-center justify-center px-6 py-7 sm:px-8 lg:px-10 lg:py-8">
            <div class="w-full max-w-[360px]" x-data="{ showPassword: false }">
                <a href="{{ route('home') }}" class="inline-block">
                    <img src="{{ asset('logo.png') }}" alt="Rupnora" class="h-8 w-auto object-contain">
                </a>
                <h1 class="font-display mt-3 text-2xl text-charcoal">Sign In</h1>
                <p class="mt-1 text-sm text-muted">Enter your details to access your account.</p>

                @if (session('success'))
                    <div class="mt-3 rounded-xl bg-success/10 px-4 py-2.5 text-sm text-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="mt-3 rounded-xl bg-error/10 px-4 py-2.5 text-sm text-error">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="login" class="label-luxe">Email / Mobile Number</label>
                        <input id="login" type="text" name="login" value="{{ old('login') }}" required autocomplete="username" class="input-luxe" placeholder="you@example.com or 98765 43210">
                        @error('login') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password" class="label-luxe">Password</label>
                        <div class="relative">
                            <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" class="input-luxe pr-11" placeholder="Enter your password">
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-muted hover:text-charcoal" aria-label="Show or hide password">
                                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" /><circle cx="12" cy="12" r="3" /></svg>
                            </button>
                        </div>
                        @error('password') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-charcoal-soft">
                            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-line text-champagne-dark focus:ring-champagne-dark/40">
                            Remember Me
                        </label>
                        <a href="{{ route('password.request') }}" class="font-medium text-champagne-dark hover:underline">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn-primary w-full">Sign In</button>
                </form>

                <p class="mt-4 text-center text-sm text-muted">
                    Don't have an account? <a href="{{ route('register') }}" class="font-semibold text-champagne-dark hover:underline">Create Account</a>
                </p>
            </div>
        </div>
        </div>
    </section>
</x-layouts.app>

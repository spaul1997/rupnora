<x-layouts.app title="Create Account">
    <section class="container-luxe py-6 sm:py-8 lg:py-10">
        <div class="grid overflow-hidden rounded-3xl border border-line bg-paper shadow-card lg:grid-cols-[0.9fr_1.1fr]">
        <div class="relative hidden min-h-[480px] overflow-hidden bg-gradient-to-br from-champagne-light/50 via-ivory to-beige lg:block">
            <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 22px 22px; color: var(--color-charcoal);"></div>
            <div class="absolute inset-0">
                <x-ui.optimized-image :src="asset('images/register.png')" alt="Rupnora" sizes="50vw" class="h-full w-full object-cover" />
            </div>
            <div class="relative flex h-full flex-col justify-end p-8 xl:p-10">
                <span class="eyebrow">Join Rupnora</span>
                <h2 class="font-display mt-2 max-w-sm text-2xl leading-tight text-charcoal xl:text-3xl">Become Part of Our Story</h2>
                <p class="mt-2.5 max-w-sm text-sm leading-relaxed text-muted">Create an account for faster checkout, order tracking and exclusive member offers.</p>
            </div>
        </div>

        <div class="flex items-center justify-center px-6 py-7 sm:px-8 lg:px-10 lg:py-8">
            <div class="w-full max-w-lg" x-data="{ showPw: false, showPwConfirm: false }">
                <a href="{{ route('home') }}" class="inline-block">
                    <img src="{{ asset('logo.png') }}" alt="Rupnora" class="h-8 w-auto object-contain">
                </a>

                <h1 class="font-display mt-3 text-2xl text-charcoal">Create Account</h1>
                <p class="mt-1 text-sm text-muted">Join us to start your jewellery journey.</p>

                @if ($errors->any())
                    <div class="mt-3 rounded-xl bg-error/10 px-4 py-2.5 text-sm text-error">
                        Please check the highlighted fields and try again.
                    </div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="label-luxe">First Name</label>
                            <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name" class="input-luxe" placeholder="Ananya">
                            @error('first_name') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="last_name" class="label-luxe">Last Name</label>
                            <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name" class="input-luxe" placeholder="Rao">
                            @error('last_name') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="label-luxe">Mobile Number</label>
                            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" class="input-luxe" placeholder="+91 98765 43210">
                            @error('phone') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email" class="label-luxe">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="input-luxe" placeholder="you@example.com">
                            @error('email') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="password" class="label-luxe">Password</label>
                            <div class="relative">
                                <input id="password" :type="showPw ? 'text' : 'password'" name="password" required minlength="8" autocomplete="new-password" class="input-luxe pr-11" placeholder="Minimum 8 characters">
                                <button type="button" @click="showPw = !showPw" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-muted hover:text-charcoal" aria-label="Show or hide password">
                                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" /><circle cx="12" cy="12" r="3" /></svg>
                                </button>
                            </div>
                            @error('password') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="label-luxe">Confirm Password</label>
                            <div class="relative">
                                <input id="password_confirmation" :type="showPwConfirm ? 'text' : 'password'" name="password_confirmation" required minlength="8" autocomplete="new-password" class="input-luxe pr-11" placeholder="Re-enter password">
                                <button type="button" @click="showPwConfirm = !showPwConfirm" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-muted hover:text-charcoal" aria-label="Show or hide password confirmation">
                                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" /><circle cx="12" cy="12" r="3" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <label class="flex items-start gap-2.5 text-sm text-charcoal-soft">
                        <input type="checkbox" name="terms" value="1" @checked(old('terms')) required class="mt-0.5 h-4 w-4 flex-shrink-0 rounded border-line text-champagne-dark focus:ring-champagne-dark/40">
                        <span>I agree to the <a href="{{ route('terms-of-service') }}" class="font-medium text-champagne-dark hover:underline">Terms of Service</a> and <a href="{{ route('privacy-policy') }}" class="font-medium text-champagne-dark hover:underline">Privacy Policy</a>.</span>
                    </label>
                    @error('terms') <p class="text-xs text-error">{{ $message }}</p> @enderror

                    <button type="submit" class="btn-primary w-full">Create Account</button>
                </form>

                <p class="mt-4 text-center text-sm text-muted">
                    Already have an account? <a href="{{ route('login') }}" class="font-semibold text-champagne-dark hover:underline">Sign In</a>
                </p>
            </div>
        </div>
        </div>
    </section>
</x-layouts.app>

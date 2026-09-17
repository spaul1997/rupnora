<x-layouts.app title="Forgot Password">
    <section class="container-luxe py-10 sm:py-14 lg:py-16">
        <div class="mx-auto w-full max-w-lg rounded-3xl border border-line bg-paper p-6 shadow-card sm:p-10">
            <a href="{{ route('home') }}" class="inline-block">
                <img src="{{ asset('logo.png') }}" alt="Rupnora" class="h-9 w-auto object-contain">
            </a>

            @if (session('status'))
                <div class="mt-5 rounded-xl bg-success/10 px-4 py-3 text-sm text-success">{{ session('status') }}</div>
            @endif

            @if ($step === 'request')
                <h1 class="font-display mt-4 text-3xl text-charcoal">Forgot Password?</h1>
                <p class="mt-1.5 text-sm text-muted">Enter your registered email or mobile number and we'll email you a reset code.</p>

                <form method="POST" action="{{ route('password.email') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="login" class="label-luxe">Email / Mobile Number</label>
                        <input id="login" type="text" name="login" value="{{ old('login') }}" required autocomplete="username" class="input-luxe" placeholder="you@example.com or 98765 43210">
                        @error('login') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="btn-primary w-full">Send Reset Code</button>
                </form>

                <p class="mt-6 text-center text-sm text-muted">
                    Remembered your password? <a href="{{ route('login') }}" class="font-semibold text-champagne-dark hover:underline">Sign In</a>
                </p>
            @elseif ($step === 'otp')
                <h1 class="font-display mt-4 text-3xl text-charcoal">Enter Reset Code</h1>
                <p class="mt-1.5 text-sm text-muted">Enter the 4-digit code sent to your registered email. It expires in 10 minutes.</p>

                <form method="POST" action="{{ route('password.verify') }}" class="mt-6 space-y-5">
                    @csrf
                    <div class="flex justify-between gap-3">
                        @for ($i = 0; $i < 4; $i++)
                            <input type="text" name="otp[]" inputmode="numeric" pattern="[0-9]" maxlength="1" required autocomplete="one-time-code" oninput="if (this.value && this.nextElementSibling) this.nextElementSibling.focus()" class="h-14 w-14 rounded-xl border border-line text-center text-xl font-semibold text-charcoal focus:border-champagne-dark focus:outline-none focus:ring-2 focus:ring-champagne/25">
                        @endfor
                    </div>
                    @error('otp') <p class="text-xs text-error">{{ $message }}</p> @enderror
                    @error('otp.*') <p class="text-xs text-error">{{ $message }}</p> @enderror
                    <button type="submit" class="btn-primary w-full">Verify Code</button>
                </form>

                <form method="POST" action="{{ route('password.email') }}" class="mt-4 text-center">
                    @csrf
                    <input type="hidden" name="login" value="{{ $resetEmail }}">
                    <button type="submit" class="text-sm font-semibold text-champagne-dark hover:underline">Resend Code</button>
                </form>
            @elseif ($step === 'reset')
                <div x-data="{ showPassword: false }">
                    <h1 class="font-display mt-4 text-3xl text-charcoal">Create New Password</h1>
                    <p class="mt-1.5 text-sm text-muted">Choose a strong password you haven't used before.</p>

                    <form method="POST" action="{{ route('password.update') }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="password" class="label-luxe">New Password</label>
                            <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required minlength="8" autocomplete="new-password" class="input-luxe" placeholder="Minimum 8 characters">
                            @error('password') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="label-luxe">Confirm New Password</label>
                            <input id="password_confirmation" :type="showPassword ? 'text' : 'password'" name="password_confirmation" required minlength="8" autocomplete="new-password" class="input-luxe" placeholder="Re-enter new password">
                        </div>
                        <label class="flex items-center gap-2 text-sm text-muted">
                            <input type="checkbox" @change="showPassword = $event.target.checked" class="h-4 w-4 rounded border-line text-champagne-dark focus:ring-champagne-dark/40">
                            Show passwords
                        </label>
                        <button type="submit" class="btn-primary w-full">Reset Password</button>
                    </form>
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>

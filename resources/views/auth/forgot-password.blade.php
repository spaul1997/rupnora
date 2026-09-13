<x-layouts.app title="Forgot Password">
    <div class="flex min-h-[calc(100vh-1px)] items-center justify-center px-5 py-16 sm:px-10">
        <div
            class="w-full max-w-sm"
            x-data="{
                step: 'request',
                otp: ['', '', '', ''],
                timer: 30,
                interval: null,
                pw: '', pwConfirm: '',
                startTimer() { this.timer = 30; clearInterval(this.interval); this.interval = setInterval(() => { if (this.timer > 0) this.timer--; else clearInterval(this.interval); }, 1000); },
                sendCode() { this.step = 'otp'; this.startTimer(); },
                verify() { this.step = 'reset'; },
                resetPw() { this.step = 'success'; }
            }"
        >
            <a href="{{ route('home') }}" class="font-display text-2xl text-charcoal">Aurelle</a>

            {{-- Step 1: Request --}}
            <div x-show="step === 'request'" x-cloak>
                <h1 class="font-display mt-6 text-3xl text-charcoal">Forgot Password?</h1>
                <p class="mt-2 text-sm text-muted">Enter your registered email or mobile number and we'll send you a reset code.</p>
                <form class="mt-6 space-y-5" @submit.prevent="sendCode()">
                    <div>
                        <label class="label-luxe">Email / Mobile Number</label>
                        <input type="text" required class="input-luxe" placeholder="you@example.com or 98765 43210">
                    </div>
                    <button type="submit" class="btn-primary w-full">Send Reset Code</button>
                </form>
                <p class="mt-8 text-center text-sm text-muted">
                    Remembered your password? <a href="{{ route('login') }}" class="font-semibold text-champagne-dark hover:underline">Sign In</a>
                </p>
            </div>

            {{-- Step 2: OTP --}}
            <div x-show="step === 'otp'" x-cloak>
                <h1 class="font-display mt-6 text-3xl text-charcoal">Enter Reset Code</h1>
                <p class="mt-2 text-sm text-muted">We've sent a 4-digit verification code to your registered contact.</p>
                <form class="mt-8 space-y-6" @submit.prevent="verify()">
                    <div class="flex justify-between gap-3">
                        @for ($i = 0; $i < 4; $i++)
                            <input type="text" inputmode="numeric" maxlength="1" x-model="otp[{{ $i }}]" @input="if($event.target.value && $event.target.nextElementSibling) $event.target.nextElementSibling.focus()" class="h-14 w-14 rounded-xl border border-line text-center text-xl font-semibold text-charcoal focus:border-champagne-dark focus:outline-none focus:ring-2 focus:ring-champagne/25">
                        @endfor
                    </div>
                    <button type="submit" class="btn-primary w-full">Verify Code</button>
                    <p class="text-center text-sm text-muted">
                        <template x-if="timer > 0"><span>Resend code in <span class="font-semibold text-charcoal" x-text="timer"></span>s</span></template>
                        <template x-if="timer === 0"><button type="button" @click="startTimer()" class="font-semibold text-champagne-dark hover:underline">Resend Code</button></template>
                    </p>
                </form>
            </div>

            {{-- Step 3: Reset --}}
            <div x-show="step === 'reset'" x-cloak>
                <h1 class="font-display mt-6 text-3xl text-charcoal">Create New Password</h1>
                <p class="mt-2 text-sm text-muted">Choose a strong password you haven't used before.</p>
                <form class="mt-6 space-y-5" @submit.prevent="resetPw()">
                    <div>
                        <label class="label-luxe">New Password</label>
                        <input type="password" x-model="pw" required minlength="8" class="input-luxe" placeholder="Minimum 8 characters">
                    </div>
                    <div>
                        <label class="label-luxe">Confirm New Password</label>
                        <input type="password" x-model="pwConfirm" required minlength="8" class="input-luxe" placeholder="Re-enter new password">
                    </div>
                    <p x-show="pw && pwConfirm && pw !== pwConfirm" x-cloak class="text-xs text-error">Passwords do not match.</p>
                    <button type="submit" class="btn-primary w-full" :disabled="!pw || pw !== pwConfirm">Reset Password</button>
                </form>
            </div>

            {{-- Step 4: Success --}}
            <div x-show="step === 'success'" x-cloak class="pt-10 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-success/10">
                    <svg class="h-7 w-7 text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </div>
                <h1 class="font-display mt-5 text-2xl text-charcoal">Password Reset</h1>
                <p class="mt-2 text-sm text-muted">Your password has been updated successfully.</p>
                <a href="{{ route('login') }}" class="btn-primary mt-7 w-full">Back to Sign In</a>
            </div>
        </div>
    </div>
</x-layouts.app>

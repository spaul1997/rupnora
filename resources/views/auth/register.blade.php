<x-layouts.app title="Create Account">
    <div class="grid min-h-[calc(100vh-1px)] grid-cols-1 lg:grid-cols-2">
        <div class="relative hidden overflow-hidden bg-gradient-to-br from-champagne-light/50 via-ivory to-beige lg:block">
            <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 22px 22px; color: var(--color-charcoal);"></div>
            <div class="absolute inset-0 opacity-60">
                <x-ui.product-art art="diamond" class="aspect-auto h-full" />
            </div>
            <div class="relative flex h-full flex-col justify-end p-12 xl:p-16">
                <span class="eyebrow">Join Aurelle</span>
                <h2 class="font-display mt-3 max-w-sm text-4xl leading-tight text-charcoal">Become Part of Our Story</h2>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-muted">Create an account for faster checkout, order tracking and exclusive member offers.</p>
            </div>
        </div>

        <div class="flex items-center justify-center px-5 py-16 sm:px-10">
            <div
                class="w-full max-w-sm"
                x-data="{
                    step: 'form',
                    otp: ['', '', '', ''],
                    timer: 30,
                    interval: null,
                    startTimer() { this.timer = 30; clearInterval(this.interval); this.interval = setInterval(() => { if (this.timer > 0) this.timer--; else clearInterval(this.interval); }, 1000); },
                    submitForm() { this.step = 'otp'; this.startTimer(); },
                    verifyOtp() { this.step = 'success'; }
                }"
            >
                <a href="{{ route('home') }}" class="font-display text-2xl text-charcoal">Aurelle</a>

                {{-- Step 1: Form --}}
                <div x-show="step === 'form'" x-cloak>
                    <h1 class="font-display mt-6 text-3xl text-charcoal">Create Account</h1>
                    <p class="mt-2 text-sm text-muted">Join us to start your jewellery journey.</p>

                    <form class="mt-6 space-y-5" @submit.prevent="submitForm()">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label-luxe">First Name</label>
                                <input type="text" required class="input-luxe" placeholder="Ananya">
                            </div>
                            <div>
                                <label class="label-luxe">Last Name</label>
                                <input type="text" required class="input-luxe" placeholder="Rao">
                            </div>
                        </div>
                        <div>
                            <label class="label-luxe">Mobile Number</label>
                            <input type="tel" required class="input-luxe" placeholder="+91 98765 43210">
                        </div>
                        <div>
                            <label class="label-luxe">Email</label>
                            <input type="email" required class="input-luxe" placeholder="you@example.com">
                        </div>
                        <div>
                            <label class="label-luxe">Password</label>
                            <input type="password" required minlength="8" class="input-luxe" placeholder="Minimum 8 characters">
                        </div>
                        <div>
                            <label class="label-luxe">Confirm Password</label>
                            <input type="password" required minlength="8" class="input-luxe" placeholder="Re-enter password">
                        </div>
                        <label class="flex items-start gap-2.5 text-sm text-charcoal-soft">
                            <input type="checkbox" required class="mt-0.5 h-4 w-4 flex-shrink-0 rounded border-line text-champagne-dark focus:ring-champagne-dark/40">
                            <span>I agree to the <a href="#" class="font-medium text-champagne-dark hover:underline">Terms &amp; Conditions</a> and <a href="#" class="font-medium text-champagne-dark hover:underline">Privacy Policy</a>.</span>
                        </label>
                        <button type="submit" class="btn-primary w-full">Create Account</button>
                    </form>

                    <p class="mt-8 text-center text-sm text-muted">
                        Already have an account? <a href="{{ route('login') }}" class="font-semibold text-champagne-dark hover:underline">Sign In</a>
                    </p>
                </div>

                {{-- Step 2: OTP --}}
                <div x-show="step === 'otp'" x-cloak>
                    <h1 class="font-display mt-6 text-3xl text-charcoal">Verify Your Number</h1>
                    <p class="mt-2 text-sm text-muted">Enter the 4-digit code we sent to your mobile number.</p>

                    <form class="mt-8 space-y-6" @submit.prevent="verifyOtp()">
                        <div class="flex justify-between gap-3">
                            @for ($i = 0; $i < 4; $i++)
                                <input type="text" inputmode="numeric" maxlength="1" x-model="otp[{{ $i }}]" @input="if($event.target.value && $event.target.nextElementSibling) $event.target.nextElementSibling.focus()" class="h-14 w-14 rounded-xl border border-line text-center text-xl font-semibold text-charcoal focus:border-champagne-dark focus:outline-none focus:ring-2 focus:ring-champagne/25">
                            @endfor
                        </div>
                        <button type="submit" class="btn-primary w-full">Verify &amp; Continue</button>
                        <p class="text-center text-sm text-muted">
                            <template x-if="timer > 0">
                                <span>Resend OTP in <span class="font-semibold text-charcoal" x-text="timer"></span>s</span>
                            </template>
                            <template x-if="timer === 0">
                                <button type="button" @click="startTimer()" class="font-semibold text-champagne-dark hover:underline">Resend OTP</button>
                            </template>
                        </p>
                    </form>
                </div>

                {{-- Step 3: Success --}}
                <div x-show="step === 'success'" x-cloak class="pt-10 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-success/10">
                        <svg class="h-7 w-7 text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </div>
                    <h1 class="font-display mt-5 text-2xl text-charcoal">Welcome to Aurelle</h1>
                    <p class="mt-2 text-sm text-muted">Your account has been created successfully.</p>
                    <a href="{{ route('account.dashboard') }}" class="btn-primary mt-7 w-full">Go to My Account</a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

<x-layouts.app title="Create Account">
    <div class="grid min-h-[calc(100vh-1px)] grid-cols-1 lg:grid-cols-2">
        <div class="relative hidden overflow-hidden bg-gradient-to-br from-champagne-light/50 via-ivory to-beige lg:block">
            <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 22px 22px; color: var(--color-charcoal);"></div>
            <div class="absolute inset-0 opacity-60">
                <x-ui.product-art art="diamond" class="aspect-auto h-full" />
            </div>
            <div class="relative flex h-full flex-col justify-end p-12 xl:p-16">
                <span class="eyebrow">Join Rupnora</span>
                <h2 class="font-display mt-3 max-w-sm text-4xl leading-tight text-charcoal">Become Part of Our Story</h2>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-muted">Create an account for faster checkout, order tracking and exclusive member offers.</p>
            </div>
        </div>

        <div class="flex items-center justify-center px-5 py-16 sm:px-10">
            <div class="w-full max-w-sm text-center lg:text-left">
                <a href="{{ route('home') }}" class="inline-block">
                    <img src="{{ asset('logo.png') }}" alt="Rupnora" class="h-10 w-auto object-contain">
                </a>

                <span class="eyebrow mt-8 block">Account Registration</span>
                <h1 class="font-display mt-2 text-3xl text-charcoal">Coming Soon</h1>
                <p class="mt-3 text-sm leading-relaxed text-muted">
                    We're putting the finishing touches on new customer accounts. In the meantime, you can continue browsing our collections, or sign in if you already have an account.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center lg:justify-start">
                    <a href="{{ route('home') }}" class="btn-primary">Continue Shopping</a>
                    <a href="{{ route('login') }}" class="btn-ghost">Sign In</a>
                </div>

                <p class="mt-8 text-sm text-muted">
                    Need help? <a href="{{ route('contact') }}" class="font-semibold text-champagne-dark hover:underline">Contact Us</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>

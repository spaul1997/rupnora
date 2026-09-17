<x-layouts.app title="Influencer Program" description="The Rupnora Influencer Program is coming soon.">
    <section class="relative isolate flex min-h-[70vh] items-center overflow-hidden bg-charcoal py-20 sm:py-28">
        <div class="absolute inset-0 -z-10 opacity-[0.08]" style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 24px 24px; color: var(--color-ivory);"></div>
        <div class="absolute -left-24 top-10 -z-10 h-72 w-72 rounded-full bg-champagne-dark/20 blur-3xl"></div>
        <div class="absolute -right-20 bottom-0 -z-10 h-80 w-80 rounded-full bg-champagne-light/10 blur-3xl"></div>

        <div class="container-luxe text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-ivory/20 bg-ivory/10 text-champagne-light">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M4 19.5V8.8a2 2 0 012-2h12a2 2 0 012 2v10.7M8 6.8V5a4 4 0 018 0v1.8M2 19.5h20" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M9 12h6M9 15h4" stroke-linecap="round" />
                </svg>
            </div>

            <span class="eyebrow mt-7 block text-champagne-light">Rupnora Partner Program</span>
            <h1 class="font-display mt-3 text-4xl text-ivory sm:text-5xl lg:text-6xl">Coming Soon</h1>
            <p class="mx-auto mt-5 max-w-xl text-sm leading-7 text-ivory/70 sm:text-base">
                We are creating an exclusive program for creators who love timeless jewellery. Applications and partnership details will be available soon.
            </p>

            <div class="mt-9 flex flex-wrap justify-center gap-3">
                <a href="{{ route('home') }}" class="btn-light">Back to Home</a>
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center rounded-full border border-ivory/30 px-7 py-3.5 text-[13px] font-semibold uppercase tracking-[0.14em] text-ivory transition-colors hover:bg-ivory/10">Contact Us</a>
            </div>
        </div>
    </section>
</x-layouts.app>

@props(['slides' => []])

<section
    x-data="{ active: 0, total: {{ count($slides) }}, timer: null, start() { this.timer = setInterval(() => this.active = (this.active + 1) % this.total, 6000) }, stop() { clearInterval(this.timer) } }"
    x-init="start()"
    @mouseenter="stop()" @mouseleave="start()"
    class="relative"
>
    <div class="relative">
        @foreach ($slides as $i => $slide)
            <div x-show="active === {{ $i }}" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" @if($i > 0) x-cloak @endif>
                <x-ui.hero-slide :tone="$i + 1" :eyebrow="$slide['eyebrow']" :heading="$slide['heading']" :subheading="$slide['subheading']" :primary-label="$slide['primaryLabel']" :primary-url="$slide['primaryUrl']" :secondary-label="$slide['secondaryLabel']" :secondary-url="$slide['secondaryUrl']" :image="$slide['image'] ?? null" />
            </div>
        @endforeach
    </div>

    <button @click="stop(); active = (active - 1 + total) % total" class="absolute left-3 top-1/2 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-charcoal/15 bg-paper/80 text-charcoal backdrop-blur transition-colors hover:bg-paper sm:flex" aria-label="Previous slide">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
    </button>
    <button @click="stop(); active = (active + 1) % total" class="absolute right-3 top-1/2 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-charcoal/15 bg-paper/80 text-charcoal backdrop-blur transition-colors hover:bg-paper sm:flex" aria-label="Next slide">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
    </button>

    <div class="absolute bottom-6 left-1/2 flex -translate-x-1/2 gap-2">
        @foreach ($slides as $i => $slide)
            <button @click="stop(); active = {{ $i }}" class="h-1.5 rounded-full transition-all duration-300" :class="active === {{ $i }} ? 'w-7 bg-champagne-dark' : 'w-1.5 bg-charcoal/20'" aria-label="Go to slide {{ $i + 1 }}"></button>
        @endforeach
    </div>
</section>

@props(['slides' => [], 'products' => []])

@php
    $total = count($slides);
    $tiles = array_slice($products, 0, 5);
    // Repeat a short list so one marquee half is always wider than its box and the loop never shows a gap.
    $tiles = $tiles ? array_merge(...array_fill(0, (int) ceil(5 / count($tiles)), $tiles)) : [];
@endphp

{{-- One `active` index drives both the center taglines and the mobile card, so they always match. --}}
<section
    x-data="{
        active: 0,
        total: {{ $total }},
        timer: null,
        touchX: 0,
        touchY: 0,
        go(i) { this.active = (i + this.total) % this.total },
        start() {
            this.stop();
            if (this.total > 1 && !matchMedia('(prefers-reduced-motion: reduce)').matches) this.timer = setInterval(() => this.go(this.active + 1), 6000);
        },
        stop() { clearInterval(this.timer) },
        swipe(e) {
            const dx = e.changedTouches[0].clientX - this.touchX, dy = e.changedTouches[0].clientY - this.touchY;
            if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) { this.stop(); this.go(this.active + (dx < 0 ? 1 : -1)) }
        },
    }"
    x-init="start()"
    @mouseenter="stop()" @mouseleave="start()" @focusin="stop()" @focusout="start()"
    class="relative isolate overflow-hidden bg-gradient-to-br from-charcoal via-charcoal-soft to-champagne-dark"
>
    <div class="pointer-events-none absolute -left-32 -top-24 h-96 w-96 rounded-full bg-[radial-gradient(circle,var(--color-champagne)_0%,transparent_65%)] opacity-30" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-32 right-0 h-96 w-96 rounded-full bg-[radial-gradient(circle,var(--color-champagne-light)_0%,transparent_65%)] opacity-15" aria-hidden="true"></div>

    <div class="container-luxe relative grid items-center gap-8 py-6 sm:py-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)_auto] lg:gap-10 lg:py-10 xl:gap-14">

        {{-- Left (desktop only): model photo with an animated jewellery strip underneath. --}}
        <div class="hidden min-h-0 flex-col gap-4 lg:flex lg:self-stretch">
            <div class="relative min-h-64 flex-1 overflow-hidden rounded-3xl border border-ivory/15 shadow-lift">
                <x-ui.optimized-image :src="asset('images/her.png')" alt="Woman wearing Rupnora jewellery" sizes="(min-width: 1280px) 460px, 30vw" class="absolute inset-0 h-full w-full object-cover object-top" />
                <div class="absolute inset-0 bg-gradient-to-t from-charcoal/60 via-transparent to-transparent" aria-hidden="true"></div>
            </div>

            @if ($tiles)
                <div class="overflow-hidden [mask-image:linear-gradient(to_right,transparent,#000_8%,#000_92%,transparent)]">
                    {{-- Two identical halves + translateX(-50%) = seamless loop; the copy is inert so keyboard/screen readers skip it. --}}
                    <div class="flex w-max animate-marquee hover:[animation-play-state:paused] focus-within:[animation-play-state:paused]" style="animation-duration: 20s">
                        @foreach ([false, true] as $isCopy)
                            <div class="flex" @if ($isCopy) inert aria-hidden="true" @endif>
                                @foreach ($tiles as $product)
                                    <a href="{{ route('product.show', $product['slug'] ?? $product['id']) }}" class="group/tile mr-3 block w-24 shrink-0 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-champagne-light focus-visible:ring-offset-2 focus-visible:ring-offset-charcoal">
                                        <span class="block aspect-square overflow-hidden rounded-xl border border-ivory/20 bg-ivory/10">
                                            @if (! empty($product['image']))
                                                <x-ui.optimized-image :src="$product['image']" alt="" sizes="96px" class="h-full w-full object-cover transition-transform duration-500 group-hover/tile:scale-110" />
                                            @else
                                                <x-ui.product-art :art="$product['art']" class="h-full w-full transition-transform duration-500 group-hover/tile:scale-110" />
                                            @endif
                                        </span>
                                        <span class="mt-1.5 block truncate text-[11px] leading-tight text-ivory/85">{{ $product['name'] }}</span>
                                        <span class="block text-[11px] font-semibold text-champagne-light">₹{{ number_format($product['price']) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Center (desktop only): taglines. Slides share one grid cell so the block never changes height. --}}
        <div class="mx-auto hidden w-full max-w-md lg:grid">
            @foreach ($slides as $i => $slide)
                <div class="col-start-1 row-start-1 flex flex-col items-center justify-center text-center transition-all duration-700 ease-out @if ($i > 0) invisible translate-y-4 opacity-0 @endif" :class="{ 'invisible translate-y-4 opacity-0': active !== {{ $i }} }">
                    @if ($slide['eyebrow'])
                        <span class="eyebrow text-champagne-light">{{ $slide['eyebrow'] }}</span>
                    @endif
                    <h1 class="font-display mt-4 text-3xl leading-[1.12] text-ivory xl:text-5xl">{{ $slide['heading'] }}</h1>
                    @if ($slide['subheading'])
                        <p class="mt-5 text-[15px] leading-relaxed text-ivory/75">{{ $slide['subheading'] }}</p>
                    @endif
                    @if (($slide['primaryLabel'] && $slide['primaryUrl']) || ($slide['secondaryLabel'] && $slide['secondaryUrl']))
                        <div class="mt-8 flex flex-wrap justify-center gap-3">
                            @if ($slide['primaryLabel'] && $slide['primaryUrl'])
                                <a href="{{ $slide['primaryUrl'] }}" class="btn-light">{{ $slide['primaryLabel'] }}</a>
                            @endif
                            @if ($slide['secondaryLabel'] && $slide['secondaryUrl'])
                                <a href="{{ $slide['secondaryUrl'] }}" class="btn-ghost border-ivory/40 text-ivory hover:border-ivory hover:bg-ivory/10">{{ $slide['secondaryLabel'] }}</a>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Right (the only column on mobile): admin "Mobile Banner" images in a card. --}}
        <div class="relative mx-auto w-72 max-w-full" role="region" aria-roledescription="carousel" aria-label="Featured offers">
            <div class="pointer-events-none absolute -inset-6 rounded-full bg-[radial-gradient(circle,var(--color-champagne)_0%,transparent_70%)] opacity-40" aria-hidden="true"></div>
            <div class="relative rounded-[2rem] border border-ivory/25 bg-ivory/10 p-2 shadow-lift backdrop-blur-sm" @touchstart.passive="touchX = $event.touches[0].clientX; touchY = $event.touches[0].clientY" @touchend="swipe($event)">
                <div class="relative aspect-[9/16] overflow-hidden rounded-3xl bg-charcoal">
                    @foreach ($slides as $i => $slide)
                        @php($cardImage = $slide['mobileImage'] ?: $slide['image'])
                        <div class="absolute inset-0 transition-opacity duration-700 @if ($i > 0) invisible opacity-0 @endif" :class="{ 'invisible opacity-0': active !== {{ $i }} }">
                            @if ($cardImage)
                                <x-ui.optimized-image :src="$cardImage" :alt="$slide['heading']" sizes="288px" :loading="$i === 0 ? 'eager' : 'lazy'" :fetchpriority="$i === 0 ? 'high' : null" class="h-full w-full object-cover" />
                                @if ($slide['primaryUrl'])
                                    <a href="{{ $slide['primaryUrl'] }}" class="absolute inset-0" aria-label="{{ $slide['primaryLabel'] ?: $slide['heading'] }}"></a>
                                @endif
                            @else
                                {{-- Banner without an image (e.g. the built-in defaults): show its text instead. --}}
                                <div class="flex h-full flex-col items-center justify-center bg-gradient-to-b from-champagne-dark via-charcoal-soft to-charcoal px-6 text-center">
                                    @if ($slide['eyebrow'])
                                        <span class="eyebrow text-champagne-light">{{ $slide['eyebrow'] }}</span>
                                    @endif
                                    <h2 class="font-display mt-3 text-2xl leading-tight text-ivory">{{ $slide['heading'] }}</h2>
                                    @if ($slide['primaryLabel'] && $slide['primaryUrl'])
                                        <a href="{{ $slide['primaryUrl'] }}" class="btn-light mt-6 !px-5 !py-3 !text-[11px]">{{ $slide['primaryLabel'] }}</a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach

                    @if ($total > 1)
                        <button type="button" @click="stop(); go(active - 1)" class="absolute left-2 top-1/2 z-10 hidden h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-paper/85 text-charcoal shadow-card backdrop-blur transition hover:bg-paper sm:flex" aria-label="Previous slide">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </button>
                        <button type="button" @click="stop(); go(active + 1)" class="absolute right-2 top-1/2 z-10 hidden h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-paper/85 text-charcoal shadow-card backdrop-blur transition hover:bg-paper sm:flex" aria-label="Next slide">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </button>

                        {{-- pointer-events-none keeps the banner link clickable under the fade; each dot re-enables its own hit area. --}}
                        <div class="pointer-events-none absolute inset-x-0 bottom-0 z-10 flex justify-center bg-gradient-to-t from-charcoal/50 to-transparent pb-1.5 pt-8">
                            @foreach ($slides as $i => $slide)
                                <button type="button" @click="stop(); go({{ $i }})" class="pointer-events-auto flex h-6 items-center px-1" aria-label="Go to slide {{ $i + 1 }}" :aria-current="active === {{ $i }}">
                                    <span class="block h-1.5 rounded-full transition-all duration-300" :class="active === {{ $i }} ? 'w-6 bg-ivory' : 'w-1.5 bg-ivory/50'"></span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

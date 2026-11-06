@props(['slides' => [], 'products' => []])

@php
    $total = count($slides);
    $models = [
        ['image' => 'images/banner11.png', 'alt' => 'Model styling a pendant necklace and drop earrings'],
        ['image' => 'images/banner12.png', 'alt' => 'Model wearing jewellery with an everyday look'],
        ['image' => 'images/banner13.png', 'alt' => 'Model exploring statement earrings and bracelets'],
    ];
    $cardSlides = [
        ['mobileImage' => asset('images/mbanner1.png'), 'heading' => 'Rupnora jewellery banner 1'],
        ['mobileImage' => asset('images/mbanner2.png'), 'heading' => 'Rupnora jewellery banner 2'],
        ['mobileImage' => asset('images/mbanner3.png'), 'heading' => 'Rupnora jewellery banner 3'],
    ];
    $cardTotal = count($cardSlides);
    $modelOptions = array_map(fn ($slide) => array_keys(array_filter(
        $models,
        fn ($model) => asset($model['image']) !== $slide['mobileImage'],
    )), $cardSlides);
    $initialModel = $modelOptions[0][0] ?? 0;
    $tiles = array_slice($products, 0, 5);
    // Repeat a short list so one marquee half is always wider than its box and the loop never shows a gap.
    $tiles = $tiles ? array_merge(...array_fill(0, (int) ceil(5 / count($tiles)), $tiles)) : [];
@endphp

{{-- The card cycles through mobile banners independently of the center taglines. --}}
<section
    x-data="{
        active: 0,
        total: {{ $total }},
        cardActive: 0,
        cardTotal: {{ $cardTotal }},
        modelActive: {{ $initialModel }},
        modelTotal: {{ count($models) }},
        modelOptions: @js($modelOptions),
        timer: null,
        touchX: 0,
        touchY: 0,
        go(i) { this.active = (i + this.total) % this.total },
        goCard(i) {
            if (this.cardTotal) {
                this.cardActive = (i + this.cardTotal) % this.cardTotal;
                this.avoidDuplicateModel();
            }
        },
        avoidDuplicateModel() {
            const options = this.modelOptions[this.cardActive];
            for (let tries = 0; !options.includes(this.modelActive) && tries < this.modelTotal; tries++) {
                this.modelActive = (this.modelActive + 1) % this.modelTotal;
            }
        },
        start() {
            this.stop();
            if ((this.total > 1 || this.cardTotal > 1 || this.modelTotal > 1) && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
                this.timer = setInterval(() => {
                    if (this.total > 1) this.go(this.active + 1);
                    this.modelActive = (this.modelActive + 1) % this.modelTotal;
                    if (this.cardTotal > 1) this.goCard(this.cardActive + 1);
                    this.avoidDuplicateModel();
                }, 3000);
            }
        },
        stop() { clearInterval(this.timer) },
        swipe(e) {
            const dx = e.changedTouches[0].clientX - this.touchX, dy = e.changedTouches[0].clientY - this.touchY;
            if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) { this.stop(); this.goCard(this.cardActive + (dx < 0 ? 1 : -1)) }
        },
    }"
    x-init="start()"
    @mouseenter="stop()" @mouseleave="start()" @focusin="stop()" @focusout="start()"
    class="relative isolate overflow-hidden bg-gradient-to-br from-charcoal via-charcoal-soft to-champagne-dark"
>
    <div class="hero-glow pointer-events-none absolute -left-32 -top-24 h-96 w-96 rounded-full bg-[radial-gradient(circle,var(--color-champagne)_0%,transparent_65%)] opacity-30" aria-hidden="true"></div>
    <div class="hero-glow pointer-events-none absolute -bottom-32 right-0 h-96 w-96 rounded-full bg-[radial-gradient(circle,var(--color-champagne-light)_0%,transparent_65%)] opacity-15" style="animation-delay: -7s" aria-hidden="true"></div>

    <div class="container-luxe relative grid items-center gap-8 py-6 sm:py-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)_auto] lg:gap-10 lg:py-10 xl:gap-14">

        {{-- Left (desktop only): rotating model photos with an animated jewellery strip underneath. --}}
        <div class="hidden min-h-0 flex-col gap-4 lg:flex lg:self-stretch">
            <div class="relative min-h-64 flex-1 overflow-hidden rounded-3xl border border-ivory/15 shadow-lift">
                @foreach ($models as $i => $model)
                    <div class="hero-model-slide absolute inset-0 transition-opacity duration-700 @if ($i !== $initialModel) opacity-0 @endif" :class="{ 'opacity-0': modelActive !== {{ $i }} }" aria-hidden="{{ $i !== $initialModel ? 'true' : 'false' }}" :aria-hidden="modelActive !== {{ $i }} ? 'true' : 'false'">
                        <x-ui.optimized-image :src="asset($model['image'])" :alt="$model['alt']" sizes="(min-width: 1280px) 460px, 30vw" class="h-full w-full object-cover object-top" />
                    </div>
                @endforeach
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

        {{-- Right (the only column on mobile): image-only mobile banners, with room for the tilted card. --}}
        <div class="relative mx-auto w-full max-w-[26rem] py-10 lg:w-[23rem] xl:w-[26rem]" role="region" aria-roledescription="carousel" aria-label="Featured offers">
            <div class="hero-glow pointer-events-none absolute -inset-6 rounded-full bg-[radial-gradient(circle,var(--color-champagne)_0%,transparent_70%)] opacity-40" aria-hidden="true"></div>
            <div class="hero-card-ring pointer-events-none absolute inset-x-1.5 top-1/2 aspect-square -translate-y-1/2 rounded-full" aria-hidden="true"></div>
            <div class="hero-card relative mx-auto w-3/5 rotate-[25deg] rounded-[2rem] border border-ivory/25 bg-ivory/10 p-2 shadow-lift backdrop-blur-sm" @touchstart.passive="touchX = $event.touches[0].clientX; touchY = $event.touches[0].clientY" @touchend="swipe($event)">
                <div class="hero-card-media relative aspect-[9/16] overflow-hidden rounded-3xl bg-charcoal">
                    @foreach ($cardSlides as $i => $slide)
                        <div class="absolute inset-0 transition-opacity duration-700 @if ($i > 0) opacity-0 @endif" :class="{ 'opacity-0': cardActive !== {{ $i }} }" aria-hidden="{{ $i > 0 ? 'true' : 'false' }}" :aria-hidden="cardActive !== {{ $i }} ? 'true' : 'false'">
                            <x-ui.optimized-image :src="$slide['mobileImage']" :alt="$slide['heading']" sizes="(min-width: 1024px) 250px, 60vw" :loading="$i === 0 ? 'eager' : 'lazy'" :fetchpriority="$i === 0 ? 'high' : null" class="h-full w-full object-cover" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

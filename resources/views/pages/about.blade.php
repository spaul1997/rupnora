@php
    $values = [
        [
            'number' => '01',
            'title' => 'Style that feels personal',
            'text' => 'From subtle everyday pieces to celebration-ready designs, our collections are chosen to help you express your style in your own way.',
        ],
        [
            'number' => '02',
            'title' => 'Clarity you can trust',
            'text' => 'We keep product, material, price and certification details clear, so choosing a piece feels considered, confident and uncomplicated.',
        ],
        [
            'number' => '03',
            'title' => 'Care beyond checkout',
            'text' => 'Helpful guidance, secure shopping and responsive support are part of every Rupnora experience, before and after your order arrives.',
        ],
    ];

    $details = [
        [
            'title' => 'Considered curation',
            'text' => 'A versatile mix of modern favourites, timeless forms and occasion-led designs.',
            'icon' => 'M12 3l2.2 5.1L20 10l-4 3.5 1.2 5.5L12 16.2 6.8 19 8 13.5 4 10l5.8-1.9L12 3z',
        ],
        [
            'title' => 'Quality-led details',
            'text' => 'Pieces selected with close attention to finish, settings, comfort and wearability.',
            'icon' => 'M12 3l7 3v5c0 4.4-2.8 7.5-7 10-4.2-2.5-7-5.6-7-10V6l7-3zm-3 9l2 2 4-4',
        ],
        [
            'title' => 'Made for real moments',
            'text' => 'Jewellery for ordinary mornings, thoughtful gifts and milestones worth remembering.',
            'icon' => 'M12 20s-7-4.4-7-10a4 4 0 017-2.6A4 4 0 0119 10c0 5.6-7 10-7 10z',
        ],
    ];
@endphp

<x-layouts.app
    title="Our Story"
    description="Discover Rupnora — jewellery for everyday expression, thoughtful gifting and life’s most meaningful moments."
>
    <div class="container-luxe pt-6">
        <x-ui.breadcrumb :trail="[['label' => 'Our Story']]" />
    </div>

    {{-- Hero --}}
    <section class="overflow-hidden pb-16 pt-8 sm:pb-20 sm:pt-10 lg:pb-24 lg:pt-12">
        <div class="container-luxe">
            <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-8">
                <div class="relative z-10 lg:col-span-5 lg:pr-6">
                    <span class="eyebrow">The Rupnora Story</span>
                    <h1 class="font-display mt-4 text-4xl leading-[1.08] text-charcoal sm:text-5xl lg:text-6xl">
                        Made to be worn.<br>
                        <span class="text-champagne-dark">Made to be yours.</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-[15px] leading-7 text-muted sm:text-base">
                        We believe jewellery should feel as natural as it feels special. Rupnora brings together expressive design, thoughtful detail and an easy shopping experience for every day, every gift and every celebration.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('new-arrivals') }}" class="btn-primary">Shop New Arrivals</a>
                        <a href="{{ route('collections.index') }}" class="btn-ghost">Explore Collections</a>
                    </div>
                    <div class="mt-9 flex items-center gap-4 text-xs font-medium uppercase tracking-[0.16em] text-muted">
                        <span class="h-px w-12 bg-champagne"></span>
                        Everyday style, endless sparkle
                    </div>
                </div>

                <div class="relative lg:col-span-7 lg:pl-8">
                    <div class="absolute -right-24 -top-20 h-72 w-72 rounded-full bg-champagne-light/60 blur-3xl" aria-hidden="true"></div>
                    <div class="relative ml-auto max-w-2xl pb-10 sm:pb-14 sm:pl-14">
                        <div class="overflow-hidden rounded-[2rem] bg-beige shadow-soft">
                            <x-ui.optimized-image
                                :src="asset('images/story4.png')"
                                alt="Woman wearing delicate Rupnora jewellery"
                                sizes="(min-width: 1024px) 560px, 100vw"
                                loading="eager"
                                fetchpriority="high"
                                class="aspect-[4/3] h-full w-full object-cover object-center"
                            />
                        </div>
                        <div class="absolute bottom-0 left-0 hidden w-44 overflow-hidden rounded-2xl border-4 border-paper bg-paper shadow-card sm:block lg:w-48">
                            <x-ui.optimized-image
                                :src="asset('images/story2.png')"
                                alt="Gold ring with a brilliant centre stone"
                                sizes="192px"
                                class="aspect-[4/5] h-full w-full object-cover"
                            />
                        </div>
                        <div class="absolute -bottom-1 right-5 rounded-full border border-line bg-paper px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-charcoal shadow-soft sm:bottom-5">
                            Jewellery for your story
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Brand introduction --}}
    <section class="border-y border-line bg-ivory-soft py-16 sm:py-20">
        <div class="container-luxe">
            <div class="mx-auto max-w-3xl text-center">
                <span class="eyebrow">Why Rupnora</span>
                <h2 class="font-display mt-3 text-3xl leading-tight text-charcoal sm:text-4xl">Beautiful jewellery should fit into your life, not wait for an occasion.</h2>
                <p class="mx-auto mt-5 max-w-2xl text-[15px] leading-7 text-muted">
                    That idea guides everything we bring together. Our edits move easily from everyday minimal pieces to festive, bridal and gifting favourites—so there is always something that feels like you, or like someone you love.
                </p>
            </div>
        </div>
    </section>

    {{-- Story --}}
    <section class="section-pad overflow-hidden">
        <div class="container-luxe">
            <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
                <div class="relative order-2 lg:order-1">
                    <div class="absolute -bottom-8 -left-8 h-48 w-48 rounded-full bg-champagne-light/60 blur-3xl" aria-hidden="true"></div>
                    <div class="relative grid grid-cols-5 items-end gap-4">
                        <div class="col-span-3 overflow-hidden rounded-[1.5rem] bg-beige shadow-soft">
                            <x-ui.optimized-image
                                :src="asset('images/story1.png')"
                                alt="Delicate gold pendant displayed on a jewellery stand"
                                sizes="(min-width: 1024px) 330px, 60vw"
                                class="aspect-[4/5] h-full w-full object-cover"
                            />
                        </div>
                        <div class="col-span-2 mb-8 overflow-hidden rounded-[1.25rem] bg-beige shadow-card">
                            <x-ui.optimized-image
                                :src="asset('images/story3.png')"
                                alt="Diamond ring beside a jeweller's loupe and quality seal"
                                sizes="(min-width: 1024px) 220px, 40vw"
                                class="aspect-[3/4] h-full w-full object-cover object-center"
                            />
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <span class="eyebrow">Our Point of View</span>
                    <h2 class="font-display mt-3 text-3xl leading-tight text-charcoal sm:text-4xl">A little luxury for all the ways you shine</h2>
                    <p class="mt-5 text-[15px] leading-7 text-muted">
                        Rupnora is a destination for jewellery that makes getting dressed feel more personal. We curate across gold, diamond, silver and fashion-led styles with one simple purpose: to help you find pieces you will genuinely enjoy wearing.
                    </p>
                    <p class="mt-4 text-[15px] leading-7 text-muted">
                        Whether you are choosing your new everyday favourite, marking a milestone or finding a gift with meaning, we want the experience to feel warm, clear and entirely yours.
                    </p>
                    <div class="mt-7 border-l-2 border-champagne pl-5">
                        <p class="font-display text-xl italic leading-relaxed text-charcoal">“For the small joys, the big yes, and every beautiful moment in between.”</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Values --}}
    <section class="section-pad bg-beige">
        <div class="container-luxe">
            <div class="mb-10 max-w-2xl">
                <span class="eyebrow">What Matters to Us</span>
                <h2 class="font-display mt-3 text-3xl text-charcoal sm:text-4xl">The values behind every choice</h2>
            </div>
            <div class="grid gap-px overflow-hidden rounded-2xl border border-line bg-line md:grid-cols-3">
                @foreach ($values as $value)
                    <article class="bg-paper p-6 sm:p-8">
                        <div class="flex items-center gap-3">
                            <span class="font-display text-sm text-champagne-dark">{{ $value['number'] }}</span>
                            <span class="h-px flex-1 bg-line"></span>
                        </div>
                        <h3 class="font-display mt-8 text-2xl text-charcoal">{{ $value['title'] }}</h3>
                        <p class="mt-3 text-sm leading-6 text-muted">{{ $value['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Quality and care --}}
    <section class="overflow-hidden bg-charcoal py-16 sm:py-20 lg:py-24">
        <div class="container-luxe">
            <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-14">
                <div class="lg:col-span-5">
                    <span class="eyebrow text-champagne-light">Beauty in the Details</span>
                    <h2 class="font-display mt-3 text-3xl leading-tight text-ivory sm:text-4xl">Thoughtful from first look to lasting care</h2>
                    <p class="mt-5 text-[15px] leading-7 text-ivory/70">
                        Great jewellery is about more than sparkle. It is the balance of design, finish and comfort—and the confidence of knowing what you are choosing. We pair considered collections with clear details and helpful support at every step.
                    </p>
                    <div class="mt-8 space-y-5">
                        @foreach ($details as $detail)
                            <div class="flex gap-4">
                                <span class="flex h-11 w-11 flex-none items-center justify-center rounded-full border border-ivory/15 bg-ivory/5 text-champagne-light">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path d="{{ $detail['icon'] }}" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                                <div>
                                    <h3 class="font-display text-lg text-ivory">{{ $detail['title'] }}</h3>
                                    <p class="mt-1 text-sm leading-6 text-ivory/60">{{ $detail['text'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="relative lg:col-span-7">
                    <div class="overflow-hidden rounded-[2rem] border border-ivory/10 bg-charcoal-soft">
                        <x-ui.optimized-image
                            :src="asset('images/story5.png')"
                            alt="Jewellery specialist carefully polishing a gold ring"
                            sizes="(min-width: 1024px) 620px, 100vw"
                            class="aspect-[4/3] h-full w-full object-cover"
                        />
                    </div>
                    <div class="absolute -bottom-5 left-5 right-5 rounded-2xl border border-ivory/10 bg-charcoal/90 p-4 backdrop-blur sm:left-auto sm:right-6 sm:w-64">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-champagne-light">Our promise</p>
                        <p class="mt-2 text-sm leading-6 text-ivory/80">Clear information, secure shopping and support that stays with you.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Closing CTA --}}
    <section class="relative overflow-hidden py-20 text-center sm:py-24">
        <div class="absolute inset-0 bg-gradient-to-br from-ivory via-paper to-champagne-light/50" aria-hidden="true"></div>
        <div class="absolute left-1/2 top-0 h-56 w-56 -translate-x-1/2 rounded-full bg-champagne-light/70 blur-3xl" aria-hidden="true"></div>
        <div class="container-luxe relative">
            <span class="eyebrow">Find Your Favourite</span>
            <h2 class="font-display mx-auto mt-3 max-w-2xl text-3xl leading-tight text-charcoal sm:text-4xl">Your next story deserves a little sparkle</h2>
            <p class="mx-auto mt-4 max-w-lg text-sm leading-6 text-muted">Explore jewellery curated for everyday expression, meaningful gifting and moments you will always remember.</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('categories.index') }}" class="btn-primary">Shop by Category</a>
                <a href="{{ route('contact') }}" class="btn-ghost">Talk to Us</a>
            </div>
        </div>
    </section>
</x-layouts.app>

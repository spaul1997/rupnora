@php
    $storySections = [
        ['title' => 'Our Journey', 'art' => 'gold', 'text' => 'Aurelle began in 2009 as a small family workshop dedicated to a simple idea: jewellery should be made to last generations, not seasons. What started with a handful of artisans has grown into a house trusted by thousands of families across the country, while our commitment to hand-finished craftsmanship has never wavered.'],
        ['title' => 'Our Craftsmanship', 'art' => 'diamond', 'text' => 'Every piece passes through the hands of master goldsmiths before it reaches you. From wax carving to final polish, our artisans combine traditional techniques passed down over decades with precise modern quality control — so every setting is secure and every finish is flawless.'],
        ['title' => 'Our Values', 'art' => 'ring', 'text' => 'We believe fine jewellery should be an honest purchase. That means transparent pricing with no hidden making charges, certified purity on every piece, and a team that treats returns and resizing as a normal part of good service, not a hassle.'],
        ['title' => 'Quality & Certification', 'art' => 'bridal', 'text' => 'All gold jewellery is BIS hallmarked, and every diamond above 0.05 carats ships with IGI certification. We conduct independent third-party audits twice a year to ensure our quality promise holds at scale, not just in showroom samples.'],
        ['title' => 'Ethical Sourcing', 'art' => 'necklace', 'text' => 'Our gold is sourced exclusively from refiners certified under the Responsible Jewellery Council, and our diamonds are 100% conflict-free, verified under the Kimberley Process. We publish our sourcing partners on request for full transparency.'],
    ];
@endphp

<x-layouts.app title="Our Story" description="Discover the craftsmanship, values and heritage behind Aurelle's certified jewellery.">

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-beige via-ivory to-champagne-light/40">
        <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 22px 22px; color: var(--color-charcoal);"></div>
        <div class="container-luxe relative py-24 text-center sm:py-32">
            <span class="eyebrow">Est. 2009</span>
            <h1 class="font-display mt-3 text-5xl text-charcoal sm:text-6xl">Our Story</h1>
            <p class="mx-auto mt-5 max-w-xl text-[15px] leading-relaxed text-muted sm:text-base">
                Three generations of craftsmanship, one promise: jewellery made to be treasured, not just worn.
            </p>
        </div>
    </section>

    {{-- Brand introduction --}}
    <section class="section-pad">
        <div class="container-luxe grid grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <x-ui.product-art art="chain" class="aspect-[4/3] rounded-2xl" />
            <div>
                <span class="eyebrow">Who We Are</span>
                <h2 class="font-display mt-3 text-3xl text-charcoal sm:text-4xl">Fine Jewellery, Honestly Made</h2>
                <p class="mt-5 text-[15px] leading-relaxed text-muted">
                    Aurelle is a certified fine jewellery house crafting gold, diamond and silver pieces for life's most meaningful moments. From everyday essentials to complete bridal sets, every design is created in-house by our team of designers and hand-finished by master artisans.
                </p>
                <p class="mt-4 text-[15px] leading-relaxed text-muted">
                    We work directly with certified refiners and diamond graders, cutting out unnecessary middlemen so you get transparent pricing without compromising on quality or provenance.
                </p>
            </div>
        </div>
    </section>

    {{-- Alternating sections --}}
    @foreach ($storySections as $i => $section)
        <section class="section-pad {{ $i % 2 === 1 ? 'bg-ivory-soft' : '' }}">
            <div class="container-luxe grid grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-16">
                <div class="{{ $i % 2 === 1 ? 'lg:order-2' : '' }}">
                    <x-ui.product-art :art="$section['art']" class="aspect-[4/3] rounded-2xl" />
                </div>
                <div class="{{ $i % 2 === 1 ? 'lg:order-1' : '' }}">
                    <span class="eyebrow">{{ sprintf('%02d', $i + 1) }}</span>
                    <h2 class="font-display mt-3 text-3xl text-charcoal sm:text-4xl">{{ $section['title'] }}</h2>
                    <p class="mt-5 text-[15px] leading-relaxed text-muted">{{ $section['text'] }}</p>
                </div>
            </div>
        </section>
    @endforeach

    {{-- Stats --}}
    <section class="bg-charcoal py-16 sm:py-20">
        <div class="container-luxe grid grid-cols-2 gap-8 text-center sm:grid-cols-4">
            @foreach ([['value' => '17+', 'label' => 'Years of Experience'], ['value' => '2,40,000+', 'label' => 'Happy Customers'], ['value' => '3,200+', 'label' => 'Jewellery Designs'], ['value' => '85+', 'label' => 'Cities Served']] as $stat)
                <div>
                    <p class="font-display text-3xl text-champagne-light sm:text-5xl">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-xs uppercase tracking-wide text-ivory/70 sm:text-sm">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Why Choose Us --}}
    <section class="section-pad">
        <div class="container-luxe">
            <div class="mb-10 text-center">
                <span class="eyebrow">Why Choose Us</span>
                <h2 class="font-display mt-2 text-3xl text-charcoal sm:text-4xl">What Sets Us Apart</h2>
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['title' => 'Certified Purity', 'text' => 'BIS hallmarked gold and IGI certified diamonds on every applicable piece.', 'icon' => 'M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['title' => 'Transparent Pricing', 'text' => 'No hidden making charges — the price you see is the price you pay.', 'icon' => 'M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6l8-4z'],
                    ['title' => 'Lifetime Care', 'text' => 'Complimentary cleaning, polishing and one free resizing on every order.', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['title' => 'Ethical Sourcing', 'text' => 'RJC-certified gold and 100% conflict-free, Kimberley Process diamonds.', 'icon' => 'M4 4v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V4'],
                ] as $item)
                    <div class="rounded-2xl border border-line p-6">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-beige text-champagne-dark">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="{{ $item['icon'] }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </div>
                        <h3 class="font-display mt-4 text-lg text-charcoal">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted">{{ $item['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="relative overflow-hidden bg-beige py-20 text-center">
        <h2 class="font-display text-3xl text-charcoal sm:text-4xl">Ready to Find Your Piece?</h2>
        <p class="mx-auto mt-3 max-w-md text-sm text-muted">Explore collections crafted with the same care and honesty behind every Aurelle story.</p>
        <a href="{{ route('collections.index') }}" class="btn-primary mt-8 inline-flex">Discover Our Collections</a>
    </section>

</x-layouts.app>

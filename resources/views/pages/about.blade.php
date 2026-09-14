@php
    $promises = [
        ['title' => 'Certified Jewellery', 'art' => 'diamond', 'text' => 'Every eligible gold design is hallmarked and every diamond-led piece is handled with certification-first transparency, so customers can shop with confidence.'],
        ['title' => 'Designed for Daily Life', 'art' => 'ring', 'text' => 'Rupnora collections balance occasion-ready detail with everyday comfort across rings, earrings, necklaces, bangles, pendants, bracelets, bridal edits and mens styles.'],
        ['title' => 'Care After Purchase', 'art' => 'bracelet', 'text' => 'From sizing questions to order support, our service flow is built around clear communication, easy returns and lifetime care for treasured pieces.'],
    ];

    $process = [
        ['step' => '01', 'title' => 'Curate', 'text' => 'We study what customers are searching for, gifting and saving to their wishlist, then shape collections around real buying moments.'],
        ['step' => '02', 'title' => 'Craft', 'text' => 'Designs are prepared for precise finishing, secure settings and dependable wear across gold, diamond and silver categories.'],
        ['step' => '03', 'title' => 'Verify', 'text' => 'Product details, imagery, pricing and certification notes are checked before a piece reaches the storefront.'],
        ['step' => '04', 'title' => 'Deliver', 'text' => 'Orders move through secure checkout, careful packing, shipping updates and responsive post-purchase support.'],
    ];
@endphp

<x-layouts.app title="Our Story" description="Meet Rupnora, a certified jewellery destination for gold, diamond and silver pieces crafted for everyday sparkle and meaningful occasions.">

    <div class="container-luxe pt-6">
        <x-ui.breadcrumb :trail="[['label' => 'Our Story']]" />
    </div>

    <section class="section-pad-sm">
        <div class="container-luxe grid grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <div>
                <span class="eyebrow">Rupnora Jewellery</span>
                <h1 class="font-display mt-3 text-4xl leading-tight text-charcoal sm:text-5xl lg:text-6xl">Everyday Style, Endless Sparkle</h1>
                <p class="mt-5 max-w-xl text-[15px] leading-relaxed text-muted sm:text-base">
                    Rupnora is built for customers who want jewellery that feels special without making the shopping experience complicated. We bring together certified gold, diamond and silver designs, clear product information and a service team focused on helping every piece feel right.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('collections.index') }}" class="btn-primary">Explore Collections</a>
                    <a href="{{ route('contact') }}" class="btn-ghost">Talk to Us</a>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <x-ui.product-art art="necklace" class="aspect-[4/5] rounded-2xl" />
                <x-ui.product-art art="ring" :tone="2" class="mt-8 aspect-[4/5] rounded-2xl" />
            </div>
        </div>
    </section>

    <section class="section-pad bg-ivory-soft">
        <div class="container-luxe">
            <div class="mx-auto max-w-3xl text-center">
                <span class="eyebrow">Our Purpose</span>
                <h2 class="font-display mt-3 text-3xl text-charcoal sm:text-4xl">Jewellery Customers Can Understand, Trust and Love</h2>
                <p class="mt-4 text-[15px] leading-relaxed text-muted">
                    This project is more than a catalogue. It is a complete jewellery shopping experience with category discovery, curated collections, wishlist, cart, checkout, order support and admin tools that keep the storefront fresh. Our story is the same promise reflected across the product: thoughtful selection, honest information and care at every step.
                </p>
            </div>
        </div>
    </section>

    <section class="section-pad">
        <div class="container-luxe">
            <div class="mb-10 text-center">
                <span class="eyebrow">What We Stand For</span>
                <h2 class="font-display mt-2 text-3xl text-charcoal sm:text-4xl">The Rupnora Promise</h2>
            </div>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                @foreach ($promises as $promise)
                    <div class="rounded-2xl border border-line bg-paper p-5 shadow-card">
                        <x-ui.product-art :art="$promise['art']" class="aspect-[4/3] rounded-xl" />
                        <h3 class="font-display mt-5 text-xl text-charcoal">{{ $promise['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted">{{ $promise['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-charcoal py-16 sm:py-20">
        <div class="container-luxe grid grid-cols-2 gap-8 text-center sm:grid-cols-4">
            @foreach ([['value' => 'Gold', 'label' => 'Hallmarked Designs'], ['value' => 'Diamond', 'label' => 'Certified Brilliance'], ['value' => 'Silver', 'label' => 'Everyday Essentials'], ['value' => 'Bridal', 'label' => 'Occasion Edits']] as $stat)
                <div>
                    <p class="font-display text-3xl text-champagne-light sm:text-4xl">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-xs uppercase tracking-wide text-ivory/70 sm:text-sm">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="section-pad">
        <div class="container-luxe">
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-5 lg:gap-14">
                <div class="lg:col-span-2">
                    <span class="eyebrow">How We Work</span>
                    <h2 class="font-display mt-3 text-3xl text-charcoal sm:text-4xl">From Selection to Your Doorstep</h2>
                    <p class="mt-4 text-[15px] leading-relaxed text-muted">
                        Every Rupnora experience starts with clarity: browse by category, compare collections, save favourites, ask questions and check out securely. Behind the scenes, our team keeps the catalogue, imagery, order flow and support process aligned.
                    </p>
                </div>
                <div class="space-y-4 lg:col-span-3">
                    @foreach ($process as $item)
                        <div class="flex gap-4 rounded-2xl border border-line bg-paper p-5">
                            <span class="font-display text-2xl text-champagne-dark">{{ $item['step'] }}</span>
                            <div>
                                <h3 class="font-display text-xl text-charcoal">{{ $item['title'] }}</h3>
                                <p class="mt-1 text-sm leading-relaxed text-muted">{{ $item['text'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="relative overflow-hidden bg-beige py-20 text-center">
        <div class="container-luxe">
            <span class="eyebrow">Discover Rupnora</span>
            <h2 class="font-display mt-3 text-3xl text-charcoal sm:text-4xl">Find a Piece Made for Your Moment</h2>
            <p class="mx-auto mt-3 max-w-md text-sm text-muted">Explore curated jewellery edits shaped around everyday wear, gifting, celebration and bridal moments.</p>
            <a href="{{ route('categories.index') }}" class="btn-primary mt-8 inline-flex">Shop by Category</a>
        </div>
    </section>

</x-layouts.app>

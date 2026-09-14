@php
    $teams = [
        ['title' => 'Jewellery Curation', 'art' => 'diamond', 'text' => 'Shape category edits, product stories and launch assortments across gold, diamond, silver, bridal and everyday jewellery.'],
        ['title' => 'Customer Experience', 'art' => 'ring', 'text' => 'Help shoppers choose the right piece, understand sizing, track orders and feel supported after purchase.'],
        ['title' => 'Operations', 'art' => 'chain', 'text' => 'Coordinate product data, stock movement, packaging checks, shipping handoffs and admin workflows.'],
        ['title' => 'Digital Growth', 'art' => 'pendant', 'text' => 'Improve search, merchandising, content, conversion and the small moments that make online jewellery shopping feel effortless.'],
    ];

    $openings = [
        ['role' => 'Jewellery Catalogue Executive', 'type' => 'Full Time', 'location' => 'Bengaluru / Hybrid'],
        ['role' => 'Customer Care Associate', 'type' => 'Full Time', 'location' => 'Remote Friendly'],
        ['role' => 'Product Photographer', 'type' => 'Contract', 'location' => 'Bengaluru'],
        ['role' => 'Ecommerce Operations Intern', 'type' => 'Internship', 'location' => 'Hybrid'],
    ];

    $values = [
        'Customer clarity before cleverness',
        'Certification and detail matter',
        'Fast, respectful communication',
        'Craft, catalogue and service working together',
    ];
@endphp

<x-layouts.app title="Careers" description="Build the Rupnora jewellery shopping experience with teams across curation, customer care, operations and digital growth.">

    <div class="container-luxe pt-6">
        <x-ui.breadcrumb :trail="[['label' => 'Careers']]" />
    </div>

    <section class="section-pad-sm">
        <div class="container-luxe grid grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <div>
                <span class="eyebrow">Careers at Rupnora</span>
                <h1 class="font-display mt-3 text-4xl leading-tight text-charcoal sm:text-5xl lg:text-6xl">Build a Jewellery Experience People Trust</h1>
                <p class="mt-5 max-w-xl text-[15px] leading-relaxed text-muted sm:text-base">
                    We are growing a jewellery brand where product detail, service, design and operations move together. If you care about beautiful products, precise information and calm customer support, there is meaningful work to do here.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#openings" class="btn-primary">View Openings</a>
                    <a href="{{ route('contact') }}" class="btn-ghost">Contact Team</a>
                </div>
            </div>
            <x-ui.product-art art="bridal" class="aspect-[4/3] rounded-2xl" />
        </div>
    </section>

    <section class="section-pad bg-ivory-soft">
        <div class="container-luxe">
            <div class="mb-10 text-center">
                <span class="eyebrow">Where You Can Grow</span>
                <h2 class="font-display mt-2 text-3xl text-charcoal sm:text-4xl">Teams at Rupnora</h2>
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($teams as $team)
                    <div class="rounded-2xl border border-line bg-paper p-5 shadow-card">
                        <x-ui.product-art :art="$team['art']" class="aspect-square rounded-xl" />
                        <h3 class="font-display mt-5 text-xl text-charcoal">{{ $team['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted">{{ $team['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section-pad">
        <div class="container-luxe grid grid-cols-1 gap-10 lg:grid-cols-5 lg:gap-14">
            <div class="lg:col-span-2">
                <span class="eyebrow">Our Culture</span>
                <h2 class="font-display mt-3 text-3xl text-charcoal sm:text-4xl">Small Details, High Care</h2>
                <p class="mt-4 text-[15px] leading-relaxed text-muted">
                    Jewellery customers notice details. So do we. Our team works with steady ownership, clear handoffs and respect for the craft behind every item listed on the storefront.
                </p>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:col-span-3">
                @foreach ($values as $value)
                    <div class="flex items-center gap-3 rounded-2xl border border-line bg-paper p-5">
                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-beige text-champagne-dark">
                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </span>
                        <p class="text-sm font-medium leading-relaxed text-charcoal">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="openings" class="section-pad bg-charcoal scroll-mt-24">
        <div class="container-luxe">
            <div class="mb-10 text-center">
                <span class="eyebrow text-champagne-light">Open Roles</span>
                <h2 class="font-display mt-2 text-3xl text-ivory sm:text-4xl">Current Opportunities</h2>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-ivory/65">
                    These are sample role tracks for the current Rupnora workflow. Reach out with your profile and the team you want to contribute to.
                </p>
            </div>
            <div class="mx-auto max-w-4xl divide-y divide-ivory/10 rounded-2xl border border-ivory/15 bg-ivory/5">
                @foreach ($openings as $opening)
                    <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="font-display text-xl text-ivory">{{ $opening['role'] }}</h3>
                            <p class="mt-1 text-sm text-ivory/60">{{ $opening['type'] }} &middot; {{ $opening['location'] }}</p>
                        </div>
                        <a href="{{ route('contact') }}" class="btn-light w-full sm:w-auto">Apply</a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="relative overflow-hidden bg-beige py-20 text-center">
        <div class="container-luxe">
            <span class="eyebrow">Work With Us</span>
            <h2 class="font-display mt-3 text-3xl text-charcoal sm:text-4xl">Bring Care to Every Customer Moment</h2>
            <p class="mx-auto mt-3 max-w-md text-sm text-muted">Send us your profile, portfolio or a short note about where you would like to help Rupnora grow.</p>
            <a href="{{ route('contact') }}" class="btn-primary mt-8 inline-flex">Get in Touch</a>
        </div>
    </section>

</x-layouts.app>

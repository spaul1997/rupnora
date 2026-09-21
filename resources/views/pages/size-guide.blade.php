@php
    $ringSizes = [
        ['size' => '10', 'diameter' => '16.5', 'circumference' => '51.9'],
        ['size' => '12', 'diameter' => '17.3', 'circumference' => '54.4'],
        ['size' => '14', 'diameter' => '18.2', 'circumference' => '57.2'],
        ['size' => '16', 'diameter' => '19.0', 'circumference' => '59.7'],
        ['size' => '18', 'diameter' => '19.8', 'circumference' => '62.1'],
    ];
@endphp

<x-layouts.app
    :title="$title"
    description="Find your ideal Rupnora ring, bracelet and necklace size with simple measuring instructions and an easy ring-size conversion chart."
>
    <div class="container-luxe pt-6">
        <x-ui.breadcrumb :trail="[['label' => 'Size Guide']]" />
    </div>

    <section class="container-luxe pb-16 pt-10 sm:pb-20 sm:pt-14">
        <div class="mx-auto max-w-5xl">
            <div class="max-w-2xl">
                <span class="eyebrow">Find Your Fit</span>
                <h1 class="font-display mt-3 text-4xl leading-tight text-charcoal sm:text-5xl">Jewellery Size Guide</h1>
                <p class="mt-5 text-base leading-7 text-muted">
                    A comfortable fit makes every piece feel effortless. Use these simple at-home methods to choose your ring, bracelet or necklace size.
                </p>
            </div>

            <div class="mt-12 grid gap-5 md:grid-cols-3">
                @foreach ([
                    ['number' => '01', 'title' => 'Choose a piece', 'text' => 'Use a ring or bracelet that already fits well, or measure directly around your finger or wrist.'],
                    ['number' => '02', 'title' => 'Measure in millimetres', 'text' => 'Use a flexible measuring tape or a strip of paper, keeping it comfortably snug rather than tight.'],
                    ['number' => '03', 'title' => 'Match your size', 'text' => 'Compare your measurement with the guide below. If you are between sizes, choose the larger size.'],
                ] as $step)
                    <article class="rounded-2xl border border-line bg-ivory-soft p-6">
                        <span class="text-xs font-semibold tracking-[0.18em] text-champagne-dark">{{ $step['number'] }}</span>
                        <h2 class="font-display mt-3 text-xl text-charcoal">{{ $step['title'] }}</h2>
                        <p class="mt-2 text-sm leading-6 text-muted">{{ $step['text'] }}</p>
                    </article>
                @endforeach
            </div>

            <section class="mt-14 border-t border-line pt-10" aria-labelledby="ring-size-heading">
                <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:gap-14">
                    <div>
                        <span class="eyebrow">Rings</span>
                        <h2 id="ring-size-heading" class="font-display mt-2 text-3xl text-charcoal">Measure your ring size</h2>
                        <div class="mt-5 space-y-4 text-sm leading-6 text-muted sm:text-base">
                            <p><strong class="font-semibold text-charcoal">Using an existing ring:</strong> measure straight across the inside of the ring at its widest point. This is its inner diameter.</p>
                            <p><strong class="font-semibold text-charcoal">Using paper or thread:</strong> wrap it around the base of your finger, mark where the ends meet, then measure the length. This is your finger circumference.</p>
                            <p>Measure near the end of the day, when fingers are usually at their largest. Repeat the measurement twice for accuracy.</p>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-line bg-paper shadow-soft">
                        <table class="w-full text-sm">
                            <caption class="sr-only">Ring sizes with their inner diameter and circumference in millimetres</caption>
                            <thead class="bg-charcoal text-left text-xs uppercase tracking-wider text-ivory">
                                <tr>
                                    <th scope="col" class="px-4 py-3.5 sm:px-6">Ring size</th>
                                    <th scope="col" class="px-4 py-3.5 sm:px-6">Diameter</th>
                                    <th scope="col" class="px-4 py-3.5 sm:px-6">Circumference</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line text-charcoal-soft">
                                @foreach ($ringSizes as $ring)
                                    <tr class="transition-colors hover:bg-ivory-soft">
                                        <td class="px-4 py-4 font-semibold text-charcoal sm:px-6">{{ $ring['size'] }}</td>
                                        <td class="px-4 py-4 sm:px-6">{{ $ring['diameter'] }} mm</td>
                                        <td class="px-4 py-4 sm:px-6">{{ $ring['circumference'] }} mm</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <div class="mt-14 grid gap-6 border-t border-line pt-10 md:grid-cols-2">
                <section class="rounded-2xl bg-beige p-7 sm:p-8" aria-labelledby="bracelet-size-heading">
                    <span class="eyebrow">Bracelets</span>
                    <h2 id="bracelet-size-heading" class="font-display mt-2 text-2xl text-charcoal">Measure your wrist</h2>
                    <p class="mt-4 text-sm leading-6 text-muted">Wrap a flexible tape around your wrist just below the wrist bone. Add <strong class="font-semibold text-charcoal">1 cm</strong> for a close fit, <strong class="font-semibold text-charcoal">1.5 cm</strong> for a comfortable fit or <strong class="font-semibold text-charcoal">2 cm</strong> for a relaxed fit.</p>
                </section>

                <section class="rounded-2xl bg-ivory-soft p-7 sm:p-8" aria-labelledby="necklace-size-heading">
                    <span class="eyebrow">Necklaces</span>
                    <h2 id="necklace-size-heading" class="font-display mt-2 text-2xl text-charcoal">Choose your necklace length</h2>
                    <p class="mt-4 text-sm leading-6 text-muted">Use a string to preview where a necklace will sit, then measure the string. Neck shape, height and pendant size can change how the same length looks, so use the product measurements as your final reference.</p>
                </section>
            </div>

            <div class="mt-12 flex flex-col items-start justify-between gap-5 rounded-2xl border border-champagne/40 bg-champagne-light/30 p-7 sm:flex-row sm:items-center sm:p-8">
                <div>
                    <h2 class="font-display text-2xl text-charcoal">Still unsure about your size?</h2>
                    <p class="mt-2 text-sm leading-6 text-muted">Our team can help you choose the most comfortable fit before you order.</p>
                </div>
                <a href="{{ route('contact') }}" class="btn-primary flex-shrink-0">Contact Us</a>
            </div>
        </div>
    </section>
</x-layouts.app>

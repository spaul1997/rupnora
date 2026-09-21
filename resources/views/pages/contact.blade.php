@php
    $phone = trim((string) $settings->phone);
    $email = trim((string) $settings->support_email);
    $whatsapp = trim((string) $settings->whatsapp);

    $contactMethods = array_values(array_filter([
        [
            'label' => 'Call us',
            'value' => $phone,
            'note' => 'For product and order support',
            'url' => $phone ? 'tel:'.preg_replace('/[^\d+]/', '', $phone) : null,
            'icon' => 'M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3 19.5 19.5 0 01-6-6 19.8 19.8 0 01-3-8.7A2 2 0 014.1 2h3a2 2 0 012 1.7c.1.9.3 1.8.6 2.7a2 2 0 01-.4 2.1L8 9.9a16 16 0 006 6l1.4-1.4a2 2 0 012.1-.4c.9.3 1.8.5 2.7.6a2 2 0 011.8 2.2z',
        ],
        [
            'label' => 'Email us',
            'value' => $email,
            'note' => 'Send your question any time',
            'url' => $email ? 'mailto:'.$email : null,
            'icon' => 'M3 6h18v12H3zM3 6l9 7 9-7',
        ],
        [
            'label' => 'WhatsApp',
            'value' => $whatsapp,
            'note' => 'A quick way to reach our team',
            'url' => $whatsapp ? 'https://wa.me/'.preg_replace('/\D/', '', $whatsapp) : null,
            'icon' => 'M12 2a10 10 0 00-8.6 15.1L2 22l5-1.4A10 10 0 1012 2z',
        ],
    ], fn (array $method) => filled($method['value'])));

    $socialLinks = collect([
        'Instagram' => $settings->instagram,
        'Facebook' => $settings->facebook,
        'LinkedIn' => $settings->linkedin,
        'YouTube' => $settings->youtube,
    ])->filter();

    $subjects = [
        'Product & styling advice',
        'Order support',
        'Shipping & delivery',
        'Returns & refunds',
        'Partnerships & press',
        'Other',
    ];
@endphp

<x-layouts.app
    title="Contact Us"
    description="Contact the Rupnora team for jewellery guidance, order support, shipping, returns and general enquiries."
>
    <div class="container-luxe pt-6">
        <x-ui.breadcrumb :trail="[['label' => 'Contact Us']]" />
    </div>

    {{-- Hero --}}
    <section class="overflow-hidden pb-16 pt-8 sm:pb-20 sm:pt-10 lg:pb-24 lg:pt-12">
        <div class="container-luxe">
            <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-10">
                <div class="relative z-10 lg:col-span-5 lg:pr-4">
                    <span class="eyebrow">Contact Rupnora</span>
                    <h1 class="font-display mt-4 text-4xl leading-[1.08] text-charcoal sm:text-5xl lg:text-6xl">
                        Talk to us.<br>
                        <span class="text-champagne-dark">We’re here to help.</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-[15px] leading-7 text-muted sm:text-base">
                        Choosing jewellery is personal. Whether you need help finding the right piece, have a question about your order or simply want a little guidance, our team is ready to listen.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="#contact-form" class="btn-primary">Send a Message</a>
                        <a href="#faq" class="btn-ghost">Browse FAQs</a>
                    </div>
                    <div class="mt-9 flex items-center gap-4 text-xs font-medium uppercase tracking-[0.16em] text-muted">
                        <span class="h-px w-12 bg-champagne"></span>
                        Thoughtful support, every step
                    </div>
                </div>

                <div class="relative lg:col-span-7">
                    <div class="absolute -right-20 -top-16 h-72 w-72 rounded-full bg-champagne-light/70 blur-3xl" aria-hidden="true"></div>
                    <div class="relative ml-auto max-w-2xl pb-9 sm:pl-10">
                        <div class="overflow-hidden rounded-[2rem] bg-beige shadow-soft">
                            <x-ui.optimized-image
                                :src="asset('images/contact1.png')"
                                alt="Rupnora jewellery specialist helping a customer choose a necklace"
                                sizes="(min-width: 1024px) 620px, 100vw"
                                loading="eager"
                                fetchpriority="high"
                                class="aspect-[4/3] h-full w-full object-cover"
                            />
                        </div>
                        <div class="absolute bottom-0 left-0 hidden rounded-2xl border border-line bg-paper px-5 py-4 shadow-card sm:block">
                            <p class="font-display text-lg text-charcoal">Personal guidance</p>
                            <p class="mt-1 text-xs text-muted">For every question and every occasion.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Contact methods --}}
    @if (count($contactMethods) > 0)
        <section class="border-y border-line bg-ivory-soft py-8 sm:py-10">
            <div class="container-luxe">
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($contactMethods as $method)
                        <a
                            href="{{ $method['url'] }}"
                            @if ($method['label'] === 'WhatsApp') target="_blank" rel="noopener noreferrer" @endif
                            class="group flex items-center gap-4 rounded-2xl border border-line bg-paper p-5 transition hover:-translate-y-0.5 hover:border-champagne/60 hover:shadow-card"
                        >
                            <span class="flex h-12 w-12 flex-none items-center justify-center rounded-full bg-beige text-champagne-dark transition-colors group-hover:bg-champagne-light">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="{{ $method['icon'] }}" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-[10px] font-semibold uppercase tracking-[0.16em] text-champagne-dark">{{ $method['label'] }}</span>
                                <span class="mt-1 block truncate text-sm font-medium text-charcoal">{{ $method['value'] }}</span>
                                <span class="mt-1 block text-xs text-muted">{{ $method['note'] }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Contact form and details --}}
    <section id="contact-form" class="section-pad scroll-mt-24 bg-beige">
        <div class="container-luxe">
            <div class="grid items-start gap-10 lg:grid-cols-12 lg:gap-14">
                <div class="lg:col-span-5">
                    <span class="eyebrow">Let’s Connect</span>
                    <h2 class="font-display mt-3 text-3xl leading-tight text-charcoal sm:text-4xl">Tell us how we can make your experience better</h2>
                    <p class="mt-4 text-[15px] leading-7 text-muted">
                        Share a few details below and your message will go directly to our support team. If your question is about an existing order, include the order number in your message.
                    </p>

                    <div class="relative mt-8 pb-5">
                        <div class="overflow-hidden rounded-[1.75rem] bg-paper shadow-soft">
                            <x-ui.optimized-image
                                :src="asset('images/contact2.png')"
                                alt="Rupnora support specialist assisting a customer by phone"
                                sizes="(min-width: 1024px) 480px, 100vw"
                                class="aspect-[4/3] h-full w-full object-cover"
                            />
                        </div>
                        <div class="absolute -bottom-1 left-5 right-5 rounded-2xl border border-line bg-paper/95 p-4 shadow-card backdrop-blur sm:left-auto sm:right-5 sm:w-64">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-champagne-dark">Response time</p>
                            <p class="mt-2 text-sm leading-6 text-charcoal">We usually respond within one business day.</p>
                        </div>
                    </div>

                    @if (filled($settings->address) || filled($settings->business_hours))
                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                            @if (filled($settings->address))
                                <div class="rounded-2xl border border-line bg-paper p-5">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-beige text-champagne-dark">
                                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                                <path d="M12 21s-7-6.5-7-11.5A7 7 0 0112 2a7 7 0 017 7.5C19 14.5 12 21 12 21z" stroke-linejoin="round" />
                                                <circle cx="12" cy="9.5" r="2.5" />
                                            </svg>
                                        </span>
                                        <h3 class="font-display text-lg text-charcoal">Visit us</h3>
                                    </div>
                                    <p class="mt-3 text-sm leading-6 text-muted">{{ $settings->address }}</p>
                                    @if (filled($settings->google_map_url))
                                        <a href="{{ $settings->google_map_url }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex text-xs font-semibold text-champagne-dark hover:underline">Get directions</a>
                                    @endif
                                </div>
                            @endif

                            @if (filled($settings->business_hours))
                                <div class="rounded-2xl border border-line bg-paper p-5">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-beige text-champagne-dark">
                                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <h3 class="font-display text-lg text-charcoal">Hours</h3>
                                    </div>
                                    <p class="mt-3 text-sm leading-6 text-muted">{{ $settings->business_hours }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($socialLinks->isNotEmpty())
                        <div class="mt-6 flex flex-wrap items-center gap-2">
                            <span class="mr-2 text-xs font-semibold uppercase tracking-[0.16em] text-muted">Follow Rupnora</span>
                            @foreach ($socialLinks as $label => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="rounded-full border border-line bg-paper px-3 py-2 text-xs font-medium text-charcoal transition hover:border-champagne hover:text-champagne-dark">{{ $label }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="lg:col-span-7">
                    <div class="rounded-[1.75rem] border border-line bg-paper p-6 shadow-soft sm:p-8 lg:p-10">
                        <div class="mb-7">
                            <span class="eyebrow">Send a Message</span>
                            <h2 class="font-display mt-2 text-2xl text-charcoal sm:text-3xl">How can we help?</h2>
                            <p class="mt-2 text-sm text-muted">Fields marked with an asterisk are required.</p>
                        </div>

                        @if (session('success'))
                            <div class="mb-6 flex items-start gap-3 rounded-xl border border-success/20 bg-success/10 px-4 py-3 text-sm text-success" role="status">
                                <svg class="mt-0.5 h-4.5 w-4.5 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p>{{ session('success') }}</p>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('contact.store') }}" class="space-y-5">
                            @csrf
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="contact-name" class="label-luxe">Full name <span class="text-error">*</span></label>
                                    <input id="contact-name" type="text" name="name" value="{{ old('name', auth()->user()?->name) }}" required autocomplete="name" class="input-luxe" placeholder="Your full name">
                                    @error('name') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="contact-email" class="label-luxe">Email address <span class="text-error">*</span></label>
                                    <input id="contact-email" type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required autocomplete="email" class="input-luxe" placeholder="you@example.com">
                                    @error('email') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="contact-phone" class="label-luxe">Phone number</label>
                                    <input id="contact-phone" type="tel" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" autocomplete="tel" class="input-luxe" placeholder="+91 98765 43210">
                                    @error('phone') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="contact-subject" class="label-luxe">What can we help with? <span class="text-error">*</span></label>
                                    <select id="contact-subject" name="subject" required class="input-luxe">
                                        <option value="">Select a topic</option>
                                        @foreach ($subjects as $subject)
                                            <option value="{{ $subject }}" @selected(old('subject') === $subject)>{{ $subject }}</option>
                                        @endforeach
                                    </select>
                                    @error('subject') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="contact-message" class="label-luxe">Message <span class="text-error">*</span></label>
                                <textarea id="contact-message" name="message" rows="6" required minlength="20" maxlength="2000" class="input-luxe resize-none" placeholder="Share the details of your question...">{{ old('message') }}</textarea>
                                <div class="mt-1.5 flex items-start justify-between gap-4">
                                    @error('message') <p class="text-xs text-error">{{ $message }}</p> @enderror
                                    <p class="ml-auto text-[10px] text-muted-light">Maximum 2,000 characters</p>
                                </div>
                            </div>

                            <div class="flex flex-col-reverse gap-4 border-t border-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                                <p class="max-w-sm text-[11px] leading-5 text-muted">We’ll use your details only to respond to your request. Read our <a href="{{ route('privacy-policy') }}" class="font-medium text-charcoal underline underline-offset-2">Privacy Policy</a>.</p>
                                <button type="submit" class="btn-primary flex-none">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="section-pad scroll-mt-24">
        <div class="container-luxe">
            <div class="mx-auto mb-10 max-w-2xl text-center">
                <span class="eyebrow">Quick Answers</span>
                <h2 class="font-display mt-3 text-3xl text-charcoal sm:text-4xl">Frequently asked questions</h2>
                <p class="mt-4 text-sm leading-6 text-muted">Find helpful information about our jewellery, delivery, returns and shopping experience.</p>
            </div>

            <div class="mx-auto max-w-3xl overflow-hidden rounded-2xl border border-line bg-paper" x-data="{ open: 0 }">
                @foreach ($faqs as $i => $faq)
                    <div class="border-b border-line last:border-b-0">
                        <button
                            type="button"
                            @click="open = open === {{ $i }} ? null : {{ $i }}"
                            class="flex w-full items-center justify-between gap-5 px-5 py-5 text-left sm:px-6"
                            :aria-expanded="open === {{ $i }}"
                        >
                            <span class="text-sm font-medium text-charcoal sm:text-base">{{ $faq['q'] }}</span>
                            <span class="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-beige text-champagne-dark">
                                <svg class="h-4 w-4 transition-transform" :class="open === {{ $i }} && 'rotate-45'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                                </svg>
                            </span>
                        </button>
                        <div x-cloak x-show="open === {{ $i }}" x-collapse class="px-5 pb-5 text-sm leading-7 text-muted sm:px-6">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.app>

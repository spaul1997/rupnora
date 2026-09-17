<x-layouts.app title="Contact Us" description="Get in touch with the Aurelle jewellery team — visit our store, call, or send us a message.">

    <div class="container-luxe pt-6">
        <x-ui.breadcrumb :trail="[['label' => 'Contact Us']]" />
    </div>

    <div class="container-luxe py-8 text-center sm:py-10">
        <span class="eyebrow">We'd Love to Hear From You</span>
        <h1 class="font-display mt-2 text-4xl text-charcoal sm:text-5xl">Contact Us</h1>
        <p class="mx-auto mt-3 max-w-xl text-sm text-muted sm:text-base">Have a question about an order, sizing, or a custom piece? Our team typically responds within 24 hours.</p>
    </div>

    <div class="container-luxe pb-20">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-5 lg:gap-14">
            {{-- Contact info --}}
            <div class="lg:col-span-2">
                <div class="space-y-5">
                    @foreach ([
                        ['label' => 'Phone', 'value' => $settings->phone, 'icon' => 'M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3 19.5 19.5 0 01-6-6 19.8 19.8 0 01-3-8.7A2 2 0 014.1 2h3a2 2 0 012 1.7c.1.9.3 1.8.6 2.7a2 2 0 01-.4 2.1L8 9.9a16 16 0 006 6l1.4-1.4a2 2 0 012.1-.4c.9.3 1.8.5 2.7.6a2 2 0 011.8 2.2z'],
                        ['label' => 'Email', 'value' => $settings->support_email, 'icon' => 'M3 6h18v12H3zM3 6l9 7 9-7'],
                        ['label' => 'WhatsApp', 'value' => $settings->whatsapp, 'icon' => 'M12 2a10 10 0 00-8.6 15.1L2 22l5-1.4A10 10 0 1012 2z'],
                        ['label' => 'Store Address', 'value' => $settings->address, 'icon' => 'M12 21s-7-6.5-7-11.5A7 7 0 0112 2a7 7 0 017 7.5C19 14.5 12 21 12 21z'],
                        ['label' => 'Business Hours', 'value' => $settings->business_hours, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ] as $info)
                        <div class="flex items-start gap-4 rounded-xl border border-line p-4">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-beige text-champagne-dark">
                                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="{{ $info['icon'] }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-muted">{{ $info['label'] }}</p>
                                <p class="mt-1 text-sm text-charcoal">{{ $info['value'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex items-center gap-3">
                    @foreach (['instagram', 'facebook', 'pinterest'] as $name)
                        <a href="#" aria-label="{{ ucfirst($name) }}" class="flex h-10 w-10 items-center justify-center rounded-full border border-line text-charcoal transition-colors hover:border-champagne-dark hover:text-champagne-dark">
                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="9" /></svg>
                        </a>
                    @endforeach
                </div>

                {{-- Map --}}
                <div class="relative mt-6 flex aspect-[4/3] items-center justify-center overflow-hidden rounded-2xl border border-line bg-ivory-soft">
                    <div class="absolute inset-0 opacity-[0.08]" style="background-image: linear-gradient(currentColor 1px, transparent 1px), linear-gradient(90deg, currentColor 1px, transparent 1px); background-size: 28px 28px; color: var(--color-charcoal);"></div>
                    <div class="relative flex flex-col items-center gap-2 text-center">
                        <svg class="h-8 w-8 text-champagne-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21s-7-6.5-7-11.5A7 7 0 0112 2a7 7 0 017 7.5C19 14.5 12 21 12 21z" stroke-linejoin="round" /><circle cx="12" cy="9.5" r="2.5" /></svg>
                        <p class="text-xs text-muted">42 MG Road, Indiranagar<br>Bengaluru, Karnataka 560038</p>
                    </div>
                </div>
            </div>

            {{-- Contact form --}}
            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-line p-6 sm:p-8">
                    @if (session('success'))
                        <div class="mb-6 flex items-center gap-2 rounded-xl bg-success/10 px-4 py-3 text-sm text-success">
                            <svg class="h-4.5 w-4.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" class="space-y-5">
                        @csrf
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="label-luxe">Name</label>
                                <input type="text" name="name" value="{{ old('name') }}" required class="input-luxe" placeholder="Your full name">
                                @error('name') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label-luxe">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" required class="input-luxe" placeholder="you@example.com">
                                @error('email') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="label-luxe">Phone</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" class="input-luxe" placeholder="+91 98765 43210">
                            </div>
                            <div>
                                <label class="label-luxe">Subject</label>
                                <input type="text" name="subject" value="{{ old('subject') }}" required class="input-luxe" placeholder="How can we help?">
                                @error('subject') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="label-luxe">Message</label>
                            <textarea name="message" rows="5" required class="input-luxe resize-none" placeholder="Tell us more...">{{ old('message') }}</textarea>
                            @error('message') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="btn-primary w-full sm:w-auto">Send Message</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- FAQ --}}
        <div id="faq" class="mx-auto mt-20 max-w-3xl scroll-mt-24">
            <div class="mb-8 text-center">
                <span class="eyebrow">Common Questions</span>
                <h2 class="font-display mt-2 text-3xl text-charcoal">Frequently Asked Questions</h2>
            </div>
            <div class="divide-y divide-line rounded-2xl border border-line" x-data="{ open: 0 }">
                @foreach ($faqs as $i => $faq)
                    <div>
                        <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between px-5 py-4 text-left sm:px-6">
                            <span class="text-sm font-medium text-charcoal sm:text-base">{{ $faq['q'] }}</span>
                            <svg class="h-4 w-4 flex-shrink-0 text-muted transition-transform" :class="open === {{ $i }} && 'rotate-45'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                        </button>
                        <div x-cloak x-show="open === {{ $i }}" x-collapse class="px-5 pb-4 text-sm leading-relaxed text-muted sm:px-6">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</x-layouts.app>

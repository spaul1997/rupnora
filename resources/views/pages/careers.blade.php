@php
    $teams = [
        [
            'title' => 'Jewellery & Curation',
            'text' => 'Shape thoughtful edits, product stories and collections that help every customer find something personal.',
            'icon' => 'M12 3l7 6-7 12L5 9l7-6zm-7 6h14M9 9l3 12 3-12',
        ],
        [
            'title' => 'Brand & Content',
            'text' => 'Bring the world of Rupnora to life through campaigns, photography, styling and clear, useful storytelling.',
            'icon' => 'M4 18l5-1 9-9-4-4-9 9-1 5zm8-12l4 4M4 21h16',
        ],
        [
            'title' => 'Customer Experience',
            'text' => 'Make every conversation feel considered, from product guidance and sizing to delivery and aftercare.',
            'icon' => 'M4 13a8 8 0 0116 0v5a2 2 0 01-2 2h-2v-7h4M4 13h4v7H6a2 2 0 01-2-2v-5z',
        ],
        [
            'title' => 'Ecommerce & Operations',
            'text' => 'Connect catalogue, inventory, fulfilment and digital journeys so the experience works beautifully end to end.',
            'icon' => 'M4 6h16v12H4zM8 3v6M16 3v6M8 14h3M8 17h6',
        ],
    ];

    $principles = [
        [
            'title' => 'Stay close to the customer',
            'text' => 'We begin with what will make choosing, buying and caring for jewellery feel better.',
        ],
        [
            'title' => 'Care about the details',
            'text' => 'From a product description to a packed order, thoughtful details build lasting trust.',
        ],
        [
            'title' => 'Share work and ideas',
            'text' => 'The best outcomes happen when different perspectives meet early and openly.',
        ],
        [
            'title' => 'Keep learning together',
            'text' => 'We stay curious, welcome useful feedback and turn every challenge into a chance to improve.',
        ],
    ];

    $hiringSteps = [
        [
            'number' => '01',
            'title' => 'Start with a conversation',
            'text' => 'We learn about your experience, what energises you and the kind of work you want to do next.',
        ],
        [
            'number' => '02',
            'title' => 'Meet the people you will work with',
            'text' => 'You get a clear view of the role, the team and the problems we are working to solve together.',
        ],
        [
            'number' => '03',
            'title' => 'Decide with clarity',
            'text' => 'We share expectations and next steps directly, so both sides can make a thoughtful decision.',
        ],
    ];
@endphp

<x-layouts.app
    title="Careers"
    description="Explore careers at Rupnora and discover how our teams bring together jewellery, digital craft, thoughtful service and operational care."
>
    <div
        x-data="{
            applicationModal: {{ $errors->careerApplication->any() ? 'true' : 'false' }},
            cvName: '',
        }"
        x-effect="document.body.classList.toggle('overflow-hidden', applicationModal)"
    >
    <div class="container-luxe pt-6">
        <x-ui.breadcrumb :trail="[['label' => 'Careers']]" />
    </div>

    {{-- Hero --}}
    <section class="overflow-hidden pb-16 pt-8 sm:pb-20 sm:pt-10 lg:pb-24 lg:pt-12">
        <div class="container-luxe">
            <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-10">
                <div class="relative z-10 lg:col-span-5 lg:pr-4">
                    <span class="eyebrow">Careers at Rupnora</span>
                    <h1 class="font-display mt-4 text-4xl leading-[1.08] text-charcoal sm:text-5xl lg:text-6xl">
                        Create the moments<br>
                        <span class="text-champagne-dark">behind the sparkle.</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-[15px] leading-7 text-muted sm:text-base">
                        Jewellery is at the heart of what we do, but people make the experience. Join a team bringing together creativity, care and clear thinking to help every Rupnora customer find something meaningful.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="#openings" class="btn-primary">Explore Opportunities</a>
                        <a href="#teams" class="btn-ghost">Meet Our Teams</a>
                    </div>
                    <div class="mt-9 flex items-center gap-4 text-xs font-medium uppercase tracking-[0.16em] text-muted">
                        <span class="h-px w-12 bg-champagne"></span>
                        Bring your ideas. Grow with us.
                    </div>
                </div>

                <div class="relative lg:col-span-7">
                    <div class="absolute -right-20 -top-16 h-72 w-72 rounded-full bg-champagne-light/70 blur-3xl" aria-hidden="true"></div>
                    <div class="relative ml-auto max-w-2xl pb-9 sm:pl-10">
                        <div class="overflow-hidden rounded-[2rem] bg-beige shadow-soft">
                            <x-ui.optimized-image
                                :src="asset('images/careers1.png')"
                                alt="Rupnora team collaborating around a jewellery collection"
                                sizes="(min-width: 1024px) 620px, 100vw"
                                loading="eager"
                                fetchpriority="high"
                                class="aspect-[4/3] h-full w-full object-cover"
                            />
                        </div>
                        <div class="absolute bottom-0 left-0 hidden rounded-2xl border border-line bg-paper px-5 py-4 shadow-card sm:block">
                            <p class="font-display text-lg text-charcoal">One team, many crafts</p>
                            <p class="mt-1 text-xs text-muted">Ideas grow stronger together.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Introduction --}}
    <section class="border-y border-line bg-ivory-soft py-16 sm:py-20">
        <div class="container-luxe">
            <div class="mx-auto max-w-3xl text-center">
                <span class="eyebrow">Work With Purpose</span>
                <h2 class="font-display mt-3 text-3xl leading-tight text-charcoal sm:text-4xl">Help make jewellery feel more personal, joyful and easy to discover.</h2>
                <p class="mx-auto mt-5 max-w-2xl text-[15px] leading-7 text-muted">
                    At Rupnora, creative thinking and everyday execution belong together. A considered collection needs a clear story. A beautiful website needs dependable operations. And every order needs people who care about what happens next.
                </p>
            </div>
        </div>
    </section>

    {{-- Teams --}}
    <section id="teams" class="section-pad scroll-mt-24">
        <div class="container-luxe">
            <div class="mb-10 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div class="max-w-2xl">
                    <span class="eyebrow">Find Your Place</span>
                    <h2 class="font-display mt-3 text-3xl text-charcoal sm:text-4xl">Many disciplines, one customer experience</h2>
                </div>
                <p class="max-w-sm text-sm leading-6 text-muted">Different skills come together here, connected by curiosity, ownership and care.</p>
            </div>

            <div class="grid gap-px overflow-hidden rounded-2xl border border-line bg-line sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($teams as $team)
                    <article class="bg-paper p-6 sm:p-7">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-beige text-champagne-dark">
                            <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="{{ $team['icon'] }}" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <h3 class="font-display mt-6 text-xl text-charcoal">{{ $team['title'] }}</h3>
                        <p class="mt-3 text-sm leading-6 text-muted">{{ $team['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Craft and collaboration --}}
    <section class="section-pad overflow-hidden bg-beige">
        <div class="container-luxe">
            <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
                <div class="relative order-2 lg:order-1">
                    <div class="absolute -bottom-10 -left-10 h-56 w-56 rounded-full bg-champagne-light/70 blur-3xl" aria-hidden="true"></div>
                    <div class="relative grid grid-cols-5 items-end gap-4">
                        <div class="col-span-3 overflow-hidden rounded-[1.5rem] bg-paper shadow-soft">
                            <x-ui.optimized-image
                                :src="asset('images/careers2.png')"
                                alt="Rupnora colleagues reviewing jewellery sketches and a finished piece"
                                sizes="(min-width: 1024px) 330px, 60vw"
                                class="aspect-[4/5] h-full w-full object-cover"
                            />
                        </div>
                        <div class="col-span-2 mb-8 overflow-hidden rounded-[1.25rem] bg-paper shadow-card">
                            <x-ui.optimized-image
                                :src="asset('images/careers3.png')"
                                alt="Creative team reviewing jewellery photography for the online store"
                                sizes="(min-width: 1024px) 220px, 40vw"
                                class="aspect-[3/4] h-full w-full object-cover object-center"
                            />
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <span class="eyebrow">Make, Learn, Improve</span>
                    <h2 class="font-display mt-3 text-3xl leading-tight text-charcoal sm:text-4xl">Work that moves between ideas and impact</h2>
                    <p class="mt-5 text-[15px] leading-7 text-muted">
                        Our work crosses disciplines. Curators collaborate with storytellers. Customer insights shape digital journeys. Operations teams turn a promise on screen into an order delivered with care.
                    </p>
                    <p class="mt-4 text-[15px] leading-7 text-muted">
                        That means you will learn beyond your job title, see how your decisions affect the whole experience and have room to make good ideas better.
                    </p>
                    <div class="mt-7 border-l-2 border-champagne pl-5">
                        <p class="font-display text-xl italic leading-relaxed text-charcoal">“The best work happens when craft, context and care meet.”</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Culture --}}
    <section class="overflow-hidden bg-charcoal py-16 sm:py-20 lg:py-24">
        <div class="container-luxe">
            <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-14">
                <div class="lg:col-span-5">
                    <span class="eyebrow text-champagne-light">How We Work</span>
                    <h2 class="font-display mt-3 text-3xl leading-tight text-ivory sm:text-4xl">Thoughtful people. Shared standards. Better outcomes.</h2>
                    <p class="mt-5 text-[15px] leading-7 text-ivory/70">
                        We value the people who ask good questions, follow through and make space for others to contribute. These principles shape how we work across every team.
                    </p>
                    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                        @foreach ($principles as $principle)
                            <div class="flex gap-4 rounded-xl border border-ivory/10 bg-ivory/5 p-4">
                                <span class="mt-0.5 flex h-7 w-7 flex-none items-center justify-center rounded-full bg-champagne-light/10 text-champagne-light">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                                <div>
                                    <h3 class="font-display text-base text-ivory">{{ $principle['title'] }}</h3>
                                    <p class="mt-1 text-xs leading-5 text-ivory/60">{{ $principle['text'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="relative lg:col-span-7">
                    <div class="overflow-hidden rounded-[2rem] border border-ivory/10 bg-charcoal-soft">
                        <x-ui.optimized-image
                            :src="asset('images/careers4.png')"
                            alt="Rupnora team discussing a jewellery collection together"
                            sizes="(min-width: 1024px) 620px, 100vw"
                            class="aspect-[4/3] h-full w-full object-cover"
                        />
                    </div>
                    <div class="absolute -bottom-5 left-5 right-5 rounded-2xl border border-ivory/10 bg-charcoal/90 p-4 backdrop-blur sm:left-auto sm:right-6 sm:w-64">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-champagne-light">Grow together</p>
                        <p class="mt-2 text-sm leading-6 text-ivory/80">Bring your point of view and stay open to someone else’s.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Hiring process --}}
    <section class="section-pad">
        <div class="container-luxe">
            <div class="mx-auto mb-10 max-w-2xl text-center">
                <span class="eyebrow">What to Expect</span>
                <h2 class="font-display mt-3 text-3xl text-charcoal sm:text-4xl">A human, straightforward hiring process</h2>
                <p class="mt-4 text-sm leading-6 text-muted">When we are hiring, we want you to have the context you need and the space to show how you think.</p>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                @foreach ($hiringSteps as $step)
                    <article class="relative rounded-2xl border border-line bg-paper p-6 shadow-card sm:p-7">
                        <span class="font-display text-sm text-champagne-dark">{{ $step['number'] }}</span>
                        <h3 class="font-display mt-8 text-xl text-charcoal">{{ $step['title'] }}</h3>
                        <p class="mt-3 text-sm leading-6 text-muted">{{ $step['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Open roles --}}
    <section id="openings" class="scroll-mt-24 bg-ivory-soft py-16 sm:py-20">
        <div class="container-luxe">
            @if (session('career_application_success'))
                <div class="mx-auto mb-6 flex max-w-4xl items-start gap-3 rounded-2xl border border-success/20 bg-success/10 px-5 py-4 text-sm text-success" role="status">
                    <svg class="mt-0.5 h-5 w-5 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <p>{{ session('career_application_success') }}</p>
                </div>
            @endif

            <div class="mx-auto max-w-4xl overflow-hidden rounded-[2rem] border border-line bg-paper shadow-soft">
                <div class="grid lg:grid-cols-5">
                    <div class="p-7 sm:p-10 lg:col-span-3">
                        <span class="eyebrow">Open Roles</span>
                        <h2 class="font-display mt-3 text-3xl leading-tight text-charcoal sm:text-4xl">We are building our team thoughtfully.</h2>
                        <p class="mt-4 max-w-xl text-[15px] leading-7 text-muted">
                            There are no published vacancies at the moment. If Rupnora feels like a place where you could do your best work, send us a short introduction and tell us where you would like to contribute.
                        </p>
                        <button
                            type="button"
                            @click="applicationModal = true; $nextTick(() => $refs.applicationName?.focus())"
                            class="btn-primary mt-7"
                        >
                            Introduce Yourself
                        </button>
                    </div>
                    <div class="flex flex-col justify-center bg-beige p-7 sm:p-10 lg:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-champagne-dark">Areas of interest</p>
                        <div class="mt-5 flex flex-wrap gap-2">
                            @foreach (['Jewellery', 'Brand', 'Content', 'Customer Care', 'Ecommerce', 'Operations'] as $area)
                                <span class="rounded-full border border-line bg-paper px-3 py-2 text-xs font-medium text-charcoal">{{ $area }}</span>
                            @endforeach
                        </div>
                        <p class="mt-6 text-xs leading-5 text-muted">When a role becomes available, the full position details will appear on this page.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Career application modal --}}
    <div
        x-cloak
        x-show="applicationModal"
        x-transition.opacity
        class="fixed inset-0 z-[120] flex items-end justify-center overflow-y-auto bg-charcoal/75 p-0 backdrop-blur-sm sm:items-center sm:p-6"
        @click.self="applicationModal = false"
        @keydown.escape.window="applicationModal = false"
    >
        <section
            role="dialog"
            aria-modal="true"
            aria-labelledby="career-application-title"
            class="relative max-h-[94vh] w-full max-w-3xl overflow-y-auto rounded-t-[1.75rem] bg-paper shadow-2xl sm:rounded-[1.75rem]"
        >
            <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-line bg-paper/95 px-5 py-5 backdrop-blur sm:px-8">
                <div>
                    <span class="eyebrow">Join Rupnora</span>
                    <h2 id="career-application-title" class="font-display mt-1 text-2xl text-charcoal sm:text-3xl">Introduce yourself</h2>
                    <p class="mt-1 text-xs text-muted sm:text-sm">Tell us where you would like to contribute and attach your latest CV.</p>
                </div>
                <button
                    type="button"
                    @click="applicationModal = false"
                    aria-label="Close application form"
                    class="icon-btn flex-none border border-line"
                >
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <form
                method="POST"
                action="{{ route('career-applications.store') }}"
                enctype="multipart/form-data"
                class="space-y-6 p-5 sm:p-8"
            >
                @csrf

                <div>
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-beige font-display text-xs text-champagne-dark">01</span>
                        <h3 class="font-display text-lg text-charcoal">Your details</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="career-full-name" class="label-luxe">Full name <span class="text-error">*</span></label>
                            <input x-ref="applicationName" id="career-full-name" type="text" name="full_name" value="{{ old('full_name') }}" required autocomplete="name" class="input-luxe" placeholder="Your full name">
                            @error('full_name', 'careerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="career-email" class="label-luxe">Email address <span class="text-error">*</span></label>
                            <input id="career-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="input-luxe" placeholder="you@example.com">
                            @error('email', 'careerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="career-phone" class="label-luxe">Phone number <span class="text-error">*</span></label>
                            <input id="career-phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" class="input-luxe" placeholder="+91 98765 43210">
                            @error('phone', 'careerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="career-location" class="label-luxe">Current location <span class="text-error">*</span></label>
                            <input id="career-location" type="text" name="location" value="{{ old('location') }}" required autocomplete="address-level2" class="input-luxe" placeholder="City, State">
                            @error('location', 'careerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="border-t border-line pt-6">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-beige font-display text-xs text-champagne-dark">02</span>
                        <h3 class="font-display text-lg text-charcoal">Professional profile</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="career-area" class="label-luxe">Area of interest <span class="text-error">*</span></label>
                            <select id="career-area" name="area_of_interest" required class="input-luxe">
                                <option value="">Select an area</option>
                                @foreach (\App\Models\CareerApplication::AREAS as $value => $label)
                                    <option value="{{ $value }}" @selected(old('area_of_interest') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('area_of_interest', 'careerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="career-role" class="label-luxe">Current role</label>
                            <input id="career-role" type="text" name="current_role" value="{{ old('current_role') }}" class="input-luxe" placeholder="e.g. Content Designer">
                            @error('current_role', 'careerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="career-experience" class="label-luxe">Years of experience</label>
                            <input id="career-experience" type="number" name="experience_years" value="{{ old('experience_years') }}" min="0" max="50" inputmode="numeric" class="input-luxe" placeholder="e.g. 3">
                            @error('experience_years', 'careerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="career-linkedin" class="label-luxe">LinkedIn profile</label>
                            <input id="career-linkedin" type="url" name="linkedin_url" value="{{ old('linkedin_url') }}" class="input-luxe" placeholder="https://linkedin.com/in/your-name">
                            @error('linkedin_url', 'careerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="career-portfolio" class="label-luxe">Portfolio or website</label>
                            <input id="career-portfolio" type="url" name="portfolio_url" value="{{ old('portfolio_url') }}" class="input-luxe" placeholder="https://yourportfolio.com">
                            @error('portfolio_url', 'careerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="border-t border-line pt-6">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-beige font-display text-xs text-champagne-dark">03</span>
                        <h3 class="font-display text-lg text-charcoal">Your introduction &amp; CV</h3>
                    </div>
                    <div class="space-y-5">
                        <div>
                            <label for="career-message" class="label-luxe">Tell us about yourself <span class="text-error">*</span></label>
                            <textarea id="career-message" name="message" rows="4" required maxlength="3000" class="input-luxe resize-none" placeholder="What would you like to work on at Rupnora?">{{ old('message') }}</textarea>
                            @error('message', 'careerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="career-cv" class="label-luxe">Upload CV <span class="text-error">*</span></label>
                            <label for="career-cv" class="mt-1 flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-champagne/60 bg-ivory-soft px-5 py-7 text-center transition hover:border-champagne-dark hover:bg-champagne-light/20">
                                <svg class="h-8 w-8 text-champagne-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="M12 16V4m0 0L7 9m5-5l5 5M5 14v5h14v-5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span class="mt-3 text-sm font-medium text-charcoal" x-text="cvName || 'Choose your CV'"></span>
                                <span class="mt-1 text-xs text-muted">PDF, DOC or DOCX · Maximum 5 MB</span>
                            </label>
                            <input
                                id="career-cv"
                                type="file"
                                name="cv"
                                required
                                accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                class="sr-only"
                                @change="cvName = $event.target.files[0]?.name || ''"
                            >
                            @error('cv', 'careerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="max-w-md text-[11px] leading-5 text-muted">
                        By submitting, you agree that Rupnora may use these details to review and respond to your application. See our <a href="{{ route('privacy-policy') }}" class="font-medium text-charcoal underline underline-offset-2">Privacy Policy</a>.
                    </p>
                    <button type="submit" class="btn-primary flex-none">Submit Application</button>
                </div>
            </form>
        </section>
    </div>
    </div>
</x-layouts.app>

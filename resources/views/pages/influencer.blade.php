<x-layouts.app title="Influencer Program" description="Apply to join the Rupnora Influencer Program and create timeless jewellery stories with us.">
    <section class="relative isolate overflow-hidden bg-charcoal py-16 sm:py-24 lg:py-28">
        <div class="absolute inset-0 -z-10 opacity-[0.08]" style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 24px 24px; color: var(--color-ivory);"></div>
        <div class="absolute -left-24 top-10 -z-10 h-72 w-72 rounded-full bg-champagne-dark/20 blur-3xl"></div>
        <div class="absolute -right-20 bottom-0 -z-10 h-80 w-80 rounded-full bg-champagne-light/10 blur-3xl"></div>

        <div class="container-luxe grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <span class="eyebrow text-champagne-light">Rupnora Partner Program</span>
                <h1 class="font-display mt-4 text-4xl leading-tight text-ivory sm:text-5xl lg:text-6xl">Create beautiful stories with Rupnora.</h1>
                <p class="mt-5 max-w-xl text-sm leading-7 text-ivory/70 sm:text-base">
                    We partner with creators who share our love for thoughtful style and timeless jewellery. Bring your point of view, inspire your audience, and earn through every successful collaboration.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#apply" class="btn-light">Apply Now</a>
                    <a href="#benefits" class="inline-flex items-center justify-center rounded-full border border-ivory/30 px-7 py-3.5 text-[13px] font-semibold uppercase tracking-[0.14em] text-ivory transition-colors hover:bg-ivory/10">View Benefits</a>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <x-ui.optimized-image :src="asset('images/inf1.png')" alt="Rupnora creator wearing jewellery" sizes="(min-width: 1024px) 25vw, 50vw" class="aspect-[4/5] w-full rounded-2xl object-cover" />
                <x-ui.optimized-image :src="asset('images/inf2.png')" alt="Rupnora jewellery styled by a creator" sizes="(min-width: 1024px) 25vw, 50vw" class="mt-8 aspect-[4/5] w-full rounded-2xl object-cover" />
            </div>
        </div>
    </section>

    <section id="benefits" class="scroll-mt-24 bg-ivory py-16 sm:py-20">
        <div class="container-luxe">
            <div class="mx-auto max-w-2xl text-center">
                <span class="eyebrow">Why Partner With Us</span>
                <h2 class="font-display mt-3 text-3xl text-charcoal sm:text-4xl">A partnership designed for creators</h2>
                <p class="mt-4 text-sm leading-7 text-muted">Approved partners receive support, access, and earning opportunities built around authentic content.</p>
            </div>

            <div class="mt-10 grid gap-4 md:grid-cols-3">
                @foreach ([
                    ['title' => 'Early Access', 'text' => 'Discover selected launches and new collections before they reach the wider audience.', 'icon' => 'M12 3v18M3 12h18'],
                    ['title' => 'Creative Collaborations', 'text' => 'Receive campaign briefs, styling opportunities, and selected pieces for original content.', 'icon' => 'M4 19V8l8-5 8 5v11H4zm5-7h6'],
                    ['title' => 'Earn Commission', 'text' => 'Receive a personal coupon code and earn commission on eligible sales you generate.', 'icon' => 'M12 2v20m5-16H9.5a3.5 3.5 0 000 7H14a3.5 3.5 0 010 7H6'],
                ] as $benefit)
                    <article class="rounded-2xl border border-line bg-paper p-6 shadow-card sm:p-7">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-champagne-light text-champagne-dark">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="{{ $benefit['icon'] }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </span>
                        <h3 class="font-display mt-5 text-xl text-charcoal">{{ $benefit['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-muted">{{ $benefit['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="apply" class="scroll-mt-20 bg-ivory-soft py-16 sm:py-20">
        <div class="container-luxe">
            @if (session('influencer_application_success'))
                <div class="mx-auto mb-6 flex max-w-4xl items-start gap-3 rounded-2xl border border-success/20 bg-success/10 px-5 py-4 text-sm text-success" role="status">
                    <svg class="mt-0.5 h-5 w-5 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    <p>{{ session('influencer_application_success') }}</p>
                </div>
            @endif

            <div class="mx-auto max-w-4xl overflow-hidden rounded-[2rem] border border-line bg-paper shadow-soft">
                <div class="border-b border-line px-6 py-7 sm:px-10 sm:py-9">
                    <span class="eyebrow">Creator Application</span>
                    <h2 class="font-display mt-3 text-3xl text-charcoal sm:text-4xl">Tell us about your community</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-muted">Share your main social profile and the kind of content you create. Our partnerships team will review your application and contact you if there is a suitable opportunity.</p>
                </div>

                <form method="POST" action="{{ route('influencer.apply') }}" class="space-y-7 p-6 sm:p-10">
                    @csrf

                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-beige font-display text-xs text-champagne-dark">01</span>
                            <h3 class="font-display text-lg text-charcoal">Your details</h3>
                        </div>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="influencer-full-name" class="label-luxe">Full name <span class="text-error">*</span></label>
                                <input id="influencer-full-name" type="text" name="full_name" value="{{ old('full_name') }}" required autocomplete="name" class="input-luxe" placeholder="Your full name">
                                @error('full_name', 'influencerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="influencer-email" class="label-luxe">Email address <span class="text-error">*</span></label>
                                <input id="influencer-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="input-luxe" placeholder="you@example.com">
                                @error('email', 'influencerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="influencer-phone" class="label-luxe">Phone number <span class="text-error">*</span></label>
                                <input id="influencer-phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" class="input-luxe" placeholder="+91 98765 43210">
                                @error('phone', 'influencerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="influencer-location" class="label-luxe">Location <span class="text-error">*</span></label>
                                <input id="influencer-location" type="text" name="location" value="{{ old('location') }}" required autocomplete="address-level2" class="input-luxe" placeholder="City, State">
                                @error('location', 'influencerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-line pt-7">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-beige font-display text-xs text-champagne-dark">02</span>
                            <h3 class="font-display text-lg text-charcoal">Creator profile</h3>
                        </div>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="influencer-platform" class="label-luxe">Primary platform <span class="text-error">*</span></label>
                                <select id="influencer-platform" name="primary_platform" required class="input-luxe">
                                    <option value="">Select a platform</option>
                                    @foreach (\App\Models\Influencer::PLATFORMS as $value => $label)
                                        <option value="{{ $value }}" @selected(old('primary_platform') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('primary_platform', 'influencerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="influencer-handle" class="label-luxe">Social handle <span class="text-error">*</span></label>
                                <input id="influencer-handle" type="text" name="social_handle" value="{{ old('social_handle') }}" required class="input-luxe" placeholder="@yourhandle">
                                @error('social_handle', 'influencerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="influencer-profile-url" class="label-luxe">Profile URL</label>
                                <input id="influencer-profile-url" type="url" name="profile_url" value="{{ old('profile_url') }}" class="input-luxe" placeholder="https://instagram.com/yourhandle">
                                @error('profile_url', 'influencerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="influencer-followers" class="label-luxe">Audience size <span class="text-error">*</span></label>
                                <input id="influencer-followers" type="number" name="followers_count" value="{{ old('followers_count') }}" min="0" max="2000000000" required inputmode="numeric" class="input-luxe" placeholder="e.g. 25000">
                                @error('followers_count', 'influencerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="influencer-niche" class="label-luxe">Content niche <span class="text-error">*</span></label>
                                <input id="influencer-niche" type="text" name="content_niche" value="{{ old('content_niche') }}" required class="input-luxe" placeholder="Jewellery, fashion, lifestyle">
                                @error('content_niche', 'influencerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="influencer-portfolio" class="label-luxe">Media kit or portfolio</label>
                                <input id="influencer-portfolio" type="url" name="portfolio_url" value="{{ old('portfolio_url') }}" class="input-luxe" placeholder="https://yourportfolio.com">
                                @error('portfolio_url', 'influencerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-line pt-7">
                        <label for="influencer-message" class="label-luxe">Why would you like to partner with Rupnora? <span class="text-error">*</span></label>
                        <textarea id="influencer-message" name="message" rows="5" required minlength="20" maxlength="3000" class="input-luxe resize-none" placeholder="Tell us about your content, audience and collaboration ideas.">{{ old('message') }}</textarea>
                        @error('message', 'influencerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="border-t border-line pt-7">
                        <label class="flex items-start gap-3 text-xs leading-5 text-muted">
                            <input type="checkbox" name="terms" value="1" required @checked(old('terms')) class="mt-0.5 h-4 w-4 flex-none rounded border-line text-champagne-dark focus:ring-champagne-dark/40">
                            <span>I confirm these details are accurate and agree that Rupnora may use them to review and respond to my application. See our <a href="{{ route('privacy-policy') }}" class="font-medium text-charcoal underline underline-offset-2">Privacy Policy</a>.</span>
                        </label>
                        @error('terms', 'influencerApplication') <p class="mt-1.5 text-xs text-error">{{ $message }}</p> @enderror

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="btn-primary">Submit Application</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</x-layouts.app>

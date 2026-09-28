@extends('admin.layouts.app')

@section('title', 'New Email Campaign')

@php
    $presets = [
        'new-offer-launch' => [
            'name' => 'New Offer Launch - '.now()->format('d M Y'),
            'subject' => 'A special Rupnora offer, created for you',
            'preheader' => 'Discover a limited Rupnora offer before it ends.',
            'eyebrow' => 'Exclusive Offer',
            'headline' => 'A little more sparkle, for less',
            'body' => "Celebrate the pieces you love with a special Rupnora offer. Thoughtfully crafted jewellery is waiting to become part of your story.\n\nThis invitation is available for a limited time.",
            'highlight' => 'Use code RUPNORA10',
            'ctaLabel' => 'Shop the Offer',
            'ctaUrl' => route('collections.index'),
        ],
        'new-design-launch' => [
            'name' => 'New Design Launch - '.now()->format('d M Y'),
            'subject' => 'Introducing our newest Rupnora design',
            'preheader' => 'A new expression of timeless jewellery has arrived.',
            'eyebrow' => 'Just Launched',
            'headline' => 'Meet the newest expression of Rupnora',
            'body' => "Designed with modern elegance and finished with the care that defines Rupnora, our newest creation is ready to be discovered.\n\nExplore the details, craftsmanship and story behind the design.",
            'highlight' => 'A new chapter in timeless design',
            'ctaLabel' => 'Discover the Design',
            'ctaUrl' => route('new-arrivals'),
        ],
        'influencer-proposal' => [
            'name' => 'Influencer Collaboration Proposal - '.now()->format('d M Y'),
            'subject' => 'A collaboration proposal from Rupnora',
            'preheader' => 'Let us create a beautiful jewellery story together.',
            'eyebrow' => 'Brand Collaboration',
            'headline' => 'Let us create something memorable together',
            'body' => "We admire the way you connect with your community and would love to explore a collaboration with Rupnora. Our vision is to create authentic content that pairs your point of view with our timeless jewellery.\n\nReply to this invitation or use the link below to begin the conversation.",
            'highlight' => 'Creative content, product styling and commission opportunities',
            'ctaLabel' => 'Explore Rupnora',
            'ctaUrl' => route('influencer'),
        ],
        'influencer-agreement' => [
            'name' => 'Influencer Agreement - '.now()->format('d M Y'),
            'subject' => 'Your Rupnora influencer partnership agreement',
            'preheader' => 'Please review the details of your Rupnora partnership.',
            'eyebrow' => 'Partnership Agreement',
            'headline' => 'Your Rupnora partnership details',
            'body' => "We are delighted to move forward with our collaboration. This message outlines the agreed campaign scope, content expectations, timelines, usage rights and compensation.\n\nPlease review the complete terms carefully and contact our partnerships team with any questions before confirming.",
            'highlight' => 'Rupnora Influencer Partnership Agreement',
            'ctaLabel' => 'Contact Partnerships Team',
            'ctaUrl' => route('contact'),
        ],
    ];
    $initialTemplate = old('template_type', 'new-offer-launch');
    $initial = $presets[$initialTemplate] ?? $presets['new-offer-launch'];
@endphp

@section('content')
    <x-admin.page-header title="New Email Campaign" :breadcrumb="[['label' => 'Email Marketing', 'url' => route('admin.email-campaigns.index')], ['label' => 'New Campaign']]" />

    <form
        method="POST"
        action="{{ route('admin.email-campaigns.store') }}"
        enctype="multipart/form-data"
        x-data='{
            template: @json($initialTemplate),
            presets: @json($presets),
            name: @json(old('name', $initial['name'])),
            subject: @json(old('subject', $initial['subject'])),
            preheader: @json(old('preheader', $initial['preheader'])),
            eyebrow: @json(old('eyebrow', $initial['eyebrow'])),
            headline: @json(old('headline', $initial['headline'])),
            body: @json(old('body', $initial['body'])),
            highlight: @json(old('highlight_text', $initial['highlight'])),
            ctaLabel: @json(old('cta_label', $initial['ctaLabel'])),
            ctaUrl: @json(old('cta_url', $initial['ctaUrl'])),
            selectedCustomers: @json(array_map('strval', old('customer_ids', []))),
            selectedInfluencers: @json(array_map('strval', old('influencer_ids', []))),
            allCustomers: @json($customers->pluck('id')->map(fn ($id) => (string) $id)->values()),
            allInfluencers: @json($influencers->pluck('id')->map(fn ($id) => (string) $id)->values()),
            chooseTemplate(key) {
                this.template = key;
                const preset = this.presets[key];
                this.name = preset.name;
                this.subject = preset.subject;
                this.preheader = preset.preheader;
                this.eyebrow = preset.eyebrow;
                this.headline = preset.headline;
                this.body = preset.body;
                this.highlight = preset.highlight;
                this.ctaLabel = preset.ctaLabel;
                this.ctaUrl = preset.ctaUrl;
            }
        }'
        class="space-y-6"
    >
        @csrf

        @error('recipients')
            <div class="rounded-xl bg-error/10 px-4 py-3 text-sm text-error">{{ $message }}</div>
        @enderror

        <div class="admin-card p-6">
            <div class="mb-5">
                <h2 class="text-sm font-semibold text-gray-900">1. Choose a branded template</h2>
                <p class="mt-1 text-xs text-gray-400">Selecting a template fills the campaign with brand-ready copy that you can edit.</p>
            </div>
            <input type="hidden" name="template_type" x-model="template">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($templates as $value => $label)
                    <button type="button" @click="chooseTemplate('{{ $value }}')" :class="template === '{{ $value }}' ? 'border-champagne-dark bg-champagne-light/30 ring-1 ring-champagne-dark' : 'border-gray-200 bg-white hover:border-champagne'" class="rounded-xl border p-4 text-left transition">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-charcoal text-ivory">
                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 6l9 6 9-6M4 5h16a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V6a1 1 0 011-1z" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </span>
                        <span class="mt-3 block text-sm font-semibold text-gray-900">{{ $label }}</span>
                    </button>
                @endforeach
            </div>
            @error('template_type') <p class="mt-2 text-xs text-error">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="admin-card space-y-5 p-6">
                    <h2 class="text-sm font-semibold text-gray-900">2. Campaign content</h2>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="admin-label">Internal Campaign Name <span class="text-error">*</span></label>
                            <input id="name" name="name" type="text" x-model="name" required class="admin-input">
                            @error('name') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="subject" class="admin-label">Email Subject <span class="text-error">*</span></label>
                            <input id="subject" name="subject" type="text" x-model="subject" required class="admin-input">
                            @error('subject') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label for="preheader" class="admin-label">Preview Text</label>
                        <input id="preheader" name="preheader" type="text" x-model="preheader" class="admin-input">
                        <p class="mt-1 text-xs text-gray-400">Shown beside the subject in many email inboxes.</p>
                        @error('preheader') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="eyebrow" class="admin-label">Eyebrow</label>
                            <input id="eyebrow" name="eyebrow" type="text" x-model="eyebrow" class="admin-input">
                            @error('eyebrow') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="highlight_text" class="admin-label">Highlight / Offer Code</label>
                            <input id="highlight_text" name="highlight_text" type="text" x-model="highlight" class="admin-input">
                            @error('highlight_text') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label for="headline" class="admin-label">Headline <span class="text-error">*</span></label>
                        <input id="headline" name="headline" type="text" x-model="headline" required class="admin-input">
                        @error('headline') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="body" class="admin-label">Message <span class="text-error">*</span></label>
                        <textarea id="body" name="body" rows="9" x-model="body" required class="admin-textarea resize-y"></textarea>
                        @error('body') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="cta_label" class="admin-label">Button Label</label>
                            <input id="cta_label" name="cta_label" type="text" x-model="ctaLabel" class="admin-input">
                            @error('cta_label') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="cta_url" class="admin-label">Button URL</label>
                            <input id="cta_url" name="cta_url" type="url" x-model="ctaUrl" class="admin-input">
                            @error('cta_url') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label for="image" class="admin-label">Campaign Image</label>
                        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp,image/avif" class="admin-input">
                        <p class="mt-1 text-xs text-gray-400">Optional wide JPG, PNG, WebP or AVIF image. Maximum 5 MB.</p>
                        @error('image') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="admin-card p-6">
                    <div class="mb-5">
                        <h2 class="text-sm font-semibold text-gray-900">3. Select recipients</h2>
                        <p class="mt-1 text-xs text-gray-400">Duplicate addresses are removed automatically. Every person receives a separate email.</p>
                    </div>

                    <div class="grid gap-5 xl:grid-cols-2">
                        <div class="rounded-xl border border-gray-200 p-4">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Customers</p>
                                    <p class="text-xs text-gray-400"><span x-text="selectedCustomers.length"></span> selected</p>
                                </div>
                                <button type="button" @click="selectedCustomers = selectedCustomers.length === allCustomers.length ? [] : [...allCustomers]" class="text-xs font-medium text-champagne-dark hover:underline">Toggle all</button>
                            </div>
                            <div class="max-h-72 space-y-1 overflow-y-auto pr-1">
                                @forelse ($customers as $customer)
                                    <label class="flex cursor-pointer items-start gap-2.5 rounded-lg px-2 py-2 hover:bg-gray-50">
                                        <input type="checkbox" name="customer_ids[]" value="{{ $customer->id }}" x-model="selectedCustomers" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-champagne-dark">
                                        <span class="min-w-0 text-sm text-gray-700">
                                            <span class="block font-medium">{{ $customer->name }}</span>
                                            <span class="block truncate text-xs text-gray-400">{{ $customer->email }}</span>
                                        </span>
                                    </label>
                                @empty
                                    <p class="py-6 text-center text-sm text-gray-400">No active customers found.</p>
                                @endforelse
                            </div>
                            @error('customer_ids.*') <p class="mt-2 text-xs text-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="rounded-xl border border-gray-200 p-4">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Influencers</p>
                                    <p class="text-xs text-gray-400"><span x-text="selectedInfluencers.length"></span> selected</p>
                                </div>
                                <button type="button" @click="selectedInfluencers = selectedInfluencers.length === allInfluencers.length ? [] : [...allInfluencers]" class="text-xs font-medium text-champagne-dark hover:underline">Toggle all</button>
                            </div>
                            <div class="max-h-72 space-y-1 overflow-y-auto pr-1">
                                @forelse ($influencers as $influencer)
                                    <label class="flex cursor-pointer items-start gap-2.5 rounded-lg px-2 py-2 hover:bg-gray-50">
                                        <input type="checkbox" name="influencer_ids[]" value="{{ $influencer->id }}" x-model="selectedInfluencers" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-champagne-dark">
                                        <span class="min-w-0 flex-1 text-sm text-gray-700">
                                            <span class="flex items-center gap-2"><strong>{{ $influencer->full_name }}</strong><x-admin.status-badge :status="$influencer->status" /></span>
                                            <span class="block truncate text-xs text-gray-400">{{ $influencer->email }}</span>
                                        </span>
                                    </label>
                                @empty
                                    <p class="py-6 text-center text-sm text-gray-400">No influencers found.</p>
                                @endforelse
                            </div>
                            @error('influencer_ids.*') <p class="mt-2 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-5">
                        <label for="manual_emails" class="admin-label">Additional Email Addresses</label>
                        <textarea id="manual_emails" name="manual_emails" rows="4" class="admin-textarea resize-y" placeholder="one@example.com&#10;two@example.com">{{ old('manual_emails') }}</textarea>
                        <p class="mt-1 text-xs text-gray-400">Enter one address per line, or separate addresses with commas.</p>
                        @error('manual_emails') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="admin-card p-6">
                    <h2 class="text-sm font-semibold text-gray-900">Delivery Summary</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Customers</dt><dd class="font-medium text-gray-800" x-text="selectedCustomers.length"></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Influencers</dt><dd class="font-medium text-gray-800" x-text="selectedInfluencers.length"></dd></div>
                        <div class="border-t border-gray-100 pt-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Required CC</dt>
                            <dd class="mt-1 break-all font-medium text-champagne-dark">{{ $ccEmail }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs leading-5 text-amber-800">
                    Submitting this form immediately places one job per recipient on the Laravel <strong>emails</strong> queue. Review the subject, links and recipient selection before continuing.
                </div>

                <button type="submit" class="admin-btn-primary w-full py-3">Create &amp; Queue Campaign</button>
                <a href="{{ route('admin.email-campaigns.index') }}" class="admin-btn-secondary w-full">Cancel</a>
            </div>
        </div>
    </form>
@endsection

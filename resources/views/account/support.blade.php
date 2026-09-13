<x-layouts.app :title="$title">
    <x-account.shell active="support">
        <h1 class="font-display text-2xl text-charcoal sm:text-3xl">Support</h1>
        <p class="mt-2 text-sm text-muted">Need help? Reach us directly or browse common questions below.</p>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach ([
                ['label' => 'Call Us', 'value' => '+91 80 4567 8900', 'icon' => 'M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3 19.5 19.5 0 01-6-6 19.8 19.8 0 01-3-8.7A2 2 0 014.1 2h3a2 2 0 012 1.7c.1.9.3 1.8.6 2.7a2 2 0 01-.4 2.1L8 9.9a16 16 0 006 6l1.4-1.4a2 2 0 012.1-.4c.9.3 1.8.5 2.7.6a2 2 0 011.8 2.2z'],
                ['label' => 'Email Us', 'value' => 'care@aurellejewellery.com', 'icon' => 'M3 6h18v12H3zM3 6l9 7 9-7'],
                ['label' => 'WhatsApp', 'value' => '+91 98765 00000', 'icon' => 'M12 2a10 10 0 00-8.6 15.1L2 22l5-1.4A10 10 0 1012 2z'],
            ] as $item)
                <div class="flex items-center gap-3 rounded-xl border border-line p-4">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-beige text-champagne-dark">
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="{{ $item['icon'] }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </div>
                    <div>
                        <p class="text-xs text-muted">{{ $item['label'] }}</p>
                        <p class="text-sm font-medium text-charcoal">{{ $item['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-10">
            <h2 class="font-display text-xl text-charcoal">Frequently Asked Questions</h2>
            <div class="mt-4 divide-y divide-line rounded-2xl border border-line" x-data="{ open: 0 }">
                @foreach ($faqs as $i => $faq)
                    <div>
                        <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between px-5 py-4 text-left">
                            <span class="text-sm font-medium text-charcoal">{{ $faq['q'] }}</span>
                            <svg class="h-4 w-4 flex-shrink-0 text-muted transition-transform" :class="open === {{ $i }} && 'rotate-45'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                        </button>
                        <div x-cloak x-show="open === {{ $i }}" x-collapse class="px-5 pb-4 text-sm leading-relaxed text-muted">{{ $faq['a'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-account.shell>
</x-layouts.app>

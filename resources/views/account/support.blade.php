<x-layouts.app :title="$title">
    <x-account.shell active="support">
        <h1 class="font-display text-2xl text-charcoal sm:text-3xl">Support</h1>
        <p class="mt-2 text-sm text-muted">Need help? Reach us directly or browse common questions below.</p>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach ([
                ['label' => 'Call Us', 'value' => $settings->phone, 'icon' => 'M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3 19.5 19.5 0 01-6-6 19.8 19.8 0 01-3-8.7A2 2 0 014.1 2h3a2 2 0 012 1.7c.1.9.3 1.8.6 2.7a2 2 0 01-.4 2.1L8 9.9a16 16 0 006 6l1.4-1.4a2 2 0 012.1-.4c.9.3 1.8.5 2.7.6a2 2 0 011.8 2.2z'],
                ['label' => 'Email Us', 'value' => $settings->support_email, 'icon' => 'M3 6h18v12H3zM3 6l9 7 9-7'],
                ['label' => 'WhatsApp', 'value' => $settings->whatsapp, 'icon' => 'M12 2a10 10 0 00-8.6 15.1L2 22l5-1.4A10 10 0 1012 2z'],
            ] as $item)
                @php
                    $contactUrl = match ($item['label']) {
                        'Call Us' => 'tel:'.preg_replace('/[^+0-9]/', '', $item['value'] ?? ''),
                        'Email Us' => 'mailto:'.$item['value'],
                        'WhatsApp' => 'https://wa.me/'.preg_replace('/\D/', '', $item['value'] ?? ''),
                    };
                @endphp
                @if (filled($item['value']))
                <a href="{{ $contactUrl }}" class="flex items-center gap-3 rounded-xl border border-line p-4 transition-colors hover:bg-ivory-soft">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-beige text-champagne-dark">
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="{{ $item['icon'] }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </div>
                    <div>
                        <p class="text-xs text-muted">{{ $item['label'] }}</p>
                        <p class="text-sm font-medium text-charcoal">{{ $item['value'] }}</p>
                    </div>
                </a>
                @endif
            @endforeach
        </div>

        <div class="mt-10">
            <h2 class="font-display text-xl text-charcoal">Send a Support Request</h2>
            <form method="POST" action="{{ route('account.support.store') }}" class="mt-4 max-w-xl space-y-4">
                @csrf
                <div>
                    <label for="support-subject" class="label-luxe">Subject</label>
                    <input id="support-subject" name="subject" required maxlength="150" value="{{ old('subject') }}" class="input-luxe">
                </div>
                <div>
                    <label for="support-message" class="label-luxe">Message</label>
                    <textarea id="support-message" name="message" required maxlength="2000" rows="5" class="input-luxe">{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="btn-primary">Submit Request</button>
            </form>
        </div>

        <div class="mt-10">
            <h2 class="font-display text-xl text-charcoal">Your Support Requests</h2>
            <div class="mt-4 space-y-4">
                @forelse ($tickets as $ticket)
                    <div class="rounded-xl border border-line p-5">
                        <div class="flex flex-wrap justify-between gap-2">
                            <p class="text-sm font-semibold text-charcoal">{{ $ticket->ticket_no }} &middot; {{ $ticket->subject }}</p>
                            <span class="text-xs text-muted">{{ Str::headline($ticket->status) }}</span>
                        </div>
                        <p class="mt-2 whitespace-pre-line text-sm text-muted">{{ $ticket->message }}</p>
                        <p class="mt-2 text-xs text-muted-light">{{ $ticket->created_at->format('d M Y, h:i A') }}</p>
                        @if ($ticket->admin_reply)
                            <div class="mt-4 rounded-lg bg-ivory-soft p-4">
                                <p class="text-xs font-semibold text-charcoal">Support reply</p>
                                <p class="mt-1 whitespace-pre-line text-sm text-muted">{{ $ticket->admin_reply }}</p>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-muted">You haven't submitted any support requests yet.</p>
                @endforelse
            </div>
            <div class="mt-6">{{ $tickets->links() }}</div>
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

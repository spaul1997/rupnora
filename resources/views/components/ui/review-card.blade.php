@props(['review'])

<div class="card-luxe h-full p-6">
    <x-ui.rating :value="$review['rating']" size="sm" />
    <p class="mt-4 text-[14.5px] leading-relaxed text-charcoal-soft">&ldquo;{{ $review['text'] }}&rdquo;</p>
    <div class="mt-5 flex items-center gap-3 border-t border-line pt-4">
        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-beige font-display text-sm text-champagne-dark">
            {{ Str::of($review['name'])->explode(' ')->map(fn($n) => $n[0])->take(2)->implode('') }}
        </div>
        <div>
            <p class="text-sm font-medium text-charcoal">{{ $review['name'] }}</p>
            <p class="text-xs text-muted">{{ $review['location'] }} &middot; {{ $review['date'] }}</p>
        </div>
        @if ($review['verified'])
            <span class="ml-auto inline-flex items-center gap-1 text-[11px] font-medium text-success">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                Verified
            </span>
        @endif
    </div>
</div>

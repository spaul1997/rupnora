@props([
    'current' => 1,
    'total' => 5,
])

<nav aria-label="Pagination" class="flex items-center justify-center gap-1.5">
    <button type="button" class="icon-btn border border-line disabled:opacity-40" @disabled($current <= 1)>
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
    </button>

    @for ($i = 1; $i <= $total; $i++)
        @if ($i === 1 || $i === $total || abs($i - $current) <= 1)
            <button type="button" class="h-9 min-w-9 rounded-full px-2 text-sm font-medium transition-colors {{ $i === $current ? 'bg-charcoal text-ivory' : 'text-charcoal hover:bg-charcoal/5' }}">
                {{ $i }}
            </button>
        @elseif ($i === 2 || $i === $total - 1)
            <span class="px-1 text-muted">&hellip;</span>
        @endif
    @endfor

    <button type="button" class="icon-btn border border-line disabled:opacity-40" @disabled($current >= $total)>
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
    </button>
</nav>

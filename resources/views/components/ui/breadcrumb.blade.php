@props([
    'trail' => [],
])

<nav aria-label="Breadcrumb" class="text-xs">
    <ol class="flex flex-wrap items-center gap-1.5 text-muted">
        <li>
            <a href="{{ route('home') }}" class="hover:text-champagne-dark transition-colors">Home</a>
        </li>
        @foreach ($trail as $crumb)
            <li class="flex items-center gap-1.5">
                <svg class="h-3 w-3 text-line" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                @if (! empty($crumb['url']) && ! $loop->last)
                    <a href="{{ $crumb['url'] }}" class="hover:text-champagne-dark transition-colors">{{ $crumb['label'] }}</a>
                @else
                    <span class="text-charcoal font-medium">{{ $crumb['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

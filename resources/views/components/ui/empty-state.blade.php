@props([
    'icon' => 'bag',
    'title' => 'Nothing here yet',
    'description' => null,
    'actionLabel' => null,
    'actionUrl' => null,
])

@php
    $icons = [
        'bag' => 'M6 8h12l-1 12H7L6 8zM9 8V6a3 3 0 016 0v2',
        'heart' => 'M12 20.5s-7.5-4.9-10.1-9.6C.3 7.9 1.6 4.5 4.9 3.6c2-.5 4 .3 5.1 2 .3.4.7.4 1 0 1.1-1.7 3.1-2.5 5.1-2 3.3.9 4.6 4.3 3 7.3-2.6 4.7-10.1 9.6-10.1 9.6z',
        'search' => 'M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.35-4.35',
        'box' => 'M21 8l-9-5-9 5 9 5 9-5zM3 8v8l9 5 9-5V8M12 13v8',
        'map' => 'M12 21s-7-6.5-7-11.5A7 7 0 0112 2a7 7 0 017 7.5C19 14.5 12 21 12 21zM12 12a2.5 2.5 0 100-5 2.5 2.5 0 000 5z',
    ];
@endphp

<div class="flex flex-col items-center justify-center px-6 py-16 text-center">
    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-beige">
        <svg class="h-7 w-7 text-champagne-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="{{ $icons[$icon] ?? $icons['bag'] }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
    </div>
    <h3 class="mt-5 font-display text-xl text-charcoal">{{ $title }}</h3>
    @if ($description)
        <p class="mt-2 max-w-sm text-sm text-muted">{{ $description }}</p>
    @endif
    @if ($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn-primary mt-6">{{ $actionLabel }}</a>
    @endif
</div>

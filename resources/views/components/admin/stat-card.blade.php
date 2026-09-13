@props([
    'label',
    'value',
    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    'tone' => 'default',
    'href' => null,
    'trend' => null,
])

@php
    $tones = [
        'default' => 'bg-beige text-champagne-dark',
        'success' => 'bg-success/10 text-success',
        'warning' => 'bg-amber-100 text-amber-600',
        'error' => 'bg-error/10 text-error',
        'info' => 'bg-blue-100 text-blue-600',
    ];
@endphp

<a href="{{ $href ?? '#' }}" class="admin-card flex items-center gap-4 p-5 {{ $href ? 'transition-shadow hover:shadow-md' : 'cursor-default' }}">
    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] ?? $tones['default'] }}">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="{{ $icon }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
    </div>
    <div class="min-w-0">
        <p class="truncate text-xs font-medium text-gray-500">{{ $label }}</p>
        <p class="mt-0.5 text-xl font-semibold text-gray-900">{{ $value }}</p>
        @if ($trend)
            <p class="mt-0.5 text-xs text-gray-400">{{ $trend }}</p>
        @endif
    </div>
</a>

@props([
    'value' => 4.5,
    'count' => null,
    'size' => 'sm',
])

@php
    $sizeClasses = $size === 'lg' ? 'h-4.5 w-4.5' : ($size === 'xs' ? 'h-2.5 w-2.5' : 'h-3.5 w-3.5');
    $textSize = $size === 'lg' ? 'text-sm' : ($size === 'xs' ? 'text-[10.5px]' : 'text-xs');
    $gap = $size === 'xs' ? 'gap-1' : 'gap-1.5';
@endphp

<div class="flex items-center {{ $gap }}">
    <div class="flex items-center gap-0.5">
        @for ($i = 1; $i <= 5; $i++)
            <svg class="{{ $sizeClasses }} {{ $i <= round($value) ? 'text-champagne-dark' : 'text-line' }}" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 1.5l2.6 5.6 6.1.6-4.6 4.2 1.3 6.1L10 15l-5.4 3 1.3-6.1L1.3 7.7l6.1-.6L10 1.5z" />
            </svg>
        @endfor
    </div>
    @if ($count !== null)
        <span class="{{ $textSize }} text-muted">{{ number_format($value, 1) }} · {{ $count }} {{ Str::plural('review', $count) }}</span>
    @endif
</div>

@props([
    'tone' => 'champagne',
])

@php
    $tones = [
        'champagne' => 'bg-champagne text-ivory',
        'charcoal' => 'bg-charcoal text-ivory',
        'success' => 'bg-success/10 text-success',
        'error' => 'bg-error/10 text-error',
        'outline' => 'bg-paper text-charcoal border border-line',
        'limited' => 'bg-error text-ivory',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'badge-luxe ' . ($tones[$tone] ?? $tones['champagne'])]) }}>
    {{ $slot }}
</span>

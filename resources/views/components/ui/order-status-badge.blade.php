@props(['status'])

@php
    $tones = [
        'Processing' => 'bg-champagne-light/60 text-champagne-dark',
        'Confirmed' => 'bg-champagne-light/60 text-champagne-dark',
        'Packed' => 'bg-beige text-charcoal-soft',
        'Shipped' => 'bg-charcoal/10 text-charcoal',
        'Out for Delivery' => 'bg-charcoal/10 text-charcoal',
        'Delivered' => 'bg-success/10 text-success',
        'Cancelled' => 'bg-error/10 text-error',
        'Returned' => 'bg-error/10 text-error',
    ];
@endphp

<span class="badge-luxe {{ $tones[$status] ?? 'bg-beige text-charcoal-soft' }}">{{ $status }}</span>

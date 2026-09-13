@props([
    'price' => 0,
    'mrp' => null,
    'size' => 'md',
    'align' => 'left',
])

@php
    $discount = $mrp && $mrp > $price ? round((($mrp - $price) / $mrp) * 100) : null;
    $priceSize = match ($size) {
        'lg' => 'text-2xl sm:text-[28px]',
        'sm' => 'text-[15px]',
        'xs' => 'text-[12.5px]',
        default => 'text-lg',
    };
    $secondarySize = $size === 'xs' ? 'text-[10.5px]' : 'text-sm';
    $gap = $size === 'xs' ? 'gap-x-1.5 gap-y-0' : 'gap-x-2 gap-y-1';
@endphp

<div class="flex flex-wrap items-baseline {{ $gap }} {{ $align === 'center' ? 'justify-center' : '' }}">
    <span class="font-semibold text-charcoal {{ $priceSize }}">₹{{ number_format($price) }}</span>
    @if ($mrp && $mrp > $price)
        <span class="{{ $secondarySize }} text-muted-light line-through">₹{{ number_format($mrp) }}</span>
        <span class="{{ $secondarySize }} font-semibold text-success">{{ $discount }}% OFF</span>
    @endif
</div>

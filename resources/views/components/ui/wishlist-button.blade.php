@props([
    'id' => '',
    'size' => 'md',
])

@php
    $dim = $size === 'sm' ? 'h-8 w-8' : 'h-10 w-10';
    $iconDim = $size === 'sm' ? 'h-4 w-4' : 'h-[18px] w-[18px]';
@endphp

<button
    type="button"
    @click.stop.prevent="$store.ui.toggleWishlist('{{ $id }}')"
    :aria-pressed="$store.ui.isWishlisted('{{ $id }}')"
    aria-label="Toggle wishlist"
    {{ $attributes->merge(['class' => "$dim inline-flex items-center justify-center rounded-full bg-paper/90 text-charcoal shadow-card backdrop-blur transition-all duration-200 hover:scale-105 active:scale-95"]) }}
>
    <svg :class="$store.ui.isWishlisted('{{ $id }}') ? 'text-error' : 'text-charcoal'" class="{{ $iconDim }} transition-colors" viewBox="0 0 24 24" :fill="$store.ui.isWishlisted('{{ $id }}') ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.6">
        <path d="M12 20.5s-7.5-4.9-10.1-9.6C.3 7.9 1.6 4.5 4.9 3.6c2-.5 4 .3 5.1 2 .3.4.7.4 1 0 1.1-1.7 3.1-2.5 5.1-2 3.3.9 4.6 4.3 3 7.3-2.6 4.7-10.1 9.6-10.1 9.6z" stroke-linejoin="round" />
    </svg>
</button>

@props([
    'art' => 'ring',
    'tone' => 1,
    'class' => 'aspect-square',
])

@php
    $gradients = [
        1 => 'from-beige via-ivory-soft to-champagne-light/70',
        2 => 'from-champagne-light/80 via-paper to-beige',
    ];
    $gradient = $gradients[$tone] ?? $gradients[1];
@endphp

<div {{ $attributes->merge(['class' => "$class relative overflow-hidden bg-gradient-to-br $gradient"]) }}>
    <div class="absolute inset-0 opacity-[0.06]" style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 16px 16px; color: var(--color-charcoal);"></div>
    <div class="absolute inset-0 flex items-center justify-center p-[18%]">
        <svg viewBox="0 0 100 100" class="h-full w-full text-charcoal-soft/65" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
            @switch($art)
                @case('ring')
                    <circle cx="50" cy="62" r="24" />
                    <path d="M50 38 L58 24 L50 16 L42 24 Z" />
                    <line x1="50" y1="16" x2="50" y2="24" opacity="0.5" />
                    @break

                @case('earring')
                    <circle cx="35" cy="30" r="10" />
                    <path d="M35 40 L35 62" />
                    <path d="M28 62 Q35 76 42 62 Z" />
                    <circle cx="70" cy="30" r="10" />
                    <path d="M70 40 L70 58" />
                    <path d="M64 58 Q70 70 76 58 Z" />
                    @break

                @case('necklace')
                    <path d="M18 20 Q50 62 82 20" />
                    <path d="M18 20 Q50 68 82 20" opacity="0.4" />
                    <path d="M44 60 L50 74 L56 60 Z" />
                    @break

                @case('pendant')
                    <circle cx="50" cy="24" r="7" />
                    <path d="M50 31 L50 42" />
                    <path d="M38 42 L62 42 L54 76 L46 76 Z" />
                    @break

                @case('bracelet')
                    <ellipse cx="50" cy="50" rx="34" ry="20" />
                    <ellipse cx="50" cy="50" rx="34" ry="20" transform="rotate(35 50 50)" opacity="0.45" />
                    @break

                @case('bangle')
                    <circle cx="50" cy="50" r="30" />
                    <circle cx="50" cy="50" r="23" opacity="0.4" />
                    @break

                @case('chain')
                    <ellipse cx="32" cy="30" rx="10" ry="14" transform="rotate(-20 32 30)" />
                    <ellipse cx="50" cy="44" rx="10" ry="14" transform="rotate(20 50 44)" opacity="0.85" />
                    <ellipse cx="32" cy="58" rx="10" ry="14" transform="rotate(-20 32 58)" opacity="0.7" />
                    <ellipse cx="50" cy="72" rx="10" ry="14" transform="rotate(20 50 72)" opacity="0.55" />
                    @break

                @case('mens')
                    <rect x="24" y="40" width="52" height="20" rx="4" />
                    <line x1="36" y1="40" x2="36" y2="60" opacity="0.5" />
                    <line x1="50" y1="40" x2="50" y2="60" opacity="0.5" />
                    <line x1="64" y1="40" x2="64" y2="60" opacity="0.5" />
                    @break

                @case('gold')
                    <path d="M28 34 L72 34 L64 70 L36 70 Z" />
                    <line x1="34" y1="46" x2="66" y2="46" opacity="0.4" />
                    <line x1="31" y1="58" x2="69" y2="58" opacity="0.4" />
                    @break

                @case('diamond')
                    <path d="M32 38 L68 38 L50 82 Z" />
                    <path d="M22 38 L32 38 L50 82 Z" opacity="0.5" />
                    <path d="M68 38 L78 38 L50 82 Z" opacity="0.5" />
                    <path d="M22 38 L38 18 L62 18 L78 38 Z" />
                    <line x1="38" y1="18" x2="32" y2="38" opacity="0.4" />
                    <line x1="62" y1="18" x2="68" y2="38" opacity="0.4" />
                    <line x1="50" y1="18" x2="50" y2="38" opacity="0.4" />
                    @break

                @case('silver')
                    <circle cx="50" cy="50" r="26" />
                    <path d="M50 24 L56 40 L50 50 L44 40 Z" opacity="0.6" />
                    @break

                @case('bridal')
                    <path d="M20 58 Q35 22 50 58 Q65 22 80 58" />
                    <circle cx="20" cy="58" r="3.5" />
                    <circle cx="50" cy="58" r="4.5" />
                    <circle cx="80" cy="58" r="3.5" />
                    <path d="M50 62 L50 78" opacity="0.5" />
                    <path d="M44 78 L56 78" opacity="0.5" />
                    @break

                @default
                    <circle cx="50" cy="50" r="26" />
            @endswitch
        </svg>
    </div>
    {{ $slot ?? '' }}
</div>

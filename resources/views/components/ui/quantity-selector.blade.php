@props([
    'model' => 'qty',
    'min' => 1,
    'max' => 10,
])

<div class="inline-flex items-center rounded-full border border-line">
    <button type="button" @click="{{ $model }} = Math.max({{ $min }}, {{ $model }} - 1)" class="flex h-10 w-10 items-center justify-center text-charcoal transition-colors hover:text-champagne-dark disabled:opacity-30" :disabled="{{ $model }} <= {{ $min }}">
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14" stroke-linecap="round" /></svg>
    </button>
    <span class="w-8 text-center text-sm font-medium text-charcoal" x-text="{{ $model }}"></span>
    <button type="button" @click="{{ $model }} = Math.min({{ $max }}, {{ $model }} + 1)" class="flex h-10 w-10 items-center justify-center text-charcoal transition-colors hover:text-champagne-dark disabled:opacity-30" :disabled="{{ $model }} >= {{ $max }}">
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
    </button>
</div>

@props(['address', 'selectable' => false, 'model' => 'selectedAddress'])

<div
    @if($selectable) @click="{{ $model }} = {{ $address['id'] }}" @endif
    class="card-luxe relative p-5 {{ $selectable ? 'cursor-pointer transition-colors' : '' }}"
    @if($selectable) :class="{{ $model }} === {{ $address['id'] }} ? 'border-champagne-dark ring-1 ring-champagne-dark' : ''" @endif
>
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-2">
            @if ($selectable)
                <span class="flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full border-2" :class="{{ $model }} === {{ $address['id'] }} ? 'border-champagne-dark' : 'border-line'">
                    <span x-show="{{ $model }} === {{ $address['id'] }}" class="h-2 w-2 rounded-full bg-champagne-dark"></span>
                </span>
            @endif
            <span class="badge-luxe bg-beige text-charcoal-soft">{{ $address['type'] }}</span>
            @if ($address['default'])
                <span class="badge-luxe bg-champagne text-charcoal">Default</span>
            @endif
        </div>
        @if (! $selectable)
            <div class="flex items-center gap-3 text-xs">
                <button type="button" class="text-muted hover:text-champagne-dark">Edit</button>
                <button type="button" class="text-muted hover:text-error">Delete</button>
            </div>
        @endif
    </div>
    <p class="mt-3 text-sm font-semibold text-charcoal">{{ $address['name'] }}</p>
    <p class="mt-1 text-sm leading-relaxed text-muted">
        {{ $address['line1'] }}, {{ $address['line2'] }}@if(!empty($address['landmark'])), {{ $address['landmark'] }}@endif<br>
        {{ $address['city'] }}, {{ $address['state'] }} {{ $address['pincode'] }}<br>
        {{ $address['country'] }}
    </p>
    <p class="mt-2 text-sm text-charcoal">{{ $address['phone'] }}</p>
    @if (! $selectable && ! $address['default'])
        <button type="button" class="mt-3 text-xs font-medium text-champagne-dark hover:underline">Set as Default</button>
    @endif
</div>

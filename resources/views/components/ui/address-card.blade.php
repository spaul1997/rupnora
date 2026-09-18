@props(['address', 'selectable' => false, 'manage' => false, 'model' => 'selectedAddress'])

<div
    @if($selectable) @click="{{ $model }} = {{ Illuminate\Support\Js::from($address['id']) }}" @endif
    class="card-luxe relative p-5 {{ $selectable ? 'cursor-pointer transition-colors' : '' }}"
    @if($selectable) :class="{{ $model }} === {{ Illuminate\Support\Js::from($address['id']) }} ? 'border-champagne-dark ring-1 ring-champagne-dark' : ''" @endif
>
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-2">
            @if ($selectable)
                <span class="flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full border-2" :class="{{ $model }} === {{ Illuminate\Support\Js::from($address['id']) }} ? 'border-champagne-dark' : 'border-line'">
                    <span x-show="{{ $model }} === {{ Illuminate\Support\Js::from($address['id']) }}" class="h-2 w-2 rounded-full bg-champagne-dark"></span>
                </span>
            @endif
            <span class="badge-luxe bg-beige text-charcoal-soft">{{ $address['type'] }}</span>
            @if ($address['default'])
                <span class="badge-luxe bg-champagne text-charcoal">Default</span>
            @endif
        </div>
        @if ($manage && ! $selectable)
            <div class="flex items-center gap-3 text-xs">
                <a href="{{ route('account.addresses.edit', $address['id']) }}" class="text-muted hover:text-champagne-dark">Edit</a>
                <form method="POST" action="{{ route('account.addresses.destroy', $address['id']) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-muted hover:text-error">Delete</button>
                </form>
            </div>
        @endif
    </div>
    <p class="mt-3 text-sm font-semibold text-charcoal">{{ $address['name'] }}</p>
    <p class="mt-1 text-sm leading-relaxed text-muted">
        {{ collect([$address['line1'], $address['line2'], $address['landmark'] ?? ''])->filter()->join(', ') }}<br>
        {{ collect([$address['city'], $address['district'] ?? '', $address['state']])->filter()->join(', ') }} {{ $address['pincode'] }}<br>
        {{ $address['country'] }}
    </p>
    <p class="mt-2 text-sm text-charcoal">{{ $address['phone'] }}</p>
    @if ($manage && ! $selectable && ! $address['default'])
        <form method="POST" action="{{ route('account.addresses.default', $address['id']) }}" class="mt-3">
            @csrf
            @method('PATCH')
            <button type="submit" class="text-xs font-medium text-champagne-dark hover:underline">Set as Default</button>
        </form>
    @endif
</div>

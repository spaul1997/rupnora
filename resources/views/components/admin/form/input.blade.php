@props([
    'label' => null,
    'name',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'help' => null,
])

<div>
    @if ($label)
        <label for="{{ $name }}" class="admin-label">{{ $label }} @if($required)<span class="text-error">*</span>@endif</label>
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'admin-input']) }}
    >
    @if ($help)
        <p class="mt-1 text-xs text-gray-400">{{ $help }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-xs text-error">{{ $message }}</p>
    @enderror
</div>

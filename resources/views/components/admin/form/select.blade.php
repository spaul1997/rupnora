@props([
    'label' => null,
    'name',
    'options' => [],
    'value' => null,
    'required' => false,
    'placeholder' => 'Select an option',
    'help' => null,
])

<div>
    @if ($label)
        <label for="{{ $name }}" class="admin-label">{{ $label }} @if($required)<span class="text-error">*</span>@endif</label>
    @endif
    <select
        name="{{ $name }}"
        id="{{ $name }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'admin-select']) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected((string) old($name, $value) === (string) $optValue)>{{ $optLabel }}</option>
        @endforeach
    </select>
    @if ($help)
        <p class="mt-1 text-xs text-gray-400">{{ $help }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-xs text-error">{{ $message }}</p>
    @enderror
</div>

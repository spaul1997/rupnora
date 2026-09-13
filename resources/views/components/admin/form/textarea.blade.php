@props([
    'label' => null,
    'name',
    'value' => null,
    'required' => false,
    'rows' => 4,
    'help' => null,
])

<div>
    @if ($label)
        <label for="{{ $name }}" class="admin-label">{{ $label }} @if($required)<span class="text-error">*</span>@endif</label>
    @endif
    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'admin-textarea resize-none']) }}
    >{{ old($name, $value) }}</textarea>
    @if ($help)
        <p class="mt-1 text-xs text-gray-400">{{ $help }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-xs text-error">{{ $message }}</p>
    @enderror
</div>

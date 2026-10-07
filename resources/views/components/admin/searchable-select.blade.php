@props([
    'name',
    'options' => [],
    'selected' => null,
    'placeholder' => 'Select an option',
    'searchPlaceholder' => 'Search options...',
    'compact' => false,
])

@php
    $selectedValue = (string) ($selected ?? '');
    $searchableOptions = collect($options)
        ->map(fn ($label, $value) => [
            'value' => (string) $value,
            'label' => (string) $label,
        ])
        ->prepend([
            'value' => '',
            'label' => $placeholder,
        ])
        ->values()
        ->all();
@endphp

<div
    x-data="{
        open: false,
        search: '',
        selected: {{ Illuminate\Support\Js::from($selectedValue) }},
        options: {{ Illuminate\Support\Js::from($searchableOptions) }},
        get selectedLabel() {
            return this.options.find((option) => option.value === this.selected)?.label ?? {{ Illuminate\Support\Js::from($placeholder) }};
        },
        get filteredOptions() {
            const term = this.search.trim().toLowerCase();
            return term === ''
                ? this.options
                : this.options.filter((option) => option.label.toLowerCase().includes(term));
        },
        choose(value) {
            this.selected = value;
            this.search = '';
            this.open = false;
        },
    }"
    @keydown.escape.window="open = false"
    data-searchable-select="{{ $name }}"
    {{ $attributes->class(['relative w-full']) }}
>
    <input type="hidden" name="{{ $name }}" value="{{ $selectedValue }}" :value="selected">

    <button
        type="button"
        @class(['admin-select flex items-center justify-between gap-2 text-left', '!py-2' => $compact])
        aria-haspopup="listbox"
        :aria-expanded="open.toString()"
        @click="open = !open; if (open) $nextTick(() => $refs.searchInput.focus())"
    >
        <span class="truncate" x-text="selectedLabel"></span>
        <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.opacity.duration.150ms
        @click.outside="open = false"
        class="absolute left-0 z-40 mt-1 w-full min-w-[12rem] overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg"
    >
        <div class="border-b border-gray-100 p-2">
            <input
                x-ref="searchInput"
                x-model="search"
                type="search"
                placeholder="{{ $searchPlaceholder }}"
                autocomplete="off"
                class="admin-input !py-2"
            >
        </div>

        <div class="max-h-56 overflow-y-auto p-1" role="listbox">
            <template x-for="option in filteredOptions" :key="option.value">
                <button
                    type="button"
                    role="option"
                    :aria-selected="(selected === option.value).toString()"
                    @click="choose(option.value)"
                    class="flex w-full items-center justify-between gap-3 rounded-md px-3 py-2 text-left text-sm transition-colors hover:bg-gray-50"
                    :class="selected === option.value ? 'bg-ivory-soft font-medium text-champagne-dark' : 'text-gray-700'"
                >
                    <span x-text="option.label"></span>
                    <svg x-show="selected === option.value" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </template>

            <p x-show="filteredOptions.length === 0" class="px-3 py-4 text-center text-sm text-gray-400">No options found.</p>
        </div>
    </div>
</div>

@props([
    'title' => 'No records found',
    'description' => null,
    'icon' => 'M9 13h6m-6-4h6m2 11H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
])

<div class="flex flex-col items-center justify-center px-6 py-16 text-center">
    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
        <svg class="h-6 w-6 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="{{ $icon }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
    </div>
    <h3 class="mt-4 text-sm font-semibold text-gray-800">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1.5 max-w-sm text-sm text-gray-500">{{ $description }}</p>
    @endif
    @isset($actions)
        <div class="mt-5">{{ $actions }}</div>
    @endisset
</div>

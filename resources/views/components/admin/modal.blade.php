@props([
    'model' => 'open',
    'title' => null,
    'maxWidth' => 'max-w-lg',
])

<div x-cloak x-show="{{ $model }}" x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 p-4" @click.self="{{ $model }} = false" @keydown.window.escape="{{ $model }} = false">
    <div
        x-show="{{ $model }}"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="max-h-[90vh] w-full {{ $maxWidth }} overflow-y-auto rounded-xl bg-white shadow-xl"
    >
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
            <button type="button" @click="{{ $model }} = false" class="text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg>
            </button>
        </div>
        <div class="p-6">
            {{ $slot }}
        </div>
    </div>
</div>

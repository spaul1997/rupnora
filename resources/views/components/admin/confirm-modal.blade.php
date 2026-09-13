@props([
    'action',
    'title' => 'Delete Record',
    'message' => 'Are you sure you want to delete this record? This action cannot be undone.',
    'method' => 'DELETE',
    'triggerLabel' => 'Delete',
    'triggerClass' => 'text-error hover:underline text-sm font-medium',
])

<div x-data="{ open: false }" class="inline-block">
    <button type="button" @click="open = true" class="{{ $triggerClass }}">{{ $triggerLabel }}</button>

    <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 p-4" @click.self="open = false" @keydown.window.escape="open = false">
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-error/10 text-error">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 9v4m0 4h.01M10.3 3.9L2.5 17a1 1 0 00.9 1.5h17.2a1 1 0 00.9-1.5L13.7 3.9a1 1 0 00-1.4 0z" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">{{ $title }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $message }}</p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="open = false" class="admin-btn-secondary">Cancel</button>
                <form method="POST" action="{{ $action }}">
                    @csrf
                    @if (strtoupper($method) !== 'POST')
                        @method($method)
                    @endif
                    <button type="submit" class="admin-btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="pointer-events-none fixed inset-x-0 bottom-4 z-[100] flex flex-col items-center gap-2 px-4 sm:bottom-6 sm:items-end sm:right-6 sm:left-auto sm:px-0">
    <template x-for="toast in $store.ui.toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto flex w-full max-w-sm items-center gap-3 rounded-xl border border-line bg-charcoal px-4 py-3.5 shadow-lift"
        >
            <svg x-show="toast.type === 'success'" class="h-4.5 w-4.5 flex-shrink-0 text-champagne-light" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            <span class="text-sm text-ivory" x-text="toast.message"></span>
        </div>
    </template>
</div>

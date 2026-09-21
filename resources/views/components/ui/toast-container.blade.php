<div class="pointer-events-none fixed inset-x-0 bottom-20 z-[100] flex flex-col items-center gap-2 px-4 sm:right-6 sm:left-auto sm:w-[26rem] sm:px-0 lg:bottom-6" role="region" aria-label="Store notifications" aria-live="polite">
    <div
        x-cloak
        x-show="$store.ui.socialProof"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="pointer-events-auto relative w-full overflow-hidden rounded-2xl border border-champagne/30 bg-paper shadow-lift"
    >
        <div class="h-1 bg-gradient-to-r from-champagne-dark via-champagne to-champagne-light"></div>
        <div class="flex items-center gap-3 p-3 pr-10">
            <template x-if="$store.ui.socialProof?.image">
                <img
                    :src="$store.ui.socialProof.image"
                    :alt="$store.ui.socialProof.name"
                    class="h-16 w-16 flex-none rounded-xl border border-line object-cover"
                    loading="lazy"
                >
            </template>
            <template x-if="!$store.ui.socialProof?.image">
                <div class="flex h-16 w-16 flex-none items-center justify-center rounded-xl bg-champagne-light/60 text-champagne-dark">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 3l8 6-8 12L4 9l8-6zM4 9h16M8.5 9L12 21 15.5 9M9 3l-2 6m8-6 2 6" stroke-linejoin="round" /></svg>
                </div>
            </template>

            <div class="min-w-0 flex-1">
                <p class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-success">
                    <span class="h-1.5 w-1.5 rounded-full bg-success"></span>
                    <span x-text="$store.ui.socialProof?.recentlyPurchased ? 'Recently purchased' : 'Popular right now'"></span>
                </p>
                <p class="mt-1 truncate font-display text-sm font-semibold text-charcoal" x-text="$store.ui.socialProof?.name"></p>
                <div class="mt-1.5 flex items-center justify-between gap-3">
                    <span class="truncate text-[11px] text-muted" x-text="$store.ui.socialProof?.recentlyPurchased ? 'Chosen by a happy customer' : 'A customer favourite'"></span>
                    <a
                        :href="$store.ui.socialProof?.url"
                        class="flex-none text-[11px] font-semibold text-champagne-dark underline decoration-champagne/50 underline-offset-2 hover:text-charcoal"
                    >View piece</a>
                </div>
            </div>
        </div>

        <button
            type="button"
            @click="$store.ui.dismissSocialProof()"
            class="absolute right-2.5 top-3 inline-flex h-7 w-7 items-center justify-center rounded-full text-muted transition-colors hover:bg-ivory-soft hover:text-charcoal"
            aria-label="Dismiss product notification"
        >
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg>
        </button>
    </div>

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

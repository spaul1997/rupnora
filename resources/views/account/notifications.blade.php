<x-layouts.app :title="$title">
    <x-account.shell active="notifications">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="font-display text-2xl text-charcoal sm:text-3xl">Notifications</h1>
            <button class="text-xs font-medium text-champagne-dark hover:underline">Mark all as read</button>
        </div>

        @if (count($notifications) === 0)
            <x-ui.empty-state icon="box" title="No notifications" description="You're all caught up." />
        @else
            <div class="divide-y divide-line rounded-2xl border border-line">
                @foreach ($notifications as $note)
                    <div class="flex items-start gap-4 p-5">
                        <span class="mt-1.5 h-2 w-2 flex-shrink-0 rounded-full {{ $note['unread'] ? 'bg-champagne-dark' : 'bg-transparent' }}"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-charcoal">{{ $note['title'] }}</p>
                            <p class="mt-1 text-sm text-muted">{{ $note['body'] }}</p>
                            <p class="mt-1.5 text-xs text-muted-light">{{ $note['time'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-account.shell>
</x-layouts.app>

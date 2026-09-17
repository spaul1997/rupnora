@props(['timeline', 'cancelled' => false])

@php
    $steps = array_keys($timeline);
    $completedCount = collect($timeline)->filter()->count();
@endphp

@if ($cancelled)
    <div class="flex items-center gap-3 rounded-xl bg-error/5 p-4">
        <svg class="h-5 w-5 flex-shrink-0 text-error" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path d="M9 9l6 6M15 9l-6 6" stroke-linecap="round" /></svg>
        <p class="text-sm text-charcoal">This order was cancelled.</p>
    </div>
@else
    <div class="flex flex-col gap-0 sm:flex-row sm:items-start">
        @foreach ($steps as $i => $label)
            @php($done = ! empty($timeline[$label]))
            <div class="relative flex flex-1 sm:flex-col items-start sm:items-center gap-3 sm:gap-0 pb-8 sm:pb-0">
                <div class="flex sm:flex-col items-center gap-3 sm:gap-0 sm:w-full">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border-2 {{ $done ? 'border-champagne-dark bg-champagne-dark text-ivory' : 'border-line bg-paper text-muted-light' }}">
                        @if ($done)
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        @else
                            <span class="text-xs font-semibold">{{ $i + 1 }}</span>
                        @endif
                    </div>
                    @if (! $loop->last)
                        <div class="ml-4 h-full w-px sm:ml-0 sm:mt-1 sm:h-px sm:w-full {{ $done ? 'bg-champagne-dark' : 'bg-line' }}"></div>
                    @endif
                </div>
                <div class="sm:mt-3 sm:text-center">
                    <p class="text-[13px] font-medium {{ $done ? 'text-charcoal' : 'text-muted-light' }}">{{ $label }}</p>
                    @if ($done)
                        <p class="text-[11px] text-muted">{{ $timeline[$label] }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

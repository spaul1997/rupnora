@props([
    'title',
    'description' => null,
    'breadcrumb' => [],
])

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        @if (count($breadcrumb) > 0)
            <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-gray-500">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a>
                @foreach ($breadcrumb as $crumb)
                    <svg class="h-3 w-3 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    @if (! empty($crumb['url']))
                        <a href="{{ $crumb['url'] }}" class="hover:text-gray-700">{{ $crumb['label'] }}</a>
                    @else
                        <span class="text-gray-700">{{ $crumb['label'] }}</span>
                    @endif
                @endforeach
            </nav>
        @endif
        <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-shrink-0 flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>

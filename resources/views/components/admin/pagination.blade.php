@props(['paginator'])

@if ($paginator->hasPages())
    <div class="flex flex-col items-center justify-between gap-3 border-t border-gray-200 px-4 py-3.5 sm:flex-row">
        <p class="text-sm text-gray-500">
            Showing <span class="font-medium text-gray-700">{{ $paginator->firstItem() }}</span>
            to <span class="font-medium text-gray-700">{{ $paginator->lastItem() }}</span>
            of <span class="font-medium text-gray-700">{{ $paginator->total() }}</span> results
        </p>
        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="admin-btn-ghost !px-3 !py-1.5 opacity-40">Prev</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="admin-btn-ghost !px-3 !py-1.5">Prev</a>
            @endif

            @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                <a href="{{ $url }}" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-sm font-medium {{ $page === $paginator->currentPage() ? 'bg-charcoal text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    {{ $page }}
                </a>
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="admin-btn-ghost !px-3 !py-1.5">Next</a>
            @else
                <span class="admin-btn-ghost !px-3 !py-1.5 opacity-40">Next</span>
            @endif
        </div>
    </div>
@endif

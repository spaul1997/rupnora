@extends('admin.layouts.app')

@section('title', 'Visitor Tracking')

@section('content')
    <x-admin.page-header title="Visitor Tracking" description="Monitor page loads and product visits recorded after the storefront finishes loading." />

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card label="Total Visits" :value="number_format($summary['total'])" />
        <x-admin.stat-card label="Unique IP Addresses" :value="number_format($summary['unique_visitors'])" tone="info" />
        <x-admin.stat-card label="General Page Visits" :value="number_format($summary['page_visits'])" :href="route('admin.visitor-tracking.index', ['type' => 'pages'])" />
        <x-admin.stat-card label="Product Visits" :value="number_format($summary['product_visits'])" tone="success" :href="route('admin.visitor-tracking.index', ['type' => 'products'])" />
    </div>

    <form method="GET" class="admin-card mb-6 flex flex-wrap items-center gap-3 p-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search page, product, IP, browser..." class="admin-input max-w-xs">
        <select name="type" class="admin-select max-w-[170px]">
            <option value="all" @selected(request('type', 'all') === 'all')>All Visits</option>
            <option value="pages" @selected(request('type') === 'pages')>General Pages</option>
            <option value="products" @selected(request('type') === 'products')>Product Pages</option>
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" aria-label="From date" class="admin-input max-w-[160px]">
        <input type="date" name="date_to" value="{{ request('date_to') }}" aria-label="To date" class="admin-input max-w-[160px]">
        <button type="submit" class="admin-btn-secondary">Filter</button>
        @if (request()->hasAny(['search', 'type', 'date_from', 'date_to']))
            <a href="{{ route('admin.visitor-tracking.index') }}" class="admin-btn-ghost">Clear</a>
        @endif
    </form>

    <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div>
            <h2 class="mb-3 text-sm font-semibold text-gray-900">Top Pages</h2>
            @if ($pageStats->isEmpty())
                <x-admin.empty-state title="No general page visits found" />
            @else
                <x-admin.table :headers="['Page', '!Visits', '!Unique IPs', 'Last Visit']">
                    @foreach ($pageStats as $page)
                        <tr>
                            <td class="max-w-xs">
                                <p class="truncate font-medium text-gray-800" title="{{ $page->page_title }}">{{ $page->page_title ?: $page->page_path }}</p>
                                <p class="truncate text-xs text-gray-400" title="{{ $page->page_path }}">{{ $page->page_path }}</p>
                            </td>
                            <td class="text-right font-medium text-gray-900">{{ number_format($page->visits_count) }}</td>
                            <td class="text-right">{{ number_format($page->unique_visitors_count) }}</td>
                            <td class="whitespace-nowrap text-gray-500">{{ \Illuminate\Support\Carbon::parse($page->last_visited_at)->format('d M Y, h:i A') }}</td>
                        </tr>
                    @endforeach
                </x-admin.table>
            @endif
        </div>

        <div>
            <h2 class="mb-3 text-sm font-semibold text-gray-900">Top Products</h2>
            @if ($productStats->isEmpty())
                <x-admin.empty-state title="No product visits found" />
            @else
                <x-admin.table :headers="['Product', '!Visits', '!Unique IPs', 'Last Visit']">
                    @foreach ($productStats as $stat)
                        <tr>
                            <td class="max-w-xs">
                                @if ($stat->product)
                                    <a href="{{ route('admin.products.edit', $stat->product) }}" class="block truncate font-medium text-champagne-dark hover:underline">{{ $stat->product->name }}</a>
                                    <p class="text-xs text-gray-400">{{ $stat->product->sku }}</p>
                                @else
                                    <span class="text-gray-500">Deleted product</span>
                                @endif
                            </td>
                            <td class="text-right font-medium text-gray-900">{{ number_format($stat->visits_count) }}</td>
                            <td class="text-right">{{ number_format($stat->unique_visitors_count) }}</td>
                            <td class="whitespace-nowrap text-gray-500">{{ \Illuminate\Support\Carbon::parse($stat->last_visited_at)->format('d M Y, h:i A') }}</td>
                        </tr>
                    @endforeach
                </x-admin.table>
            @endif
        </div>
    </div>

    <h2 class="mb-3 text-sm font-semibold text-gray-900">Visit Details</h2>
    @if ($visits->isEmpty())
        <x-admin.empty-state title="No visitor records found" />
    @else
        <x-admin.table :headers="['Date & Time', 'Page', 'Product', 'IP Address', 'Visitor', 'User Agent']">
            @foreach ($visits as $visit)
                <tr>
                    <td class="whitespace-nowrap text-gray-500">{{ $visit->visited_at->format('d M Y, h:i A') }}</td>
                    <td class="max-w-xs">
                        <p class="truncate font-medium text-gray-800" title="{{ $visit->page_title }}">{{ $visit->page_title ?: $visit->page_path }}</p>
                        <p class="truncate text-xs text-gray-400" title="{{ $visit->page_path }}">{{ $visit->page_path }}</p>
                    </td>
                    <td>
                        @if ($visit->product)
                            <a href="{{ route('admin.products.edit', $visit->product) }}" class="font-medium text-champagne-dark hover:underline">{{ $visit->product->name }}</a>
                        @else
                            <span class="text-gray-400">&mdash;</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap font-mono text-xs text-gray-600">{{ $visit->ip_address }}</td>
                    <td>
                        @if ($visit->user)
                            <p class="font-medium text-gray-700">{{ $visit->user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $visit->user->email }}</p>
                        @else
                            <span class="text-gray-500">Guest</span>
                        @endif
                    </td>
                    <td class="max-w-sm">
                        <p class="truncate text-xs text-gray-500" title="{{ $visit->user_agent }}">{{ $visit->user_agent ?: 'Unknown' }}</p>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$visits" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

@extends('admin.layouts.app')

@section('title', 'Home Banners')

@section('content')
    <x-admin.page-header title="Home Banners" description="Manage hero banners shown on the storefront home page.">
        <x-slot:actions>
            <a href="{{ route('admin.home-banners.create') }}" class="admin-btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                Add Banner
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($banners->isEmpty())
        <x-admin.empty-state title="No home banners found" description="Create a banner to replace the default home hero slides.">
            <x-slot:actions>
                <a href="{{ route('admin.home-banners.create') }}" class="admin-btn-primary">Add Banner</a>
            </x-slot:actions>
        </x-admin.empty-state>
    @else
        <x-admin.table :headers="['Banner', 'Sort Order', 'Status', 'Updated', '!Actions']">
            @foreach ($banners as $banner)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            @if ($banner->image_url)
                                <x-ui.optimized-image :src="$banner->image_url" alt="" sizes="64px" class="h-12 w-16 rounded-lg object-cover" />
                            @else
                                <span class="flex h-12 w-16 items-center justify-center rounded-lg bg-beige text-xs text-champagne-dark">Hero</span>
                            @endif
                            <div>
                                <p class="font-medium text-gray-900">{{ $banner->heading }}</p>
                                <p class="text-xs text-gray-400">{{ $banner->eyebrow ?: 'No eyebrow' }}</p>
                            </div>
                        </div>
                    </td>
                    <td>{{ $banner->sort_order }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.home-banners.toggle-active', $banner) }}">
                            @csrf @method('PATCH')
                            <button type="submit"><x-admin.status-badge :status="$banner->is_active ? 'active' : 'inactive'" /></button>
                        </form>
                    </td>
                    <td class="text-gray-400">{{ $banner->updated_at->format('d M Y') }}</td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.home-banners.edit', $banner) }}" class="text-sm font-medium text-champagne-dark hover:underline">Edit</a>
                            <x-admin.confirm-modal :action="route('admin.home-banners.destroy', $banner)" title="Delete Banner" :message="'Are you sure you want to delete this home banner?'" />
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-admin.pagination :paginator="$banners" />
            </x-slot:footer>
        </x-admin.table>
    @endif
@endsection

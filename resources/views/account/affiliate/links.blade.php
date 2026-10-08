<x-layouts.app title="Affiliate Referral Links">
    <x-account.shell active="affiliate">
        @include('account.affiliate._nav', ['active' => 'links'])
        <h1 class="font-display text-3xl">Referral Links</h1>
        <p class="mt-2 text-sm text-muted">Latest valid click wins for {{ config('affiliate.attribution_window_days') }} days. Affiliate coupons override link attribution.</p>
        <div class="mt-6 rounded-2xl border border-line p-5">
            <label class="text-xs font-medium uppercase text-muted">Store link</label>
            <div class="mt-2 flex gap-2"><input readonly value="{{ route('home', ['ref' => $profile->referral_code]) }}" class="input-luxe min-w-0 flex-1 text-sm"><button type="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)" class="btn-secondary">Copy</button></div>
        </div>
        <form method="GET" action="{{ route('account.affiliate.links') }}" class="mt-6 flex flex-col gap-2 rounded-2xl border border-line bg-paper p-3 sm:flex-row sm:items-center">
            <label for="affiliate-product-search" class="sr-only">Search products</label>
            <input id="affiliate-product-search" type="search" name="search" value="{{ request('search') }}" class="input-luxe min-w-0 py-2.5 sm:flex-1" placeholder="Search by product name or SKU">
            <label for="affiliate-parent-category" class="sr-only">Parent category</label>
            <select id="affiliate-parent-category" name="parent_category_id" class="input-luxe py-2.5 sm:w-56">
                <option value="">All categories</option>
                @foreach ($parentCategories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('parent_category_id') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary shrink-0 px-6 py-2.5">Filter</button>
            @if (request()->filled('search') || request()->filled('parent_category_id'))
                <a href="{{ route('account.affiliate.links') }}" class="btn-secondary shrink-0 px-6 py-2.5">Clear</a>
            @endif
        </form>
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            @forelse($products as $product)
                <div class="rounded-2xl border border-line p-4"><p class="font-medium">{{ $product->name }}</p><p class="mt-1 text-xs text-muted">SKU: {{ $product->sku }} · {{ $product->category?->parent?->name ?? $product->category?->name ?? 'Uncategorised' }}</p><div class="mt-3 flex gap-2"><input readonly value="{{ route('products.show', ['slug' => $product->slug, 'ref' => $profile->referral_code]) }}" class="input-luxe min-w-0 flex-1 text-xs"><button type="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)" class="btn-secondary !px-3">Copy</button></div></div>
            @empty
                <div class="rounded-2xl border border-line p-8 text-center text-sm text-muted sm:col-span-2">No products match the selected filters.</div>
            @endforelse
        </div>
        <div class="mt-6">{{ $products->links() }}</div>
    </x-account.shell>
</x-layouts.app>

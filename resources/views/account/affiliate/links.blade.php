<x-layouts.app title="Affiliate Referral Links">
    <x-account.shell active="affiliate">
        @include('account.affiliate._nav', ['active' => 'links'])
        <h1 class="font-display text-3xl">Referral Links</h1>
        <p class="mt-2 text-sm text-muted">Latest valid click wins for {{ config('affiliate.attribution_window_days') }} days. Affiliate coupons override link attribution.</p>
        <div class="mt-6 rounded-2xl border border-line p-5">
            <label class="text-xs font-medium uppercase text-muted">Store link</label>
            <div class="mt-2 flex gap-2"><input readonly value="{{ route('home', ['ref' => $profile->referral_code]) }}" class="input-luxe min-w-0 flex-1 text-sm"><button type="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)" class="btn-secondary">Copy</button></div>
        </div>
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            @foreach($products as $product)
                <div class="rounded-2xl border border-line p-4"><p class="font-medium">{{ $product->name }}</p><div class="mt-3 flex gap-2"><input readonly value="{{ route('products.show', ['slug' => $product->slug, 'ref' => $profile->referral_code]) }}" class="input-luxe min-w-0 flex-1 text-xs"><button type="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)" class="btn-secondary !px-3">Copy</button></div></div>
            @endforeach
        </div>
        <div class="mt-6">{{ $products->links() }}</div>
    </x-account.shell>
</x-layouts.app>

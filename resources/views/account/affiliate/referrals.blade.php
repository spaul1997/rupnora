<x-layouts.app title="Affiliate Referrals"><x-account.shell active="affiliate">
    @include('account.affiliate._nav', ['active' => 'referrals'])
    <h1 class="font-display text-3xl">Referrals</h1>
    <div class="mt-6 overflow-x-auto rounded-2xl border border-line"><table class="min-w-full text-left text-sm"><thead class="bg-ivory-soft text-xs uppercase text-muted"><tr><th class="p-4">Date</th><th class="p-4">Landing page</th><th class="p-4">Result</th></tr></thead><tbody class="divide-y divide-line">@forelse($clicks as $click)<tr><td class="whitespace-nowrap p-4">{{ $click->clicked_at->format('d M Y H:i') }}</td><td class="max-w-sm truncate p-4">{{ $click->product?->name ?? parse_url($click->landing_url, PHP_URL_PATH) }}</td><td class="p-4">{{ $click->attributedOrder ? 'Order attributed' : ($click->is_valid ? 'Clicked' : 'Invalid') }}</td></tr>@empty<tr><td colspan="3" class="p-6 text-center text-muted">No referrals yet.</td></tr>@endforelse</tbody></table></div>
    <div class="mt-6">{{ $clicks->links() }}</div>
</x-account.shell></x-layouts.app>

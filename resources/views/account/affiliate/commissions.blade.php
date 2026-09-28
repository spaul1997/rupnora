<x-layouts.app title="Affiliate Commissions"><x-account.shell active="affiliate">
    @include('account.affiliate._nav', ['active' => 'commissions'])
    <h1 class="font-display text-3xl">Commissions</h1>
    <div class="mt-6 overflow-x-auto rounded-2xl border border-line"><table class="min-w-full text-left text-sm"><thead class="bg-ivory-soft text-xs uppercase text-muted"><tr><th class="p-4">Order</th><th class="p-4">Eligible</th><th class="p-4">Rate</th><th class="p-4">Commission</th><th class="p-4">Status</th></tr></thead><tbody class="divide-y divide-line">@forelse($commissions as $row)<tr><td class="p-4">{{ $row->order->order_number }}</td><td class="p-4">₹{{ number_format($row->eligible_amount, 2) }}</td><td class="p-4">{{ number_format($row->commission_rate, 2) }}%</td><td class="p-4">₹{{ number_format($row->gross_amount - $row->reversed_amount, 2) }}</td><td class="p-4 capitalize">{{ str_replace('_', ' ', $row->status) }}</td></tr>@empty<tr><td colspan="5" class="p-6 text-center text-muted">No commissions yet.</td></tr>@endforelse</tbody></table></div>
    <div class="mt-6">{{ $commissions->links() }}</div>
</x-account.shell></x-layouts.app>

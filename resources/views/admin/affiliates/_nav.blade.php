<div class="mb-6 flex flex-wrap gap-2">
    @foreach ([
        ['Affiliates', route('admin.affiliates.index'), ['admin.affiliates.index', 'admin.affiliates.show']],
        ['Rate Rules', route('admin.affiliates.rules'), ['admin.affiliates.rules*']],
        ['Referrals', route('admin.affiliates.referrals'), ['admin.affiliates.referrals']],
        ['Commissions', route('admin.affiliates.commissions'), ['admin.affiliates.commissions']],
        ['Withdrawals', route('admin.affiliates.withdrawals'), ['admin.affiliates.withdrawals*']],
        ['Audit Log', route('admin.affiliates.audits'), ['admin.affiliates.audits']],
    ] as [$label, $url, $routes])
        <a href="{{ $url }}" class="rounded-lg border px-3 py-2 text-xs font-medium {{ request()->routeIs(...$routes) ? 'border-charcoal bg-charcoal text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-400' }}">{{ $label }}</a>
    @endforeach
    <a href="{{ route('admin.affiliates.ledger.export') }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600">Export Ledger CSV</a>
</div>

<nav class="mb-7 flex gap-2 overflow-x-auto pb-1" aria-label="Affiliate navigation">
    @foreach ([
        ['dashboard', 'Overview', route('account.affiliate.dashboard')],
        ['links', 'Referral Links', route('account.affiliate.links')],
        ['referrals', 'Referrals', route('account.affiliate.referrals')],
        ['commissions', 'Commissions', route('account.affiliate.commissions')],
        ['payout', 'Payout Details', route('account.affiliate.payout')],
        ['withdrawals', 'Withdrawals', route('account.affiliate.withdrawals')],
    ] as [$key, $label, $url])
        <a href="{{ $url }}" class="flex-shrink-0 rounded-full border px-4 py-2 text-xs font-medium {{ ($active ?? '') === $key ? 'border-charcoal bg-charcoal text-ivory' : 'border-line text-charcoal hover:border-charcoal' }}">{{ $label }}</a>
    @endforeach
</nav>

<x-layouts.app :title="$title">
    <x-account.shell active="affiliate">
        <h1 class="font-display text-3xl text-charcoal">Affiliate Program</h1>
        <p class="mt-2 text-sm text-muted">Earn commission from verified purchases made through your referral links and coupons.</p>

        @if (! $profile)
            <form method="POST" action="{{ route('account.affiliate.apply') }}" class="mt-8 max-w-2xl space-y-5 rounded-2xl border border-line bg-paper p-6">
                @csrf
                <h2 class="font-display text-xl text-charcoal">Apply to become an affiliate</h2>
                <div><label class="mb-1 block text-sm font-medium">Why would you like to join? <span class="text-error">*</span></label><textarea name="application_message" rows="5" required minlength="30" maxlength="3000" class="input-luxe w-full">{{ old('application_message') }}</textarea></div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="mb-1 block text-sm font-medium">Website URL</label><input name="website_url" type="url" value="{{ old('website_url') }}" class="input-luxe w-full"></div>
                    <div><label class="mb-1 block text-sm font-medium">Social profile URL</label><input name="social_url" type="url" value="{{ old('social_url') }}" class="input-luxe w-full"></div>
                </div>
                <div><label class="mb-1 block text-sm font-medium">Audience summary</label><input name="audience_summary" value="{{ old('audience_summary') }}" maxlength="255" class="input-luxe w-full"></div>
                <button class="btn-primary">Submit application</button>
            </form>
        @elseif (! $profile->isApproved())
            <div class="mt-8 rounded-2xl border border-line bg-paper p-6">
                <span class="rounded-full bg-beige px-3 py-1 text-xs font-semibold uppercase">{{ $profile->status }}</span>
                <h2 class="font-display mt-4 text-xl">Application {{ $profile->status }}</h2>
                <p class="mt-2 text-sm text-muted">Submitted {{ $profile->applied_at->format('d M Y') }}. We will notify you when an administrator updates the review.</p>
                @if ($profile->admin_notes)<p class="mt-3 rounded-xl bg-ivory-soft p-3 text-sm">{{ $profile->admin_notes }}</p>@endif
            </div>
        @else
            @include('account.affiliate._nav', ['active' => 'dashboard'])
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                @foreach (['pending' => 'Pending', 'available' => 'Available', 'reserved' => 'Reserved', 'paid' => 'Paid', 'reversed' => 'Reversed'] as $key => $label)
                    <div class="rounded-2xl border border-line p-5"><p class="text-xs text-muted">{{ $label }}</p><p class="font-display mt-2 text-2xl">₹{{ number_format($wallet[$key], 2) }}</p></div>
                @endforeach
            </div>
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-charcoal p-5 text-ivory"><p class="text-xs text-ivory/60">Referral code</p><p class="mt-2 text-xl font-semibold tracking-wider">{{ $profile->referral_code }}</p></div>
                <div class="rounded-2xl border border-line p-5"><p class="text-xs text-muted">Recorded clicks</p><p class="font-display mt-2 text-2xl">{{ number_format($clicks) }}</p></div>
                <div class="rounded-2xl border border-line p-5"><p class="text-xs text-muted">Attributed orders</p><p class="font-display mt-2 text-2xl">{{ number_format($orders) }}</p></div>
            </div>
            <section class="mt-8" aria-labelledby="commission-rate-chart-heading">
                <div class="mb-3">
                    <h2 id="commission-rate-chart-heading" class="font-display text-xl text-charcoal">Your Commission Rate Chart</h2>
                    <p class="mt-1 text-sm text-muted">
                        @if ($profile->commission_rate !== null)
                            Your affiliate-specific rate overrides all shared commission rules.
                        @else
                            Product rates apply first, followed by category and Global rates.
                        @endif
                    </p>
                    <p class="mt-2 inline-flex items-start gap-1.5 rounded-full bg-error/10 px-3 py-1.5 text-xs font-medium leading-4 text-error">
                        <span aria-hidden="true">*</span>
                        <span>Commission becomes available after the return/refund period ends for returnable products, and after delivery for non-returnable products.</span>
                    </p>
                </div>
                <div class="overflow-x-auto rounded-2xl border border-line bg-paper">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-ivory-soft text-xs uppercase text-muted"><tr><th class="p-4">Scope</th><th class="p-4">Applies to</th><th class="p-4">Availability</th><th class="p-4 text-right">Rate</th></tr></thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($rateChart as $rate)
                                <tr><td class="p-4 font-medium text-charcoal">{{ $rate['scope'] }}</td><td class="p-4">{{ $rate['target'] }}</td><td class="p-4 text-muted">{{ $rate['availability'] }}</td><td class="p-4 text-right font-semibold text-charcoal">{{ number_format($rate['rate'], 2) }}%</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
            <div class="mt-8 overflow-x-auto rounded-2xl border border-line">
                <table class="min-w-full text-left text-sm"><thead class="bg-ivory-soft text-xs uppercase text-muted"><tr><th class="p-4">Order</th><th class="p-4">Status</th><th class="p-4 text-right">Commission</th></tr></thead><tbody class="divide-y divide-line">
                    @forelse($recentCommissions as $commission)<tr><td class="p-4">{{ $commission->order->order_number }}</td><td class="p-4 capitalize">{{ str_replace('_', ' ', $commission->status) }}</td><td class="p-4 text-right">₹{{ number_format($commission->gross_amount, 2) }}</td></tr>@empty<tr><td colspan="3" class="p-6 text-center text-muted">No commissions yet.</td></tr>@endforelse
                </tbody></table>
            </div>
        @endif
    </x-account.shell>
</x-layouts.app>

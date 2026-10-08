<x-layouts.app title="Affiliate Payout Details"><x-account.shell active="affiliate">
    @include('account.affiliate._nav', ['active' => 'payout'])
    <h1 class="font-display text-3xl">Payout Details</h1>
    <p class="mt-2 text-sm text-muted">Sensitive values are encrypted in the database. Saving changes resets verification.</p>

    <div class="mt-6 max-w-2xl rounded-2xl border border-line bg-surface p-5">
        <h2 class="font-display text-lg">Complete your payout profile</h2>
        <p class="mt-1 text-sm text-muted">A mobile number and at least one saved address are required before you can add payout details.</p>

        <div class="mt-4 divide-y divide-line rounded-xl border border-line">
            <div class="flex flex-wrap items-center justify-between gap-3 p-4">
                <div>
                    <p class="text-sm font-semibold text-ink">Mobile number</p>
                    <p class="mt-0.5 text-xs text-muted">Add it to your account profile.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $requirements['has_phone'] ? 'bg-success/10 text-success' : 'bg-error/10 text-error' }}">
                        {{ $requirements['has_phone'] ? 'Complete' : 'Required' }}
                    </span>
                    <a href="{{ route('account.profile') }}" class="text-sm font-semibold text-primary hover:underline">Update profile</a>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 p-4">
                <div>
                    <p class="text-sm font-semibold text-ink">Saved address</p>
                    <p class="mt-0.5 text-xs text-muted">Keep at least one address in your address book.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $requirements['has_address'] ? 'bg-success/10 text-success' : 'bg-error/10 text-error' }}">
                        {{ $requirements['has_address'] ? 'Complete' : 'Required' }}
                    </span>
                    <a href="{{ route('account.addresses') }}" class="text-sm font-semibold text-primary hover:underline">{{ $requirements['has_address'] ? 'Manage addresses' : 'Add address' }}</a>
                </div>
            </div>
        </div>
    </div>

    @if($requirements['complete'])
    <form method="POST" action="{{ route('account.affiliate.payout.update') }}" x-data="{ method: '{{ old('payout_method', $account?->payout_method ?? 'bank') }}' }" class="mt-6 max-w-2xl space-y-5 rounded-2xl border border-line p-6">@csrf @method('PUT')
        <div><label class="mb-1 block text-sm font-medium">Payout method</label><select name="payout_method" x-model="method" class="input-luxe w-full"><option value="bank">Bank transfer</option><option value="upi">UPI</option></select></div>
        <div x-show="method === 'bank'" class="space-y-4">
            <div><label class="mb-1 block text-sm font-medium">Account holder</label><input name="account_holder_name" value="{{ old('account_holder_name', $account?->account_holder_name) }}" class="input-luxe w-full"></div>
            <div><label class="mb-1 block text-sm font-medium">Bank name</label><input name="bank_name" value="{{ old('bank_name', $account?->bank_name) }}" class="input-luxe w-full"></div>
            <div><label class="mb-1 block text-sm font-medium">Account number</label><input name="account_number" value="{{ old('account_number', $account?->account_number) }}" autocomplete="off" class="input-luxe w-full"></div>
            <div><label class="mb-1 block text-sm font-medium">IFSC</label><input name="ifsc" value="{{ old('ifsc', $account?->ifsc) }}" class="input-luxe w-full"></div>
        </div>
        <div x-show="method === 'upi'"><label class="mb-1 block text-sm font-medium">UPI ID</label><input name="upi_id" value="{{ old('upi_id', $account?->upi_id) }}" class="input-luxe w-full"></div>
        @if($account)<p class="text-xs {{ $account->is_verified ? 'text-success' : 'text-muted' }}">{{ $account->is_verified ? 'Verified by admin' : 'Awaiting admin verification' }}</p>@endif
        <button class="btn-primary">Save payout details</button>
    </form>
    @else
        <div class="mt-6 max-w-2xl rounded-2xl border border-warning/30 bg-warning/10 p-4 text-sm text-ink">
            Complete the required profile information above to unlock payout details.
        </div>
    @endif
</x-account.shell></x-layouts.app>

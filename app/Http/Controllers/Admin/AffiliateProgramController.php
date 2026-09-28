<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateAuditLog;
use App\Models\AffiliateCommission;
use App\Models\AffiliateCommissionRule;
use App\Models\AffiliateLedgerEntry;
use App\Models\AffiliateProfile;
use App\Models\AffiliateReferralClick;
use App\Models\AffiliateWithdrawal;
use App\Models\Category;
use App\Models\Product;
use App\Notifications\AffiliateActivity;
use App\Services\AffiliateWithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AffiliateProgramController extends Controller
{
    public function index(Request $request): View
    {
        $affiliates = AffiliateProfile::query()->with('user')
            ->withCount(['clicks', 'orders'])
            ->when($request->status, fn ($query, $value) => $query->where('status', $value))
            ->when($request->search, fn ($query, $value) => $query->whereHas('user', fn ($q) => $q
                ->where('name', 'like', "%{$value}%")->orWhere('email', 'like', "%{$value}%")))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.affiliates.index', [
            'affiliates' => $affiliates,
            'counts' => AffiliateProfile::query()->selectRaw('status, COUNT(*) total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(AffiliateProfile $affiliate): View
    {
        $affiliate->load(['user', 'payoutAccount', 'approver']);

        return view('admin.affiliates.show', [
            'affiliate' => $affiliate, 'wallet' => $affiliate->wallet(),
            'recentOrders' => $affiliate->orders()->latest()->limit(10)->get(),
            'recentLedger' => $affiliate->ledgerEntries()->latest('occurred_at')->limit(15)->get(),
        ]);
    }

    public function updateStatus(Request $request, AffiliateProfile $affiliate): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(AffiliateProfile::STATUSES)],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'admin_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        DB::transaction(function () use ($request, $affiliate, $data) {
            $from = $affiliate->status;
            $dates = ['approved_at' => null, 'rejected_at' => null, 'suspended_at' => null];
            if ($data['status'] === 'approved') $dates['approved_at'] = now();
            if ($data['status'] === 'rejected') $dates['rejected_at'] = now();
            if ($data['status'] === 'suspended') $dates['suspended_at'] = now();
            $affiliate->update([
                ...$data, ...$dates,
                'approved_by' => $data['status'] === 'approved' ? $request->user()->id : $affiliate->approved_by,
            ]);
            AffiliateAuditLog::create([
                'affiliate_id' => $affiliate->id, 'actor_id' => $request->user()->id,
                'event' => 'affiliate_status_changed', 'status_from' => $from, 'status_to' => $data['status'],
                'metadata' => ['commission_rate' => $data['commission_rate'] ?? null], 'created_at' => now(),
            ]);
        });

        $affiliate->user->notify(new AffiliateActivity(
            'Affiliate application '.str_replace('_', ' ', $affiliate->status),
            'Your affiliate account is now '.$affiliate->status.'.',
            route('account.affiliate.dashboard')
        ));

        return back()->with('success', 'Affiliate status updated.');
    }

    public function rules(): View
    {
        return view('admin.affiliates.rules', [
            'rules' => AffiliateCommissionRule::with(['product', 'category'])->latest()->paginate(30),
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeRule(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'scope_type' => ['required', Rule::in(AffiliateCommissionRule::SCOPE_TYPES)],
            'product_id' => ['nullable', 'required_if:scope_type,product', 'exists:products,id'],
            'category_id' => ['nullable', 'required_if:scope_type,category', 'exists:categories,id'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $key = match ($data['scope_type']) {
            'product' => 'product:'.$data['product_id'],
            'category' => 'category:'.$data['category_id'],
            default => 'global',
        };
        AffiliateCommissionRule::updateOrCreate(['scope_key' => $key], [
            ...$data,
            'product_id' => $data['scope_type'] === 'product' ? $data['product_id'] : null,
            'category_id' => $data['scope_type'] === 'category' ? $data['category_id'] : null,
            'is_active' => $request->boolean('is_active'), 'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Commission rule saved. New orders will use it; existing snapshots are unchanged.');
    }

    public function destroyRule(AffiliateCommissionRule $rule): RedirectResponse
    {
        $rule->delete();

        return back()->with('success', 'Commission rule deleted. Existing orders keep their frozen rates.');
    }

    public function referrals(Request $request): View
    {
        $clicks = AffiliateReferralClick::with(['affiliate.user', 'product', 'attributedOrder'])
            ->when($request->boolean('flagged'), fn ($query) => $query->where('is_suspicious', true))
            ->latest('clicked_at')->paginate(30)->withQueryString();

        return view('admin.affiliates.referrals', compact('clicks'));
    }

    public function commissions(Request $request): View
    {
        $commissions = AffiliateCommission::with(['affiliate.user', 'order', 'orderItem'])
            ->when($request->status, fn ($query, $value) => $query->where('status', $value))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.affiliates.commissions', compact('commissions'));
    }

    public function withdrawals(Request $request): View
    {
        $withdrawals = AffiliateWithdrawal::with(['affiliate.user', 'payoutAccount'])
            ->when($request->status, fn ($query, $value) => $query->where('status', $value))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.affiliates.withdrawals', compact('withdrawals'));
    }

    public function updateWithdrawal(Request $request, AffiliateWithdrawal $withdrawal, AffiliateWithdrawalService $service): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(AffiliateWithdrawal::STATUSES)],
            'transfer_reference' => ['nullable', 'string', 'max:255', Rule::unique('affiliate_withdrawals')->ignore($withdrawal->id)],
            'admin_notes' => ['nullable', 'string', 'max:3000'],
            'failure_reason' => ['nullable', 'string', 'max:1000'],
        ]);
        $withdrawal = $service->transition($withdrawal, $data['status'], $request->user(), $data);
        $withdrawal->affiliate->user->notify(new AffiliateActivity(
            'Withdrawal '.str_replace('_', ' ', $withdrawal->status),
            'Withdrawal '.$withdrawal->request_reference.' is now '.$withdrawal->status.'.',
            route('account.affiliate.withdrawals')
        ));

        return back()->with('success', 'Withdrawal status updated.');
    }

    public function verifyPayoutAccount(Request $request, AffiliateProfile $affiliate): RedirectResponse
    {
        abort_unless($affiliate->payoutAccount, 404);
        $affiliate->payoutAccount->update(['is_verified' => $request->boolean('is_verified')]);
        AffiliateAuditLog::create([
            'affiliate_id' => $affiliate->id, 'actor_id' => $request->user()->id,
            'event' => $request->boolean('is_verified') ? 'payout_account_verified' : 'payout_account_unverified',
            'created_at' => now(),
        ]);

        return back()->with('success', 'Payout verification updated.');
    }

    public function audits(): View
    {
        return view('admin.affiliates.audits', [
            'audits' => AffiliateAuditLog::with(['affiliate.user', 'actor', 'withdrawal'])->latest()->paginate(40),
        ]);
    }

    public function exportLedger(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Affiliate', 'Email', 'Type', 'Reference', 'Pending', 'Available', 'Reserved', 'Paid', 'Reversed', 'Gross', 'Deduction', 'Net']);
            AffiliateLedgerEntry::with('affiliate.user')->orderBy('id')->chunkById(500, function ($entries) use ($handle) {
                foreach ($entries as $entry) {
                    fputcsv($handle, [
                        $entry->occurred_at, $entry->affiliate->user->name, $entry->affiliate->user->email,
                        $entry->type, $entry->reference, $entry->pending_delta, $entry->available_delta,
                        $entry->reserved_delta, $entry->paid_delta, $entry->reversed_delta,
                        $entry->gross_amount, $entry->deduction_amount, $entry->net_amount,
                    ]);
                }
            });
            fclose($handle);
        }, 'affiliate-ledger-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv']);
    }
}

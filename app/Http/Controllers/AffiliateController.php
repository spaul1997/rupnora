<?php

namespace App\Http\Controllers;

use App\Models\AffiliateAuditLog;
use App\Models\AffiliateProfile;
use App\Models\AffiliatePayoutAccount;
use App\Models\Product;
use App\Models\User;
use App\Notifications\AffiliateActivity;
use App\Services\AffiliateWithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    public function dashboard(Request $request): View
    {
        $profile = $request->user()->affiliateProfile;
        $wallet = $profile?->wallet() ?? ['pending' => 0, 'available' => 0, 'reserved' => 0, 'paid' => 0, 'reversed' => 0];

        return view('account.affiliate.dashboard', [
            'title' => 'Affiliate Program', 'profile' => $profile, 'wallet' => $wallet,
            'clicks' => $profile?->clicks()->count() ?? 0,
            'orders' => $profile?->orders()->where('affiliate_flagged', false)->count() ?? 0,
            'recentCommissions' => $profile?->commissions()->with('order')->latest()->limit(8)->get() ?? collect(),
        ]);
    }

    public function apply(Request $request): RedirectResponse
    {
        abort_if($request->user()->affiliateProfile, 409, 'An affiliate application already exists.');
        $data = $request->validate([
            'application_message' => ['required', 'string', 'min:30', 'max:3000'],
            'website_url' => ['nullable', 'url', 'max:500'],
            'social_url' => ['nullable', 'url', 'max:500'],
            'audience_summary' => ['nullable', 'string', 'max:255'],
        ]);

        $profile = DB::transaction(function () use ($request, $data) {
            $profile = AffiliateProfile::create([
                ...$data, 'user_id' => $request->user()->id, 'status' => 'pending',
                'referral_code' => AffiliateProfile::generateReferralCode($request->user()), 'applied_at' => now(),
            ]);
            AffiliateAuditLog::create([
                'affiliate_id' => $profile->id, 'actor_id' => $request->user()->id,
                'event' => 'application_submitted', 'status_to' => 'pending', 'created_at' => now(),
            ]);

            return $profile;
        });

        Notification::send(User::admins()->where('is_active', true)->get(), new AffiliateActivity(
            'New affiliate application', $request->user()->name.' submitted an affiliate application.', route('admin.affiliates.show', $profile)
        ));

        return back()->with('success', 'Your affiliate application has been submitted for review.');
    }

    public function links(Request $request): View
    {
        $profile = $this->approvedProfile($request);
        $products = Product::active()->latest()->paginate(20);

        return view('account.affiliate.links', compact('profile', 'products'));
    }

    public function referrals(Request $request): View
    {
        $profile = $this->approvedProfile($request);
        $clicks = $profile->clicks()->with(['product', 'attributedOrder'])->latest('clicked_at')->paginate(25);

        return view('account.affiliate.referrals', compact('profile', 'clicks'));
    }

    public function commissions(Request $request): View
    {
        $profile = $this->approvedProfile($request);
        $commissions = $profile->commissions()->with(['order', 'orderItem'])->latest()->paginate(25);

        return view('account.affiliate.commissions', compact('profile', 'commissions'));
    }

    public function payout(Request $request): View
    {
        $profile = $this->approvedProfile($request);

        return view('account.affiliate.payout', ['profile' => $profile, 'account' => $profile->payoutAccount]);
    }

    public function updatePayout(Request $request): RedirectResponse
    {
        $profile = $this->approvedProfile($request);
        $data = $request->validate([
            'payout_method' => ['required', Rule::in(['bank', 'upi'])],
            'account_holder_name' => ['required_if:payout_method,bank', 'nullable', 'string', 'max:150'],
            'bank_name' => ['required_if:payout_method,bank', 'nullable', 'string', 'max:150'],
            'account_number' => ['required_if:payout_method,bank', 'nullable', 'string', 'min:6', 'max:40'],
            'ifsc' => ['required_if:payout_method,bank', 'nullable', 'regex:/^[A-Za-z]{4}0[A-Za-z0-9]{6}$/'],
            'upi_id' => ['required_if:payout_method,upi', 'nullable', 'string', 'regex:/^[A-Za-z0-9.\-_]{2,}@[A-Za-z]{2,}$/', 'max:100'],
        ]);

        $number = preg_replace('/\s+/', '', (string) ($data['account_number'] ?? ''));
        AffiliatePayoutAccount::updateOrCreate(['affiliate_id' => $profile->id], [
            ...$data,
            'account_number' => $number ?: null,
            'ifsc' => isset($data['ifsc']) ? strtoupper($data['ifsc']) : null,
            'account_last_four' => $number ? substr($number, -4) : null,
            'is_verified' => false,
        ]);
        AffiliateAuditLog::create([
            'affiliate_id' => $profile->id, 'actor_id' => $request->user()->id,
            'event' => 'payout_account_updated', 'metadata' => ['method' => $data['payout_method']], 'created_at' => now(),
        ]);

        return back()->with('success', 'Payout details saved securely. They will be reviewed before payment.');
    }

    public function withdrawals(Request $request): View
    {
        $profile = $this->approvedProfile($request);

        return view('account.affiliate.withdrawals', [
            'profile' => $profile, 'wallet' => $profile->wallet(),
            'withdrawals' => $profile->withdrawals()->latest()->paginate(20),
            'requestKey' => (string) str()->uuid(),
        ]);
    }

    public function requestWithdrawal(Request $request, AffiliateWithdrawalService $service): RedirectResponse
    {
        $profile = $this->approvedProfile($request);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'idempotency_key' => ['required', 'uuid'],
        ]);
        $withdrawal = $service->request($profile, (float) $data['amount'], $data['idempotency_key']);
        Notification::send(User::admins()->where('is_active', true)->get(), new AffiliateActivity(
            'Affiliate withdrawal requested',
            $request->user()->name.' requested ₹'.number_format((float) $withdrawal->gross_amount, 2).'.',
            route('admin.affiliates.withdrawals')
        ));

        return back()->with('success', 'Withdrawal '.$withdrawal->request_reference.' submitted.');
    }

    private function approvedProfile(Request $request): AffiliateProfile
    {
        $profile = $request->user()->affiliateProfile;
        abort_unless($profile?->isApproved(), 403, 'Your affiliate account is not approved.');

        return $profile;
    }
}

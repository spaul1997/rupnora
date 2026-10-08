<?php

namespace App\Http\Controllers;

use App\Mail\AffiliateApplicationMail;
use App\Mail\AffiliateWithdrawalMail;
use App\Models\AffiliateAuditLog;
use App\Models\AffiliateCommissionRule;
use App\Models\AffiliateProfile;
use App\Models\AffiliatePayoutAccount;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Notifications\AffiliateActivity;
use App\Services\AffiliateWithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
            'rateChart' => $profile?->isApproved() ? $this->rateChart($profile) : collect(),
        ]);
    }

    /** @return Collection<int, array{scope: string, target: string, rate: float, availability: string}> */
    private function rateChart(AffiliateProfile $profile): Collection
    {
        if ($profile->commission_rate !== null) {
            return collect([[
                'scope' => 'Affiliate-specific',
                'target' => 'All products',
                'rate' => (float) $profile->commission_rate,
                'availability' => 'Always',
            ]]);
        }

        $rules = AffiliateCommissionRule::query()
            ->activeAt()
            ->with(['product:id,name', 'category:id,name'])
            ->get()
            ->sortBy(fn (AffiliateCommissionRule $rule) => match ($rule->scope_type) {
                'product' => 1,
                'category' => 2,
                default => 3,
            })
            ->values();

        $chart = $rules->map(fn (AffiliateCommissionRule $rule) => [
            'scope' => ucfirst($rule->scope_type),
            'target' => $rule->product?->name ?? $rule->category?->name ?? 'All products',
            'rate' => (float) $rule->rate,
            'availability' => match (true) {
                $rule->starts_at === null && $rule->ends_at === null => 'Always',
                $rule->starts_at === null => 'Until '.$rule->ends_at->format('d M Y'),
                $rule->ends_at === null => 'From '.$rule->starts_at->format('d M Y'),
                default => $rule->starts_at->format('d M Y').' – '.$rule->ends_at->format('d M Y'),
            },
        ]);

        if (! $rules->contains('scope_type', 'global')) {
            $chart->push([
                'scope' => 'Global default',
                'target' => 'All products',
                'rate' => (float) config('affiliate.global_commission_rate', 5),
                'availability' => 'Always',
            ]);
        }

        return $chart;
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

        try {
            $pendingMail = Mail::to($request->user()->email);
            $ccEmail = trim((string) config('marketing.cc_email'));

            if ($ccEmail !== '' && strcasecmp($ccEmail, $request->user()->email) !== 0) {
                $pendingMail->cc($ccEmail);
            }

            $pendingMail->send(new AffiliateApplicationMail($profile));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Your affiliate application has been submitted for review.');
    }

    public function links(Request $request): View
    {
        $profile = $this->approvedProfile($request);
        $search = trim((string) $request->query('search', ''));
        $parentCategoryId = $request->integer('parent_category_id');
        $products = Product::query()
            ->active()
            ->with('category.parent')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($parentCategoryId > 0, function ($query) use ($parentCategoryId) {
                $query->where(function ($query) use ($parentCategoryId) {
                    $query->where('category_id', $parentCategoryId)
                        ->orWhereHas('category', fn ($query) => $query->where('parent_id', $parentCategoryId));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $parentCategories = Category::query()->active()->parents()->orderBy('name')->get(['id', 'name']);

        return view('account.affiliate.links', compact('profile', 'products', 'parentCategories'));
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
        $requirements = $this->payoutRequirements($request->user());

        return view('account.affiliate.payout', [
            'profile' => $profile,
            'account' => $profile->payoutAccount,
            'requirements' => $requirements,
        ]);
    }

    public function updatePayout(Request $request): RedirectResponse
    {
        $profile = $this->approvedProfile($request);
        $requirements = $this->payoutRequirements($request->user());

        if (! $requirements['complete']) {
            $errors = [];

            if (! $requirements['has_phone']) {
                $errors['phone'] = 'Add a mobile number to your profile before saving payout details.';
            }

            if (! $requirements['has_address']) {
                $errors['address'] = 'Add at least one saved address before saving payout details.';
            }

            throw ValidationException::withMessages($errors);
        }

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

    /** @return array{has_phone: bool, has_address: bool, complete: bool} */
    private function payoutRequirements(User $user): array
    {
        $hasPhone = filled(trim((string) $user->phone));
        $hasAddress = $user->addresses()->exists();

        return [
            'has_phone' => $hasPhone,
            'has_address' => $hasAddress,
            'complete' => $hasPhone && $hasAddress,
        ];
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

        if ($withdrawal->wasRecentlyCreated) {
            Notification::send(User::admins()->where('is_active', true)->get(), new AffiliateActivity(
                'Affiliate withdrawal requested',
                $request->user()->name.' requested ₹'.number_format((float) $withdrawal->gross_amount, 2).'.',
                route('admin.affiliates.withdrawals')
            ));

            try {
                $pendingMail = Mail::to($request->user()->email);
                $ccEmail = trim((string) config('marketing.cc_email'));

                if ($ccEmail !== '' && strcasecmp($ccEmail, $request->user()->email) !== 0) {
                    $pendingMail->cc($ccEmail);
                }

                $pendingMail->send(new AffiliateWithdrawalMail($withdrawal, 'requested'));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return back()->with('success', 'Withdrawal '.$withdrawal->request_reference.' submitted.');
    }

    private function approvedProfile(Request $request): AffiliateProfile
    {
        $profile = $request->user()->affiliateProfile;
        abort_unless($profile?->isApproved(), 403, 'Your affiliate account is not approved.');

        return $profile;
    }
}

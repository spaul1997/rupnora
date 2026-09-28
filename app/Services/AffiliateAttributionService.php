<?php

namespace App\Services;

use App\Models\AffiliateProfile;
use App\Models\AffiliateReferralClick;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class AffiliateAttributionService
{
    public const SESSION_CLICK_KEY = 'affiliate.latest_click_id';

    public function capture(Request $request): ?AffiliateReferralClick
    {
        $code = strtoupper(trim((string) $request->query('ref')));
        if ($code === '') {
            return null;
        }

        $affiliate = AffiliateProfile::query()->with('user')->where('referral_code', $code)->first();
        if (! $affiliate) {
            return null;
        }

        $selfReferral = $request->user()?->id === $affiliate->user_id;
        $valid = $affiliate->isApproved() && ! $selfReferral;
        $product = $request->route('slug')
            ? Product::query()->where('slug', $request->route('slug'))->first()
            : null;
        $visitorHash = hash('sha256', implode('|', [
            (string) $request->ip(),
            (string) $request->userAgent(),
            (string) config('app.key'),
        ]));

        $click = AffiliateReferralClick::create([
            'affiliate_id' => $affiliate->id,
            'user_id' => $request->user()?->id,
            'product_id' => $product?->id,
            'referral_code' => $affiliate->referral_code,
            'session_hash' => hash('sha256', $request->session()->getId()),
            'visitor_hash' => $visitorHash,
            'landing_url' => mb_substr($request->fullUrl(), 0, 1000),
            'referrer_url' => $request->headers->get('referer') ? mb_substr($request->headers->get('referer'), 0, 1000) : null,
            'is_valid' => $valid,
            'is_suspicious' => $selfReferral,
            'suspicious_reason' => $selfReferral ? 'Affiliate opened their own referral link while signed in.' : null,
            'clicked_at' => now(),
            'expires_at' => now()->addDays((int) config('affiliate.attribution_window_days', 30)),
        ]);

        if ($valid) {
            $request->session()->put(self::SESSION_CLICK_KEY, $click->id);
        }

        return $click;
    }

    /** @return array{affiliate: ?AffiliateProfile, click: ?AffiliateReferralClick, coupon: ?Coupon, source: ?string, flagged: bool, reason: ?string} */
    public function resolve(User $customer, ?Coupon $coupon): array
    {
        $click = null;
        $affiliate = null;
        $source = null;
        $statusReason = null;

        if ($coupon?->affiliate_id) {
            $candidate = $coupon->affiliate()->with('user')->first();
            if ($candidate) {
                $affiliate = $candidate;
                $source = 'affiliate_coupon';
                if (! $candidate->isApproved()) {
                    $statusReason = 'Affiliate coupon owner is not currently approved.';
                }
            }
        }

        if (! $affiliate) {
            $click = AffiliateReferralClick::query()
                ->with('affiliate.user')
                ->whereKey(session(self::SESSION_CLICK_KEY))
                ->where('is_valid', true)
                ->where('expires_at', '>=', now())
                ->first();
            if ($click?->affiliate?->isApproved()) {
                $affiliate = $click->affiliate;
                $source = 'last_click';
            }
        }

        if (! $affiliate) {
            return ['affiliate' => null, 'click' => $click, 'coupon' => $coupon, 'source' => null, 'flagged' => false, 'reason' => null];
        }

        $reason = $statusReason ?? $this->selfReferralReason($customer, $affiliate->user);
        if (! $reason && $click && AffiliateReferralClick::query()
            ->where('affiliate_id', $affiliate->id)
            ->where('user_id', $affiliate->user_id)
            ->where('is_suspicious', true)
            ->where(fn ($query) => $query->where('visitor_hash', $click->visitor_hash)
                ->orWhere('session_hash', $click->session_hash))
            ->exists()) {
            $reason = 'This device or session was previously used by the affiliate on their own link.';
        }

        return [
            'affiliate' => $affiliate,
            'click' => $click,
            'coupon' => $coupon,
            'source' => $source,
            'flagged' => $reason !== null,
            'reason' => $reason,
        ];
    }

    public function attachOrder(array $attribution, Order $order): void
    {
        if ($attribution['click']) {
            $attribution['click']->update([
                'attributed_order_id' => $order->id,
                'is_suspicious' => $attribution['flagged'] || $attribution['click']->is_suspicious,
                'suspicious_reason' => $attribution['reason'] ?: $attribution['click']->suspicious_reason,
            ]);
        }
    }

    private function selfReferralReason(User $customer, User $owner): ?string
    {
        if ($customer->id === $owner->id) {
            return 'Affiliate and customer account are the same.';
        }

        if (mb_strtolower(trim($customer->email)) === mb_strtolower(trim($owner->email))) {
            return 'Affiliate and customer email addresses match.';
        }

        $customerPhone = preg_replace('/\D+/', '', (string) $customer->phone);
        $ownerPhone = preg_replace('/\D+/', '', (string) $owner->phone);
        if ($customerPhone !== '' && $ownerPhone !== '' && substr($customerPhone, -10) === substr($ownerPhone, -10)) {
            return 'Affiliate and customer phone numbers match.';
        }

        return null;
    }
}

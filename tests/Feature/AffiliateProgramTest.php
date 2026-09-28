<?php

namespace Tests\Feature;

use App\Jobs\ReconcileStaleAffiliateWithdrawals;
use App\Models\AffiliateCommissionRule;
use App\Models\AffiliateLedgerEntry;
use App\Models\AffiliatePayoutAccount;
use App\Models\AffiliateProfile;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\AffiliateAttributionService;
use App\Services\AffiliateCommissionRuleResolver;
use App\Services\AffiliateCommissionService;
use App\Services\AffiliateLedgerService;
use App\Services\AffiliateWithdrawalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AffiliateProgramTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_apply_and_admin_can_approve_with_unique_code(): void
    {
        Notification::fake();
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $this->actingAs($customer)->post(route('account.affiliate.apply'), [
            'application_message' => 'I create jewellery content for an engaged fashion audience.',
            'social_url' => 'https://example.com/profile',
            'audience_summary' => 'Fashion shoppers in India',
        ])->assertRedirect();

        $profile = $customer->affiliateProfile()->firstOrFail();
        $this->assertSame('pending', $profile->status);
        $this->assertNotEmpty($profile->referral_code);

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->patch(route('admin.affiliates.status', $profile), [
            'status' => 'approved', 'commission_rate' => 7.5,
        ])->assertRedirect();

        $this->assertDatabaseHas('affiliate_profiles', [
            'id' => $profile->id, 'status' => 'approved', 'commission_rate' => 7.5, 'approved_by' => $admin->id,
        ]);
    }

    public function test_affiliate_and_admin_management_pages_render(): void
    {
        $affiliate = $this->affiliate('PAGECODE', 5);
        $customerRoutes = [
            'account.affiliate.dashboard', 'account.affiliate.links', 'account.affiliate.referrals',
            'account.affiliate.commissions', 'account.affiliate.payout', 'account.affiliate.withdrawals',
        ];
        foreach ($customerRoutes as $route) {
            $this->actingAs($affiliate->user)->get(route($route))->assertOk();
        }

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        foreach (['admin.affiliates.index', 'admin.affiliates.rules', 'admin.affiliates.referrals', 'admin.affiliates.commissions', 'admin.affiliates.withdrawals', 'admin.affiliates.audits'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
        $this->get(route('admin.affiliates.show', $affiliate))->assertOk();
    }

    public function test_latest_valid_click_wins_and_self_referrals_are_flagged(): void
    {
        $first = $this->affiliate('FIRSTCODE');
        $second = $this->affiliate('SECONDCODE');

        $this->get('/about?ref=FIRSTCODE')->assertOk();
        $this->get('/about?ref=SECONDCODE')->assertOk();

        $this->assertSame($second->id, session(AffiliateAttributionService::SESSION_CLICK_KEY)
            ? \App\Models\AffiliateReferralClick::find(session(AffiliateAttributionService::SESSION_CLICK_KEY))->affiliate_id
            : null);

        $this->actingAs($second->user)->get('/about?ref=SECONDCODE')->assertOk();
        $this->assertDatabaseHas('affiliate_referral_clicks', [
            'affiliate_id' => $second->id, 'is_valid' => false, 'is_suspicious' => true,
        ]);

        $buyer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $this->actingAs($buyer)->get('/about?ref=SECONDCODE')->assertOk();
        $resolved = app(AffiliateAttributionService::class)->resolve($buyer, null);
        $this->assertTrue($resolved['flagged']);
        $this->assertStringContainsString('device or session', $resolved['reason']);
    }

    public function test_affiliate_coupon_overrides_link_and_self_match_is_prevented(): void
    {
        $linkAffiliate = $this->affiliate('LINKCODE');
        $couponAffiliate = $this->affiliate('COUPONCODE');
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $coupon = Coupon::create([
            'affiliate_id' => $couponAffiliate->id, 'code' => 'PARTNER10', 'discount_type' => 'percentage',
            'discount_value' => 10, 'start_date' => today(), 'end_date' => today()->addMonth(), 'is_active' => true,
        ]);

        $this->get('/about?ref=LINKCODE');
        $resolved = app(AffiliateAttributionService::class)->resolve($customer, $coupon);
        $this->assertSame($couponAffiliate->id, $resolved['affiliate']->id);
        $this->assertSame('affiliate_coupon', $resolved['source']);

        $selfCoupon = $coupon->replicate(['code']);
        $selfCoupon->code = 'SELF10';
        $selfCoupon->affiliate_id = $linkAffiliate->id;
        $selfCoupon->save();
        $resolved = app(AffiliateAttributionService::class)->resolve($linkAffiliate->user, $selfCoupon);
        $this->assertTrue($resolved['flagged']);
    }

    public function test_rate_priority_is_product_then_affiliate_then_category_then_global(): void
    {
        [$category, $product] = $this->product();
        $affiliate = $this->affiliate('RULECODE', 8);
        AffiliateCommissionRule::create(['scope_key' => 'global', 'scope_type' => 'global', 'rate' => 2]);
        AffiliateCommissionRule::create(['scope_key' => 'category:'.$category->id, 'scope_type' => 'category', 'category_id' => $category->id, 'rate' => 4]);
        $resolver = app(AffiliateCommissionRuleResolver::class);

        $this->assertSame(8.0, $resolver->resolve($affiliate, $product)['rate']);
        AffiliateCommissionRule::create(['scope_key' => 'product:'.$product->id, 'scope_type' => 'product', 'product_id' => $product->id, 'rate' => 11]);
        $this->assertSame(11.0, $resolver->resolve($affiliate, $product)['rate']);

        $affiliate->update(['commission_rate' => null]);
        AffiliateCommissionRule::where('scope_type', 'product')->delete();
        $this->assertSame(4.0, $resolver->resolve($affiliate->refresh(), $product)['rate']);
        AffiliateCommissionRule::where('scope_type', 'category')->delete();
        $this->assertSame(2.0, $resolver->resolve($affiliate, $product)['rate']);
    }

    public function test_checkout_freezes_server_resolved_coupon_attribution_and_item_commission_snapshot(): void
    {
        $linkAffiliate = $this->affiliate('CHECKOUTLINK', 3);
        $couponAffiliate = $this->affiliate('CHECKOUTCOUPON', 7);
        [, $product] = $this->product();
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true, 'phone' => '9000000001']);
        $address = $customer->addresses()->create([
            'type' => 'Home', 'name' => 'Affiliate Buyer', 'email' => $customer->email, 'phone' => '9000000001',
            'line1' => 'Buyer Street', 'city' => 'Kolkata', 'district' => 'Kolkata', 'state' => 'West Bengal',
            'pincode' => '700001', 'country' => 'India', 'is_default' => true,
        ]);
        Coupon::create([
            'affiliate_id' => $couponAffiliate->id, 'code' => 'AFFILIATE10', 'discount_type' => 'percentage',
            'discount_value' => 10, 'start_date' => today(), 'end_date' => today()->addMonth(), 'is_active' => true,
        ]);

        $this->actingAs($customer)->get('/about?ref=CHECKOUTLINK')->assertOk();
        $this->postJson(route('cart.store'), ['product_id' => $product->id, 'qty' => 1])->assertOk();
        $this->postJson(route('cart.coupon.apply'), ['code' => 'affiliate10'])->assertOk();
        $this->deleteJson(route('cart.coupon.remove'))->assertOk()->assertJsonPath('summary.coupon_code', null);
        $this->postJson(route('cart.coupon.apply'), ['code' => 'AFFILIATE10'])->assertOk();
        $this->postJson(route('checkout.order.store'), [
            'address_id' => $address->id, 'delivery' => 'standard', 'payment' => 'online',
            'affiliate_id' => $linkAffiliate->id,
        ])->assertOk();

        $order = $customer->orders()->with('items')->firstOrFail();
        $this->assertSame($couponAffiliate->id, $order->affiliate_id);
        $this->assertSame('affiliate_coupon', $order->affiliate_attribution_source);
        $this->assertSame('AFFILIATE10', $order->coupon_code);
        $this->assertSame(100.0, (float) $order->coupon_discount);
        $this->assertSame(900.0, (float) $order->items->first()->affiliate_eligible_amount);
        $this->assertSame(7.0, (float) $order->items->first()->affiliate_commission_rate);
        $this->assertSame(63.0, (float) $order->items->first()->affiliate_commission_amount);
        $this->assertDatabaseCount('affiliate_commissions', 0);
    }

    public function test_cod_commission_waits_for_collection_and_paid_creation_is_idempotent_with_frozen_rate(): void
    {
        $affiliate = $this->affiliate('CODCODE');
        $order = $this->commissionOrder($affiliate, 'cod', 10);
        $service = app(AffiliateCommissionService::class);

        $this->assertSame(0, $service->createForPaidOrder($order));
        $order->update(['payment_status' => 'paid', 'paid_at' => now()]);
        $this->assertSame(1, $service->createForPaidOrder($order));
        $this->assertSame(0, $service->createForPaidOrder($order));

        $affiliate->update(['commission_rate' => 99]);
        $this->assertDatabaseHas('affiliate_commissions', [
            'order_id' => $order->id, 'commission_rate' => 10, 'gross_amount' => 100,
        ]);
        $this->assertDatabaseCount('affiliate_commissions', 1);
        $this->assertDatabaseCount('affiliate_ledger_entries', 1);
    }

    public function test_commission_releases_after_delivery_hold_and_refunds_can_create_negative_available_balance(): void
    {
        config(['affiliate.return_hold_days' => 7]);
        $affiliate = $this->affiliate('REFUNDCODE');
        $order = $this->commissionOrder($affiliate, 'paid', 10);
        $order->update(['status' => 'delivered', 'delivered_at' => now()->subDays(8)]);
        $service = app(AffiliateCommissionService::class);
        $service->createForPaidOrder($order);
        $service->markDelivered($order->refresh());

        $this->assertSame(1, $service->releaseDue());
        $this->assertSame(100.0, $affiliate->wallet()['available']);

        // Simulate a payout consuming the available wallet before a later chargeback.
        app(AffiliateLedgerService::class)->append($affiliate, 'test-paid-out', 'withdrawal_paid', ['available' => -100, 'paid' => 100]);
        $order->update(['payment_status' => 'chargeback']);
        $this->assertSame(1, $service->reconcileReversal($order));
        $this->assertSame(0, $service->reconcileReversal($order));
        $this->assertSame(-100.0, $affiliate->wallet()['available']);
    }

    public function test_partial_item_return_only_reverses_the_returned_quantity_once(): void
    {
        $affiliate = $this->affiliate('RETURNCODE');
        $order = $this->commissionOrder($affiliate, 'paid', 10, 2);
        $service = app(AffiliateCommissionService::class);
        $service->createForPaidOrder($order);
        $item = $order->items()->firstOrFail();

        $this->assertSame(1, $service->reconcileReversal($order, [$item->id => 0.5]));
        $this->assertSame(0, $service->reconcileReversal($order, [$item->id => 0.5]));
        $this->assertDatabaseHas('affiliate_commissions', ['order_item_id' => $item->id, 'reversed_amount' => 100]);
    }

    public function test_withdrawal_reservation_is_atomic_idempotent_and_cannot_overspend(): void
    {
        $affiliate = $this->affiliate('WALLETCODE');
        $account = AffiliatePayoutAccount::create([
            'affiliate_id' => $affiliate->id, 'payout_method' => 'upi', 'upi_id' => 'creator@upi', 'is_verified' => true,
        ]);
        app(AffiliateLedgerService::class)->append($affiliate, 'opening-credit', 'test_credit', ['available' => 1000]);
        $service = app(AffiliateWithdrawalService::class);
        $key = (string) Str::uuid();

        $first = $service->request($affiliate, 600, $key);
        $retry = $service->request($affiliate, 600, $key);
        $this->assertSame($first->id, $retry->id);
        $this->assertSame(400.0, $affiliate->wallet()['available']);
        $this->assertSame(600.0, $affiliate->wallet()['reserved']);

        try {
            $service->request($affiliate, 500, (string) Str::uuid());
            $this->fail('Overspending should have failed.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('amount', $exception->errors());
        }
        $this->assertDatabaseCount('affiliate_withdrawals', 1);
        $this->assertSame($account->id, $first->payout_account_id);
    }

    public function test_manual_payout_keeps_unknown_processing_funds_reserved_until_confirmed(): void
    {
        $affiliate = $this->affiliate('PAYOUTCODE');
        AffiliatePayoutAccount::create(['affiliate_id' => $affiliate->id, 'payout_method' => 'upi', 'upi_id' => 'pay@upi', 'is_verified' => true]);
        app(AffiliateLedgerService::class)->append($affiliate, 'payout-credit', 'test_credit', ['available' => 800]);
        $service = app(AffiliateWithdrawalService::class);
        $withdrawal = $service->request($affiliate, 500, (string) Str::uuid());
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $withdrawal = $service->transition($withdrawal, 'approved', $admin);
        $withdrawal = $service->transition($withdrawal, 'processing', $admin);
        $withdrawal->update(['processing_at' => now()->subDays(2)]);

        (new ReconcileStaleAffiliateWithdrawals)->handle();
        $this->assertSame('manual_review', $withdrawal->refresh()->reconciliation_status);
        $this->assertSame(500.0, $affiliate->wallet()['reserved']);

        $service->transition($withdrawal, 'paid', $admin, ['transfer_reference' => 'UTR-123456']);
        $this->assertSame(0.0, $affiliate->wallet()['reserved']);
        $this->assertSame(500.0, $affiliate->wallet()['paid']);
        $this->assertDatabaseCount('affiliate_ledger_entries', 3);
    }

    public function test_payout_credentials_are_encrypted_at_rest(): void
    {
        $affiliate = $this->affiliate('CRYPTCODE');
        $account = AffiliatePayoutAccount::create([
            'affiliate_id' => $affiliate->id, 'payout_method' => 'bank', 'account_holder_name' => 'Test Creator',
            'bank_name' => 'Example Bank', 'account_number' => '123456789012', 'ifsc' => 'ABCD0123456', 'account_last_four' => '9012',
        ]);

        $raw = DB::table('affiliate_payout_accounts')->where('id', $account->id)->first();
        $this->assertNotSame('123456789012', $raw->account_number);
        $this->assertSame('123456789012', $account->fresh()->account_number);
    }

    private function affiliate(string $code, ?float $rate = null): AffiliateProfile
    {
        $user = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        return AffiliateProfile::create([
            'user_id' => $user->id, 'status' => 'approved', 'referral_code' => $code,
            'application_message' => 'Approved test affiliate application.', 'commission_rate' => $rate,
            'applied_at' => now(), 'approved_at' => now(),
        ]);
    }

    /** @return array{Category, Product} */
    private function product(): array
    {
        $category = Category::create(['name' => 'Affiliate Jewellery', 'slug' => 'affiliate-jewellery', 'is_active' => true]);
        $product = Product::create([
            'name' => 'Affiliate Ring', 'slug' => 'affiliate-ring', 'sku' => 'AFF-RING-1',
            'category_id' => $category->id, 'jewellery_type' => 'ring', 'metal_type' => 'gold',
            'mrp' => 1000, 'selling_price' => 1000, 'making_charge' => 0, 'gst_percentage' => 0,
            'final_price' => 1000, 'stock_quantity' => 10, 'minimum_stock' => 1, 'is_active' => true,
        ]);

        return [$category, $product];
    }

    private function commissionOrder(AffiliateProfile $affiliate, string $paymentStatus, float $rate, int $quantity = 1): Order
    {
        [, $product] = $this->product();
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $order = Order::create([
            'order_number' => 'ORD-'.Str::upper(Str::random(12)), 'user_id' => $customer->id,
            'affiliate_id' => $affiliate->id, 'affiliate_attribution_source' => 'last_click',
            'affiliate_referral_code' => $affiliate->referral_code, 'affiliate_attributed_at' => now(),
            'customer_name' => $customer->name, 'customer_email' => $customer->email,
            'status' => 'processing', 'payment_status' => $paymentStatus,
            'subtotal' => 1000 * $quantity, 'grand_total' => 1000 * $quantity,
            'paid_amount' => $paymentStatus === 'paid' ? 1000 * $quantity : 0,
        ]);
        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku,
            'quantity' => $quantity, 'price' => 1000, 'total' => 1000 * $quantity,
            'affiliate_eligible_amount' => 1000 * $quantity, 'affiliate_commission_rate' => $rate,
            'affiliate_commission_rule_type' => 'affiliate',
            'affiliate_commission_amount' => 100 * $quantity,
        ]);

        return $order;
    }
}

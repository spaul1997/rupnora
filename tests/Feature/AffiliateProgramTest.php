<?php

namespace Tests\Feature;

use App\Jobs\ReconcileStaleAffiliateWithdrawals;
use App\Mail\AffiliateApplicationMail;
use App\Mail\AffiliateStatusChangedMail;
use App\Mail\AffiliateWithdrawalMail;
use App\Models\AffiliateCommissionRule;
use App\Models\AffiliateLedgerEntry;
use App\Models\AffiliatePayoutAccount;
use App\Models\AffiliateProfile;
use App\Models\AffiliateWithdrawal;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\WebsiteSetting;
use App\Services\AffiliateAttributionService;
use App\Services\AffiliateCommissionRuleResolver;
use App\Services\AffiliateCommissionService;
use App\Services\AffiliateLedgerService;
use App\Services\AffiliateWithdrawalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AffiliateProgramTest extends TestCase
{
    use RefreshDatabase;

    public function test_affiliate_application_allows_validation_retries_before_rate_limiting(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->actingAs($customer)
                ->post(route('account.affiliate.apply'), ['application_message' => 'Too short'])
                ->assertSessionHasErrors('application_message');
        }
    }

    public function test_customer_can_apply_and_admin_can_approve_with_unique_code(): void
    {
        Mail::fake();
        Notification::fake();
        config()->set('marketing.cc_email', 'marketing@example.com');
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $this->actingAs($customer)->post(route('account.affiliate.apply'), [
            'application_message' => 'I create jewellery content for an engaged fashion audience.',
            'social_url' => 'https://example.com/profile',
            'audience_summary' => 'Fashion shoppers in India',
        ])->assertRedirect();

        $profile = $customer->affiliateProfile()->firstOrFail();
        $this->assertSame('pending', $profile->status);
        $this->assertNotEmpty($profile->referral_code);
        Mail::assertQueued(AffiliateApplicationMail::class, function (AffiliateApplicationMail $mail) use ($customer, $profile) {
            return $mail->profile->is($profile)
                && $mail->hasTo($customer->email)
                && $mail->hasCc('marketing@example.com');
        });

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->patch(route('admin.affiliates.status', $profile), [
            'status' => 'approved', 'commission_rate' => 7.5,
        ])->assertRedirect();

        Mail::assertQueued(AffiliateStatusChangedMail::class, function (AffiliateStatusChangedMail $mail) use ($customer, $profile) {
            return $mail->profile->is($profile)
                && $mail->previousStatus === 'pending'
                && $mail->profile->status === 'approved'
                && $mail->hasTo($customer->email)
                && $mail->hasCc('marketing@example.com');
        });

        $this->actingAs($admin)->patch(route('admin.affiliates.status', $profile), [
            'status' => 'approved', 'commission_rate' => 8,
        ])->assertRedirect();

        Mail::assertQueued(AffiliateStatusChangedMail::class, 1);

        $this->assertDatabaseHas('affiliate_profiles', [
            'id' => $profile->id, 'status' => 'approved', 'commission_rate' => 8, 'approved_by' => $admin->id,
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
        $this->get(route('admin.affiliates.show', $affiliate))
            ->assertOk()
            ->assertSee('href="'.route('admin.affiliates.index').'" class="rounded-lg border px-3 py-2 text-xs font-medium border-charcoal bg-charcoal text-white"', false);
    }

    public function test_referral_links_can_be_filtered_by_parent_category_name_or_sku(): void
    {
        [$category, $product] = $this->product();
        $otherCategory = Category::create([
            'name' => 'Other Jewellery',
            'slug' => 'other-jewellery',
            'is_active' => true,
        ]);
        $otherProduct = $product->replicate();
        $otherProduct->fill([
            'name' => 'Different Necklace',
            'slug' => 'different-necklace',
            'sku' => 'OTHER-NECKLACE-2',
            'category_id' => $otherCategory->id,
        ])->save();
        $affiliate = $this->affiliate('FILTERLINKS', 5);

        $this->actingAs($affiliate->user)
            ->get(route('account.affiliate.links'))
            ->assertOk()
            ->assertSee('name="search"', false)
            ->assertSee('name="parent_category_id"', false)
            ->assertSee($product->name)
            ->assertSee($otherProduct->name);

        $this->get(route('account.affiliate.links', ['search' => $product->sku]))
            ->assertOk()
            ->assertSee('SKU: '.$product->sku)
            ->assertDontSee('SKU: '.$otherProduct->sku);

        $this->get(route('account.affiliate.links', ['search' => 'Different Necklace']))
            ->assertOk()
            ->assertSee('SKU: '.$otherProduct->sku)
            ->assertDontSee('SKU: '.$product->sku);

        $this->get(route('account.affiliate.links', ['parent_category_id' => $category->id]))
            ->assertOk()
            ->assertSee('SKU: '.$product->sku)
            ->assertDontSee('SKU: '.$otherProduct->sku);
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

    public function test_rate_priority_is_affiliate_then_product_then_category_then_global(): void
    {
        [$category, $product] = $this->product();
        $affiliate = $this->affiliate('RULECODE', 8);
        AffiliateCommissionRule::create(['scope_key' => 'global', 'scope_type' => 'global', 'rate' => 2]);
        AffiliateCommissionRule::create(['scope_key' => 'category:'.$category->id, 'scope_type' => 'category', 'category_id' => $category->id, 'rate' => 4]);
        $resolver = app(AffiliateCommissionRuleResolver::class);

        $this->assertSame(8.0, $resolver->resolve($affiliate, $product)['rate']);
        AffiliateCommissionRule::create(['scope_key' => 'product:'.$product->id, 'scope_type' => 'product', 'product_id' => $product->id, 'rate' => 11]);
        $this->assertSame(8.0, $resolver->resolve($affiliate, $product)['rate']);

        $affiliate->update(['commission_rate' => null]);
        $this->assertSame(11.0, $resolver->resolve($affiliate->refresh(), $product)['rate']);
        AffiliateCommissionRule::where('scope_type', 'product')->delete();
        $this->assertSame(4.0, $resolver->resolve($affiliate, $product)['rate']);
        AffiliateCommissionRule::where('scope_type', 'category')->delete();
        $this->assertSame(2.0, $resolver->resolve($affiliate, $product)['rate']);
    }

    public function test_approved_affiliate_sees_the_applicable_commission_rate_chart(): void
    {
        [$category, $product] = $this->product();
        AffiliateCommissionRule::create(['scope_key' => 'global', 'scope_type' => 'global', 'rate' => 2]);
        AffiliateCommissionRule::create(['scope_key' => 'category:'.$category->id, 'scope_type' => 'category', 'category_id' => $category->id, 'rate' => 4]);
        AffiliateCommissionRule::create(['scope_key' => 'product:'.$product->id, 'scope_type' => 'product', 'product_id' => $product->id, 'rate' => 11]);

        $specificAffiliate = $this->affiliate('SPECIFICCHART', 8);
        $this->actingAs($specificAffiliate->user)
            ->get(route('account.affiliate.dashboard'))
            ->assertOk()
            ->assertSee('Your Commission Rate Chart')
            ->assertSee('Affiliate-specific')
            ->assertSee('8.00%')
            ->assertDontSee('11.00%');

        $sharedRuleAffiliate = $this->affiliate('SHAREDCHART');
        $this->actingAs($sharedRuleAffiliate->user)
            ->get(route('account.affiliate.dashboard'))
            ->assertOk()
            ->assertSee('Affiliate Ring')
            ->assertSee('11.00%')
            ->assertSee('Affiliate Jewellery')
            ->assertSee('4.00%')
            ->assertSee('2.00%');
    }

    public function test_approval_requires_a_specific_rate_or_an_active_undated_global_rule(): void
    {
        Notification::fake();
        $affiliate = $this->affiliate('APPROVALCODE');
        $affiliate->update(['status' => 'pending', 'approved_at' => null]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->patch(route('admin.affiliates.status', $affiliate), [
            'status' => 'approved',
            'commission_rate' => null,
        ])->assertSessionHasErrors('commission_rate');

        $this->assertSame('pending', $affiliate->refresh()->status);

        $globalRule = AffiliateCommissionRule::create([
            'scope_key' => 'global',
            'scope_type' => 'global',
            'rate' => 5,
        ]);

        $this->assertTrue(AffiliateCommissionRule::query()
            ->activeAt(now()->addYears(20))
            ->whereKey($globalRule->getKey())
            ->exists());

        $this->actingAs($admin)->patch(route('admin.affiliates.status', $affiliate), [
            'status' => 'approved',
            'commission_rate' => null,
        ])->assertSessionHasNoErrors();

        $affiliate->refresh();
        $this->assertSame('approved', $affiliate->status);
        $this->assertNull($affiliate->commission_rate);
    }

    public function test_commission_rules_are_soft_deleted_and_can_be_recreated(): void
    {
        [$category, $product] = $this->product();
        $childCategory = Category::create([
            'name' => 'Affiliate Rings',
            'slug' => 'affiliate-rings',
            'parent_id' => $category->id,
            'is_active' => true,
        ]);
        $affiliate = $this->affiliate('SOFTDELETECODE');
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $rule = AffiliateCommissionRule::create([
            'scope_key' => 'global',
            'scope_type' => 'global',
            'rate' => 6,
        ]);
        $resolver = app(AffiliateCommissionRuleResolver::class);

        $this->assertSame('global', $resolver->resolve($affiliate, $product)['type']);

        $this->actingAs($admin)
            ->get(route('admin.affiliates.rules'))
            ->assertOk()
            ->assertSee('data-searchable-select="category_id"', false)
            ->assertSee('data-searchable-select="product_id"', false)
            ->assertSee('Search parent categories...')
            ->assertSee('Search products...')
            ->assertSee($category->name)
            ->assertDontSee($childCategory->name)
            ->assertSee('data-swal-confirm', false)
            ->assertSee(route('admin.affiliates.rules.toggle-active', $rule), false);

        $this->patch(route('admin.affiliates.rules.toggle-active', $rule))
            ->assertRedirect();

        $this->assertFalse($rule->refresh()->is_active);
        $this->assertSame('global_config', $resolver->resolve($affiliate, $product)['type']);

        $this->patch(route('admin.affiliates.rules.toggle-active', $rule))
            ->assertRedirect();

        $this->assertTrue($rule->refresh()->is_active);
        $this->assertSame('global', $resolver->resolve($affiliate, $product)['type']);

        $this->delete(route('admin.affiliates.rules.destroy', $rule))
            ->assertRedirect();

        $this->assertSoftDeleted('affiliate_commission_rules', ['id' => $rule->id]);
        $this->assertSame('global_config', $resolver->resolve($affiliate, $product)['type']);

        $this->post(route('admin.affiliates.rules.store'), [
            'scope_type' => 'global',
            'rate' => 7,
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $restoredRule = AffiliateCommissionRule::where('scope_key', 'global')->firstOrFail();
        $this->assertSame($rule->id, $restoredRule->id);
        $this->assertSame('7.00', $restoredRule->rate);
        $this->assertSame(1, AffiliateCommissionRule::withTrashed()->where('scope_key', 'global')->count());

        $this->post(route('admin.affiliates.rules.store'), [
            'scope_type' => 'category',
            'category_id' => $childCategory->id,
            'rate' => 5,
            'is_active' => 1,
        ])->assertSessionHasErrors('category_id');

        $this->assertDatabaseMissing('affiliate_commission_rules', [
            'scope_key' => 'category:'.$childCategory->id,
        ]);
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
        WebsiteSetting::current()->update(['return_allow' => 3]);
        $affiliate = $this->affiliate('REFUNDCODE');
        $order = $this->commissionOrder($affiliate, 'paid', 10);
        $order->items()->firstOrFail()->product()->update(['is_return_available' => true]);
        $order->update(['status' => 'delivered', 'delivered_at' => now()->subDays(4)]);
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

    public function test_returnable_commission_waits_for_the_website_return_allowance(): void
    {
        $this->travelTo(now()->startOfHour());
        WebsiteSetting::current()->update(['return_allow' => 3]);
        $affiliate = $this->affiliate('RETURNWINDOW');
        $order = $this->commissionOrder($affiliate, 'paid', 10);
        $order->items()->firstOrFail()->product()->update(['is_refund_available' => true]);
        $order->update(['status' => 'delivered', 'delivered_at' => now()]);
        $service = app(AffiliateCommissionService::class);

        $service->createForPaidOrder($order);
        $service->markDelivered($order->refresh());

        $commission = $order->affiliateCommissions()->firstOrFail();
        $this->assertTrue($commission->available_at->equalTo(now()->addDays(3)));
        $this->assertSame(0, $service->releaseDue());

        $this->travel(3)->days();
        $this->assertSame(1, $service->releaseDue());
        $this->assertSame(100.0, $affiliate->wallet()['available']);
    }

    public function test_non_returnable_commission_is_available_after_delivery_without_a_hold(): void
    {
        $this->travelTo(now()->startOfHour());
        WebsiteSetting::current()->update(['return_allow' => 3]);
        $affiliate = $this->affiliate('FINALSALE');
        $order = $this->commissionOrder($affiliate, 'paid', 10);
        $order->update(['status' => 'delivered', 'delivered_at' => now()]);
        $service = app(AffiliateCommissionService::class);

        $service->createForPaidOrder($order);
        $service->markDelivered($order->refresh());

        $commission = $order->affiliateCommissions()->firstOrFail();
        $this->assertTrue($commission->available_at->equalTo(now()));
        $this->assertSame(1, $service->releaseDue());
        $this->assertSame(100.0, $affiliate->wallet()['available']);
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

    public function test_withdrawal_request_paid_and_rejected_emails_use_the_marketing_cc(): void
    {
        Mail::fake();
        Notification::fake();
        config()->set('marketing.cc_email', 'marketing@example.com');

        $affiliate = $this->affiliate('WITHDRAWALMAILS');
        AffiliatePayoutAccount::create([
            'affiliate_id' => $affiliate->id,
            'payout_method' => 'upi',
            'upi_id' => 'mailtest@upi',
            'is_verified' => true,
        ]);
        app(AffiliateLedgerService::class)->append($affiliate, 'mail-test-credit', 'test_credit', ['available' => 2000]);

        $this->actingAs($affiliate->user)->post(route('account.affiliate.withdrawals.store'), [
            'amount' => 600,
            'idempotency_key' => (string) Str::uuid(),
        ])->assertRedirect();

        $rejectedWithdrawal = AffiliateWithdrawal::query()->latest('id')->firstOrFail();
        Mail::assertQueued(AffiliateWithdrawalMail::class, function (AffiliateWithdrawalMail $mail) use ($affiliate, $rejectedWithdrawal) {
            return $mail->event === 'requested'
                && $mail->withdrawal->is($rejectedWithdrawal)
                && $mail->hasTo($affiliate->user->email)
                && $mail->hasCc('marketing@example.com');
        });

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->patch(route('admin.affiliates.withdrawals.update', $rejectedWithdrawal), [
            'status' => 'rejected',
        ])->assertRedirect();

        Mail::assertQueued(AffiliateWithdrawalMail::class, function (AffiliateWithdrawalMail $mail) use ($affiliate, $rejectedWithdrawal) {
            return $mail->event === 'rejected'
                && $mail->withdrawal->is($rejectedWithdrawal)
                && $mail->hasTo($affiliate->user->email)
                && $mail->hasCc('marketing@example.com');
        });

        $this->actingAs($affiliate->user)->post(route('account.affiliate.withdrawals.store'), [
            'amount' => 700,
            'idempotency_key' => (string) Str::uuid(),
        ])->assertRedirect();

        $paidWithdrawal = AffiliateWithdrawal::query()->latest('id')->firstOrFail();
        $this->actingAs($admin)->patch(route('admin.affiliates.withdrawals.update', $paidWithdrawal), ['status' => 'approved'])->assertRedirect();
        $this->patch(route('admin.affiliates.withdrawals.update', $paidWithdrawal), ['status' => 'processing'])->assertRedirect();
        $this->patch(route('admin.affiliates.withdrawals.update', $paidWithdrawal), [
            'status' => 'paid',
            'transfer_reference' => 'UTR-MAIL-123',
        ])->assertRedirect();

        Mail::assertQueued(AffiliateWithdrawalMail::class, function (AffiliateWithdrawalMail $mail) use ($affiliate, $paidWithdrawal) {
            return $mail->event === 'paid'
                && $mail->withdrawal->is($paidWithdrawal)
                && $mail->hasTo($affiliate->user->email)
                && $mail->hasCc('marketing@example.com');
        });
        Mail::assertQueued(AffiliateWithdrawalMail::class, 4);
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

    public function test_payout_details_require_a_mobile_number_and_saved_address(): void
    {
        $affiliate = $this->affiliate('PAYOUTREADY');
        $user = $affiliate->user;
        $user->update(['phone' => null]);

        $this->actingAs($user)
            ->get(route('account.affiliate.payout'))
            ->assertOk()
            ->assertSee('Complete your payout profile')
            ->assertSee('Update profile')
            ->assertSee('Add address')
            ->assertDontSee('Save payout details');

        $this->put(route('account.affiliate.payout.update'), [
            'payout_method' => 'upi',
            'upi_id' => 'creator@upi',
        ])->assertSessionHasErrors(['phone', 'address']);

        $this->assertDatabaseMissing('affiliate_payout_accounts', ['affiliate_id' => $affiliate->id]);

        $user->update(['phone' => '9876543210']);
        $user->addresses()->create([
            'type' => 'Home',
            'is_default' => true,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '9876543210',
            'line1' => '1 Test Street',
            'city' => 'Kolkata',
            'district' => 'Kolkata',
            'state' => 'West Bengal',
            'pincode' => '700001',
            'country' => 'India',
        ]);

        $this->get(route('account.affiliate.payout'))
            ->assertOk()
            ->assertSee('Save payout details');

        $this->put(route('account.affiliate.payout.update'), [
            'payout_method' => 'upi',
            'upi_id' => 'creator@upi',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('affiliate_payout_accounts', [
            'affiliate_id' => $affiliate->id,
            'payout_method' => 'upi',
        ]);
        $this->assertSame('creator@upi', $affiliate->payoutAccount()->firstOrFail()->upi_id);
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

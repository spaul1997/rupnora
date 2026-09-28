<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('pending','paid','failed','refunded','partial_refund','chargeback','cod') NOT NULL DEFAULT 'pending'");
        }

        Schema::create('affiliate_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->string('status', 30)->default('pending')->index();
            $table->string('referral_code', 40)->unique();
            $table->text('application_message');
            $table->string('website_url', 500)->nullable();
            $table->string('social_url', 500)->nullable();
            $table->string('audience_summary', 255)->nullable();
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('applied_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('affiliate_commission_rules', function (Blueprint $table) {
            $table->id();
            $table->string('scope_key')->unique();
            $table->string('scope_type', 30)->index();
            $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('rate', 5, 2);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('affiliate_referral_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliate_profiles')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('attributed_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('referral_code', 40);
            $table->string('session_hash', 64)->index();
            $table->string('visitor_hash', 64)->index();
            $table->string('landing_url', 1000);
            $table->string('referrer_url', 1000)->nullable();
            $table->boolean('is_valid')->default(true)->index();
            $table->boolean('is_suspicious')->default(false)->index();
            $table->string('suspicious_reason')->nullable();
            $table->timestamp('clicked_at')->index();
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->index(['affiliate_id', 'clicked_at']);
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->foreignId('affiliate_id')->nullable()->after('id')->constrained('affiliate_profiles')->nullOnDelete();
            $table->index(['affiliate_id', 'is_active']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('affiliate_id')->nullable()->after('user_id')->constrained('affiliate_profiles')->nullOnDelete();
            $table->foreignId('affiliate_referral_click_id')->nullable()->after('affiliate_id')->constrained('affiliate_referral_clicks')->nullOnDelete();
            $table->foreignId('affiliate_coupon_id')->nullable()->after('affiliate_referral_click_id')->constrained('coupons')->nullOnDelete();
            $table->string('affiliate_attribution_source', 30)->nullable()->after('affiliate_coupon_id');
            $table->string('affiliate_referral_code', 40)->nullable()->after('affiliate_attribution_source');
            $table->json('affiliate_rule_snapshot')->nullable()->after('affiliate_referral_code');
            $table->boolean('affiliate_flagged')->default(false)->after('affiliate_rule_snapshot');
            $table->string('affiliate_flag_reason')->nullable()->after('affiliate_flagged');
            $table->timestamp('affiliate_attributed_at')->nullable()->after('affiliate_flag_reason');
            $table->index(['affiliate_id', 'created_at']);
            $table->index('affiliate_flagged');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('affiliate_eligible_amount', 12, 2)->default(0)->after('total');
            $table->decimal('affiliate_commission_rate', 5, 2)->nullable()->after('affiliate_eligible_amount');
            $table->string('affiliate_commission_rule_type', 30)->nullable()->after('affiliate_commission_rate');
            $table->foreignId('affiliate_commission_rule_id')->nullable()->after('affiliate_commission_rule_type')->constrained('affiliate_commission_rules')->nullOnDelete();
            $table->decimal('affiliate_commission_amount', 12, 2)->default(0)->after('affiliate_commission_rule_id');
            $table->unsignedInteger('returned_quantity')->default(0)->after('affiliate_commission_amount');
        });

        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliate_profiles')->restrictOnDelete();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_item_id')->unique()->constrained()->restrictOnDelete();
            $table->string('status', 30)->default('pending')->index();
            $table->decimal('eligible_amount', 12, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('reversed_amount', 12, 2)->default(0);
            $table->string('rule_type', 30);
            $table->foreignId('rule_id')->nullable()->constrained('affiliate_commission_rules')->nullOnDelete();
            $table->string('idempotency_key')->unique();
            $table->timestamp('available_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->timestamps();

            $table->index(['affiliate_id', 'status']);
            $table->index(['status', 'available_at']);
        });

        Schema::create('affiliate_payout_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->unique()->constrained('affiliate_profiles')->cascadeOnDelete();
            $table->string('payout_method', 20);
            $table->text('account_holder_name')->nullable();
            $table->text('bank_name')->nullable();
            $table->text('account_number')->nullable();
            $table->text('ifsc')->nullable();
            $table->text('upi_id')->nullable();
            $table->string('account_last_four', 4)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        Schema::create('affiliate_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliate_profiles')->restrictOnDelete();
            $table->foreignId('payout_account_id')->constrained('affiliate_payout_accounts')->restrictOnDelete();
            $table->string('request_reference')->unique();
            $table->string('idempotency_key')->unique();
            $table->string('transfer_reference')->nullable()->unique();
            $table->string('status', 30)->default('requested')->index();
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('deduction_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->text('payout_snapshot');
            $table->string('reconciliation_status', 30)->default('not_required')->index();
            $table->text('admin_notes')->nullable();
            $table->text('failure_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['affiliate_id', 'created_at']);
        });

        Schema::create('affiliate_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliate_profiles')->restrictOnDelete();
            $table->foreignId('commission_id')->nullable()->constrained('affiliate_commissions')->restrictOnDelete();
            $table->foreignId('withdrawal_id')->nullable()->constrained('affiliate_withdrawals')->restrictOnDelete();
            $table->string('reference')->unique();
            $table->string('type', 40)->index();
            $table->decimal('pending_delta', 12, 2)->default(0);
            $table->decimal('available_delta', 12, 2)->default(0);
            $table->decimal('reserved_delta', 12, 2)->default(0);
            $table->decimal('paid_delta', 12, 2)->default(0);
            $table->decimal('reversed_delta', 12, 2)->default(0);
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('deduction_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['affiliate_id', 'occurred_at']);
        });

        Schema::create('affiliate_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->nullable()->constrained('affiliate_profiles')->nullOnDelete();
            $table->foreignId('withdrawal_id')->nullable()->constrained('affiliate_withdrawals')->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 80)->index();
            $table->string('status_from', 30)->nullable();
            $table->string('status_to', 30)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_audit_logs');
        Schema::dropIfExists('affiliate_ledger_entries');
        Schema::dropIfExists('affiliate_withdrawals');
        Schema::dropIfExists('affiliate_payout_accounts');
        Schema::dropIfExists('affiliate_commissions');

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('affiliate_commission_rule_id');
            $table->dropColumn([
                'affiliate_eligible_amount', 'affiliate_commission_rate',
                'affiliate_commission_rule_type', 'affiliate_commission_amount', 'returned_quantity',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['affiliate_id', 'created_at']);
            $table->dropIndex(['affiliate_flagged']);
            $table->dropConstrainedForeignId('affiliate_coupon_id');
            $table->dropConstrainedForeignId('affiliate_referral_click_id');
            $table->dropConstrainedForeignId('affiliate_id');
            $table->dropColumn([
                'affiliate_attribution_source', 'affiliate_referral_code', 'affiliate_rule_snapshot',
                'affiliate_flagged', 'affiliate_flag_reason', 'affiliate_attributed_at',
            ]);
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropIndex(['affiliate_id', 'is_active']);
            $table->dropConstrainedForeignId('affiliate_id');
        });

        Schema::dropIfExists('affiliate_referral_clicks');
        Schema::dropIfExists('affiliate_commission_rules');
        Schema::dropIfExists('affiliate_profiles');

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('pending','paid','failed','refunded','partial_refund','cod') NOT NULL DEFAULT 'pending'");
        }
    }
};

# Affiliate Program Implementation

## Repository findings

- Laravel 12 with customer/admin roles and database-backed queues.
- Checkout creates orders and item price snapshots inside a database transaction.
- Online orders remain `pending`; payment is verified through the existing admin payment-status workflow.
- COD orders use payment status `cod` until an admin confirms collection by changing them to `paid`.
- Returns/refunds are currently order-level; affiliate reconciliation will add item quantity support without replacing the existing flow.
- Coupons exist in admin but the storefront coupon button was not connected.
- No automated payment/payout provider or verified callback endpoint is configured. Manual payouts are therefore the first complete payout adapter.

## Implementation checklist

- [x] Inspect project structure, schema, checkout, payment/refund flow, coupons, roles, queues, scheduler, and tests.
- [x] Write implementation plan and identify integration points.
- [x] Add affiliate configuration and database schema with foreign keys, indexes, uniqueness, and idempotency constraints.
- [x] Add affiliate models, relationships, policy, immutable ledger, wallet calculation, and commission rule resolver.
- [x] Add application, approval/rejection/suspension, unique referral codes, and notifications.
- [x] Add referral middleware, 30-day latest-click attribution, affiliate coupon precedence, and self-referral/suspicion checks.
- [x] Connect coupons to cart/checkout and freeze attribution plus item-level commission snapshots during order creation.
- [x] Add idempotent paid/COD commission creation, hold-period release job, and cancellation/refund/return reversals.
- [x] Add encrypted payout accounts, atomic withdrawal reservation, manual approval/processing/payment/failure flow, and audit logs.
- [x] Build customer affiliate application/dashboard, links, referrals, commissions, payout settings, withdrawal, and history pages.
- [x] Build admin affiliate applications, rate rules, flagged referrals, commissions, withdrawals, ledger export, and audit pages.
- [x] Add scheduled release/reconciliation commands and queue/cron documentation for cPanel.
- [x] Add tests for attribution precedence, idempotency, withdrawal overspend protection, refunds/returns, COD, frozen rates, and payout retry safeguards.
- [x] Run migrations, asset build, focused tests, and the complete test suite; fix regressions.
- [x] Document deployment settings, queue/cron commands, accountant-owned deduction configuration, and remaining provider setup.

## Planned modules

- `config/affiliate.php`
- Affiliate migrations and models under `app/Models`
- `AffiliateAttributionService`, `AffiliateCommissionService`, `AffiliateLedgerService`, and `AffiliateWithdrawalService`
- Referral middleware, affiliate policy, queued jobs, notifications, console schedule
- Customer routes/controllers/views under `account/affiliate`
- Admin routes/controllers/views under `admin/affiliates`
- Checkout/cart/order integration plus focused feature tests

## External setup still required

- Accountant-approved deduction type/value must be configured before applying deductions. Defaults remain disabled.
- Automated payouts remain disabled until a payout-specific provider account, credentials, callback signature scheme, and reconciliation API are supplied.

## Verification completed

- Production asset build completed successfully with Vite.
- Affiliate migration applied successfully to the configured local database.
- Blade view compilation and schedule discovery completed successfully.
- Full automated suite passes: 164 tests, 1,216 assertions.

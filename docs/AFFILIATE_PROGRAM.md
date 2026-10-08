# Affiliate Program Operations

## What is implemented

The affiliate program uses existing customer accounts. Customers apply from **Account → Affiliate Program** and administrators review them under **Admin → Marketing → Affiliate Program**.

- Referral links use `?ref=CODE`; `/products/{slug}?ref=CODE` is supported.
- Attribution lasts 30 days by default. The latest valid click wins; an active affiliate-owned coupon overrides link attribution.
- The checkout never accepts an affiliate ID from the browser. It resolves attribution from the server session or the validated coupon and freezes the source, code, rule settings, eligible amount, rate, and commission on the order.
- Commission priority is affiliate-specific rate → product rule → category rule → global rule/config. An affiliate-specific rate overrides every shared rule for that affiliate.
- A rule with no start date is effective immediately, and a rule with no end date does not expire. Approval requires either an affiliate-specific rate or a currently active Global rule.
- Commission is created only after payment status becomes `paid`. COD therefore requires confirmed collection before commission creation.
- Commission for a returnable or refundable product remains pending until delivery plus the `return_allow` days configured in Website Settings (3 days by default). Commission for other products is eligible at delivery. Refunds, cancellations, item returns, and chargebacks append reversal entries. A late reversal can make the available wallet negative after payout.
- Wallet figures are sums of immutable ledger entries. Withdrawal reservation and balance checks run in one database transaction with row locks and idempotency keys.
- Payout account values and payout snapshots are encrypted with Laravel's `APP_KEY`.
- Payouts are manual. The administrator records the bank/UPI transfer UTR before marking a withdrawal paid. A processing payout remains reserved if its result is unknown; after 24 hours it is flagged for manual reconciliation rather than retried or released automatically.

## Environment settings

Add these values to the production `.env` as needed:

```dotenv
AFFILIATE_ATTRIBUTION_DAYS=30
AFFILIATE_GLOBAL_RATE=5
AFFILIATE_MIN_WITHDRAWAL=500
AFFILIATE_QUEUE=default

# Do not enable until an accountant has approved the withholding treatment.
AFFILIATE_DEDUCTION_TYPE=none
AFFILIATE_DEDUCTION_VALUE=0
```

`AFFILIATE_DEDUCTION_TYPE` supports `none`, `percentage`, or `fixed`. The value is frozen on each withdrawal. Tax deduction rules are intentionally configuration-owned—there is no hardcoded TDS/GST assumption.

Back up `APP_KEY` securely. Changing or losing it makes encrypted bank/UPI details unreadable.

## cPanel deployment

Build frontend assets locally (or in the deployment environment), upload the application, and then run:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
php artisan storage:link
```

Ensure `storage/` and `bootstrap/cache/` are writable by the PHP user. The configured database user must be able to create/alter tables and indexes during the migration.

The project uses the database queue. Keep `QUEUE_CONNECTION=database`, then run a persistent queue worker when cPanel provides Supervisor/process management:

```bash
php artisan queue:work database --queue=emails,default --tries=3 --timeout=120 --max-time=3600
```

If persistent workers are unavailable, add this cPanel cron every minute (replace paths and PHP binary):

```cron
* * * * * cd /home/ACCOUNT/app && /usr/local/bin/php artisan queue:work database --queue=emails,default --stop-when-empty --tries=3 --timeout=120 >> /dev/null 2>&1
```

Also add Laravel's scheduler cron every minute:

```cron
* * * * * cd /home/ACCOUNT/app && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

After deployments that change code, restart persistent workers with `php artisan queue:restart`.

## Scheduled and queued work

- Hourly: dispatch commission release processing.
- Hourly at minute 15: flag payouts that have remained unconfirmed in `processing` for more than 24 hours.
- Affiliate application/status, pending/available commission, and withdrawal events use queued database notifications.

Monitor with:

```bash
php artisan schedule:list
php artisan queue:failed
php artisan queue:retry all
```

Payout jobs must not be blindly retried if an external transfer result is unknown. The current manual adapter deliberately has no transfer API call, so recording the UTR is the point of confirmation.

## Administrator workflow

1. Review and approve the application. Set an affiliate-specific rate, or leave it blank to use shared rules when an active Global rule exists.
2. Configure global/category/product rates. Rate changes affect new order snapshots only.
3. Optionally assign an approved affiliate as owner of a coupon. That coupon takes precedence over a referral link.
4. Review suspicious referrals and flagged orders. Self-referrals do not earn commission.
5. Verify the affiliate payout account.
6. For a withdrawal: approve → processing → make the transfer → paid with UTR. Use rejected before transfer, or failed only after the failed transfer outcome is known.
7. For partial returns, enter per-item returned quantities on the admin order page. Quantities cannot be reduced after ledger reconciliation.

## Automated payout provider boundary

No payout-provider credentials or signed callback contract exist in this repository, so no speculative API integration was added. A future adapter needs:

- provider credentials stored outside source control;
- a unique transfer idempotency key mapped to `request_reference`;
- signed webhook verification;
- a transfer-status lookup endpoint for timeouts;
- explicit mapping of provider terminal states to `paid` or `failed`;
- tests proving a retry cannot create a second transfer.

Until those inputs are available, manual payout with UTR and reconciliation status is the safe production path.

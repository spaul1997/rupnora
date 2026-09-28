<?php

namespace App\Services;

use App\Models\AffiliateCommission;
use App\Models\Order;
use App\Notifications\AffiliateActivity;
use Illuminate\Support\Facades\DB;

class AffiliateCommissionService
{
    public function __construct(private readonly AffiliateLedgerService $ledger) {}

    public function createForPaidOrder(Order $order): int
    {
        return DB::transaction(function () use ($order) {
            $order = Order::query()->with(['affiliate', 'items'])->lockForUpdate()->findOrFail($order->id);

            if ($order->payment_status !== 'paid' || ! $order->affiliate || $order->affiliate_flagged ||
                in_array($order->status, ['cancelled', 'returned', 'refunded'], true)) {
                return 0;
            }

            $created = 0;
            foreach ($order->items as $item) {
                $amount = round((float) $item->affiliate_commission_amount, 2);
                if ($amount <= 0 || $item->affiliate_commission_rate === null) {
                    continue;
                }

                $commission = AffiliateCommission::firstOrCreate(
                    ['order_item_id' => $item->id],
                    [
                        'affiliate_id' => $order->affiliate_id,
                        'order_id' => $order->id,
                        'status' => 'pending',
                        'eligible_amount' => $item->affiliate_eligible_amount,
                        'commission_rate' => $item->affiliate_commission_rate,
                        'gross_amount' => $amount,
                        'rule_type' => $item->affiliate_commission_rule_type,
                        'rule_id' => $item->affiliate_commission_rule_id,
                        'idempotency_key' => 'paid-order-item-'.$item->id,
                        'available_at' => $order->delivered_at?->copy()->addDays((int) config('affiliate.return_hold_days', 7)),
                    ]
                );

                if ($commission->wasRecentlyCreated) {
                    $this->ledger->append($order->affiliate, 'commission-pending-'.$commission->id, 'commission_pending',
                        ['pending' => $amount], [
                            'commission_id' => $commission->id,
                            'gross_amount' => $amount,
                            'description' => 'Commission pending for order '.$order->order_number,
                        ]);
                    DB::afterCommit(fn () => $order->affiliate->user->notify(new AffiliateActivity(
                        'Commission pending',
                        '₹'.number_format($amount, 2).' from order '.$order->order_number.' is pending until delivery and the return hold pass.',
                        route('account.affiliate.commissions')
                    )));
                    $created++;
                }
            }

            return $created;
        }, 3);
    }

    public function markDelivered(Order $order): void
    {
        if (! $order->delivered_at) {
            return;
        }

        AffiliateCommission::query()->where('order_id', $order->id)->where('status', 'pending')
            ->update(['available_at' => $order->delivered_at->copy()->addDays((int) config('affiliate.return_hold_days', 7))]);
    }

    public function releaseDue(): int
    {
        $released = 0;
        AffiliateCommission::query()->where('status', 'pending')->whereNotNull('available_at')
            ->where('available_at', '<=', now())->orderBy('id')->chunkById(100, function ($rows) use (&$released) {
                foreach ($rows as $row) {
                    $released += DB::transaction(function () use ($row) {
                        $commission = AffiliateCommission::query()->with(['affiliate', 'order'])->lockForUpdate()->find($row->id);
                        if (! $commission || $commission->status !== 'pending' || ! $commission->available_at?->lte(now()) ||
                            $commission->order->status !== 'delivered') {
                            return 0;
                        }

                        $net = round((float) $commission->gross_amount - (float) $commission->reversed_amount, 2);
                        $commission->update(['status' => $net > 0 ? 'available' : 'reversed', 'released_at' => now()]);
                        if ($net > 0) {
                            $this->ledger->append($commission->affiliate, 'commission-release-'.$commission->id, 'commission_released',
                                ['pending' => -$net, 'available' => $net], [
                                    'commission_id' => $commission->id, 'gross_amount' => $net,
                                    'description' => 'Commission released for order '.$commission->order->order_number,
                                ]);
                            DB::afterCommit(fn () => $commission->affiliate->user->notify(new AffiliateActivity(
                                'Commission available',
                                '₹'.number_format($net, 2).' from order '.$commission->order->order_number.' is now available.',
                                route('account.affiliate.commissions')
                            )));
                        }

                        return 1;
                    }, 3);
                }
            });

        return $released;
    }

    public function reconcileReversal(Order $order, array $itemRatios = []): int
    {
        return DB::transaction(function () use ($order, $itemRatios) {
            $order = Order::query()->with(['affiliate', 'affiliateCommissions'])->lockForUpdate()->findOrFail($order->id);
            if (! $order->affiliate) {
                return 0;
            }

            $ratio = $this->reversalRatio($order);
            $changed = 0;
            foreach ($order->affiliateCommissions as $row) {
                $commission = AffiliateCommission::query()->lockForUpdate()->findOrFail($row->id);
                $commissionRatio = max($ratio, (float) ($itemRatios[$commission->order_item_id] ?? 0));
                $target = round((float) $commission->gross_amount * min(1, $commissionRatio), 2);
                $delta = round($target - (float) $commission->reversed_amount, 2);
                if ($delta <= 0) {
                    continue;
                }

                $bucket = $commission->released_at ? 'available' : 'pending';
                $newStatus = $target >= (float) $commission->gross_amount ? 'reversed' : $commission->status;
                $commission->update([
                    'reversed_amount' => $target,
                    'status' => $newStatus,
                    'reversed_at' => now(),
                ]);
                $this->ledger->append($order->affiliate,
                    'commission-reversal-'.$commission->id.'-'.(int) round($target * 100), 'commission_reversed',
                    [$bucket => -$delta, 'reversed' => $delta], [
                        'commission_id' => $commission->id, 'gross_amount' => $delta,
                        'description' => 'Commission reversed for order '.$order->order_number,
                        'metadata' => ['payment_status' => $order->payment_status, 'order_status' => $order->status],
                    ]);
                $changed++;
            }

            return $changed;
        }, 3);
    }

    private function reversalRatio(Order $order): float
    {
        if (in_array($order->status, ['cancelled', 'returned', 'refunded'], true) ||
            in_array($order->payment_status, ['refunded', 'chargeback'], true)) {
            return 1;
        }

        if ($order->payment_status !== 'partial_refund' || (float) $order->refund_amount <= 0) {
            return 0;
        }

        $goodsPaid = max(0.01, (float) $order->grand_total - (float) $order->shipping_charge - (float) $order->gst_amount - (float) $order->gift_wrap_charge);

        return min(1, (float) $order->refund_amount / $goodsPaid);
    }
}

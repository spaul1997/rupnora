<?php

namespace App\Services;

use App\Models\AffiliateAuditLog;
use App\Models\AffiliateProfile;
use App\Models\AffiliateWithdrawal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AffiliateWithdrawalService
{
    public function __construct(private readonly AffiliateLedgerService $ledger) {}

    public function request(AffiliateProfile $affiliate, float $amount, string $idempotencyKey): AffiliateWithdrawal
    {
        return DB::transaction(function () use ($affiliate, $amount, $idempotencyKey) {
            $existing = AffiliateWithdrawal::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                if ($existing->affiliate_id !== $affiliate->id) {
                    throw ValidationException::withMessages(['amount' => 'That withdrawal request key is already in use.']);
                }

                return $existing;
            }

            $affiliate = AffiliateProfile::query()->with('payoutAccount')->lockForUpdate()->findOrFail($affiliate->id);
            if (! $affiliate->isApproved()) {
                throw ValidationException::withMessages(['amount' => 'Only approved affiliates can request a withdrawal.']);
            }
            if (! $affiliate->payoutAccount) {
                throw ValidationException::withMessages(['amount' => 'Add a payout account before requesting a withdrawal.']);
            }
            if (! $affiliate->payoutAccount->is_verified) {
                throw ValidationException::withMessages(['amount' => 'Your payout account must be verified before requesting a withdrawal.']);
            }

            $amount = round($amount, 2);
            $minimum = (float) config('affiliate.minimum_withdrawal', 500);
            $available = $affiliate->wallet()['available'];
            if ($amount < $minimum) {
                throw ValidationException::withMessages(['amount' => 'The minimum withdrawal is ₹'.number_format($minimum, 2).'.']);
            }
            if ($amount > $available) {
                throw ValidationException::withMessages(['amount' => 'The requested amount exceeds your available balance.']);
            }

            $deduction = $this->deduction($amount);
            $withdrawal = AffiliateWithdrawal::create([
                'affiliate_id' => $affiliate->id,
                'payout_account_id' => $affiliate->payoutAccount->id,
                'request_reference' => 'WD-'.now()->format('Ymd').'-'.strtoupper(str()->random(10)),
                'idempotency_key' => $idempotencyKey,
                'status' => 'requested',
                'gross_amount' => $amount,
                'deduction_amount' => $deduction,
                'net_amount' => round($amount - $deduction, 2),
                'payout_snapshot' => $affiliate->payoutAccount->only([
                    'payout_method', 'account_holder_name', 'bank_name', 'account_number', 'ifsc', 'upi_id', 'account_last_four',
                ]),
                'requested_at' => now(),
            ]);

            $this->ledger->append($affiliate, 'withdrawal-reserve-'.$withdrawal->id, 'withdrawal_reserved',
                ['available' => -$amount, 'reserved' => $amount], [
                    'withdrawal_id' => $withdrawal->id, 'gross_amount' => $amount,
                    'deduction_amount' => $deduction, 'net_amount' => $withdrawal->net_amount,
                    'description' => 'Funds reserved for withdrawal '.$withdrawal->request_reference,
                ]);
            $this->audit($withdrawal, null, 'withdrawal_requested', null, 'requested');

            return $withdrawal;
        }, 3);
    }

    public function transition(AffiliateWithdrawal $withdrawal, string $status, User $actor, array $data = []): AffiliateWithdrawal
    {
        return DB::transaction(function () use ($withdrawal, $status, $actor, $data) {
            $withdrawal = AffiliateWithdrawal::query()->with('affiliate')->lockForUpdate()->findOrFail($withdrawal->id);
            if ($withdrawal->status === $status) {
                return $withdrawal;
            }

            $allowed = [
                'requested' => ['approved', 'rejected'],
                'approved' => ['processing', 'rejected'],
                'processing' => ['paid', 'failed'],
            ];
            if (! in_array($status, $allowed[$withdrawal->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => "Cannot move this withdrawal from {$withdrawal->status} to {$status}."]);
            }
            if ($status === 'paid' && blank($data['transfer_reference'] ?? null)) {
                throw ValidationException::withMessages(['transfer_reference' => 'A bank/UPI transfer reference is required before marking a payout paid.']);
            }
            if ($status === 'failed' && blank($data['failure_reason'] ?? null)) {
                throw ValidationException::withMessages(['failure_reason' => 'Record why the payout failed.']);
            }

            $from = $withdrawal->status;
            $updates = [
                'status' => $status,
                'admin_notes' => $data['admin_notes'] ?? $withdrawal->admin_notes,
            ];
            if ($status === 'approved') {
                $updates += ['approved_by' => $actor->id, 'approved_at' => now()];
            } elseif ($status === 'processing') {
                $updates += ['processed_by' => $actor->id, 'processing_at' => now(), 'reconciliation_status' => 'pending_confirmation'];
            } elseif ($status === 'paid') {
                $updates += [
                    'processed_by' => $actor->id, 'paid_at' => now(),
                    'transfer_reference' => trim($data['transfer_reference']), 'reconciliation_status' => 'confirmed',
                ];
                $this->ledger->append($withdrawal->affiliate, 'withdrawal-paid-'.$withdrawal->id, 'withdrawal_paid',
                    ['reserved' => -(float) $withdrawal->gross_amount, 'paid' => (float) $withdrawal->gross_amount], [
                        'withdrawal_id' => $withdrawal->id, 'gross_amount' => $withdrawal->gross_amount,
                        'deduction_amount' => $withdrawal->deduction_amount, 'net_amount' => $withdrawal->net_amount,
                        'description' => 'Withdrawal paid by manual transfer',
                        'metadata' => ['transfer_reference' => trim($data['transfer_reference'])],
                    ]);
            } elseif (in_array($status, ['rejected', 'failed'], true)) {
                $updates += $status === 'rejected'
                    ? ['rejected_at' => now(), 'reconciliation_status' => 'not_required']
                    : ['failed_at' => now(), 'failure_reason' => trim($data['failure_reason']), 'reconciliation_status' => 'confirmed_failed'];
                $this->ledger->append($withdrawal->affiliate, 'withdrawal-release-'.$withdrawal->id, 'withdrawal_released',
                    ['reserved' => -(float) $withdrawal->gross_amount, 'available' => (float) $withdrawal->gross_amount], [
                        'withdrawal_id' => $withdrawal->id, 'gross_amount' => $withdrawal->gross_amount,
                        'description' => 'Reserved funds released after withdrawal '.$status,
                    ]);
            }

            $withdrawal->update($updates);
            $this->audit($withdrawal, $actor, 'withdrawal_'.$status, $from, $status, $data);

            return $withdrawal->refresh();
        }, 3);
    }

    private function deduction(float $amount): float
    {
        $type = config('affiliate.deduction.type', 'none');
        $value = max(0, (float) config('affiliate.deduction.value', 0));

        return match ($type) {
            'percentage' => round(min($amount, $amount * $value / 100), 2),
            'fixed' => round(min($amount, $value), 2),
            default => 0.0,
        };
    }

    private function audit(AffiliateWithdrawal $withdrawal, ?User $actor, string $event, ?string $from, ?string $to, array $metadata = []): void
    {
        AffiliateAuditLog::create([
            'affiliate_id' => $withdrawal->affiliate_id,
            'withdrawal_id' => $withdrawal->id,
            'actor_id' => $actor?->id,
            'event' => $event,
            'status_from' => $from,
            'status_to' => $to,
            'metadata' => collect($metadata)->except(['account_number', 'ifsc', 'upi_id'])->all(),
            'created_at' => now(),
        ]);
    }
}

<?php

namespace Modules\Album\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Album\Entities\Album;
use Modules\Album\Entities\Payment;
use Modules\Album\Entities\Purchase;
use Modules\Album\Enums\PurchaseStatus;

class PurchaseService
{
    public function __construct(
        private TapPaymentService $tapPaymentService
    ) {}

    public function userHasPaidAccess(User $user, Album $album): bool
    {
        return Purchase::query()
            ->where('user_id', $user->id)
            ->where('album_id', $album->id)
            ->where('status', PurchaseStatus::Paid)
            ->exists();
    }

    public function initiatePurchase(User $user, Album $album): array
    {
        if ($this->userHasPaidAccess($user, $album)) {
            return [
                'already_purchased' => true,
                'message' => 'You already own this album.',
            ];
        }

        $pending = Purchase::query()
            ->where('user_id', $user->id)
            ->where('album_id', $album->id)
            ->where('status', PurchaseStatus::Pending)
            ->latest()
            ->first();

        if ($pending && $pending->payment?->charge_id) {
            $charge = $this->tapPaymentService->retrieveCharge($pending->payment->charge_id);

            if ($this->tapPaymentService->isChargeSuccessful($charge)) {
                $this->markAsPaid($pending, $pending->payment, $charge);

                return [
                    'already_purchased' => true,
                    'message' => 'Payment confirmed.',
                ];
            }

            if ($this->tapPaymentService->isChargePending($charge) && ! empty($charge['transaction']['url'])) {
                return [
                    'already_purchased' => false,
                    'payment_url' => $charge['transaction']['url'],
                    'purchase' => $pending,
                ];
            }
        }

        return DB::transaction(function () use ($user, $album) {
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'album_id' => $album->id,
                'amount' => $album->price,
                'currency' => $album->currency,
                'status' => PurchaseStatus::Pending,
                'payment_gateway' => 'tap',
            ]);

            $payment = Payment::create([
                'user_id' => $user->id,
                'album_id' => $album->id,
                'purchase_id' => $purchase->id,
                'gateway' => 'tap',
                'status' => PurchaseStatus::Pending,
                'amount' => $album->price,
                'currency' => $album->currency,
            ]);

            $charge = $this->tapPaymentService->createCharge($purchase, $user, $album);

            $payment->update([
                'charge_id' => $charge['id'] ?? null,
                'gateway_response' => $charge,
            ]);

            $purchase->update([
                'transaction_id' => $charge['id'] ?? null,
            ]);

            return [
                'already_purchased' => false,
                'payment_url' => $charge['transaction']['url'] ?? null,
                'purchase' => $purchase->fresh(['payment']),
            ];
        });
    }

    public function markAsPaid(Purchase $purchase, Payment $payment, array $chargeData): void
    {
        if ($purchase->isPaid()) {
            return;
        }

        DB::transaction(function () use ($purchase, $payment, $chargeData) {
            $paidAt = now();

            $purchase->update([
                'status' => PurchaseStatus::Paid,
                'transaction_id' => $chargeData['id'] ?? $purchase->transaction_id,
                'paid_at' => $paidAt,
            ]);

            $payment->update([
                'status' => PurchaseStatus::Paid,
                'charge_id' => $chargeData['id'] ?? $payment->charge_id,
                'gateway_response' => $chargeData,
                'paid_at' => $paidAt,
            ]);
        });
    }

    public function markAsFailed(Purchase $purchase, Payment $payment, array $chargeData): void
    {
        if ($purchase->isPaid()) {
            return;
        }

        $purchase->update([
            'status' => PurchaseStatus::Failed,
            'transaction_id' => $chargeData['id'] ?? $purchase->transaction_id,
        ]);

        $payment->update([
            'status' => PurchaseStatus::Failed,
            'gateway_response' => $chargeData,
        ]);
    }

    public function findByChargeId(string $chargeId): ?Payment
    {
        return Payment::where('charge_id', $chargeId)->first();
    }

    public function findByPurchaseUuid(string $uuid): ?Purchase
    {
        return Purchase::where('uuid', $uuid)->first();
    }
}

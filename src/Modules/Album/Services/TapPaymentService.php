<?php

namespace Modules\Album\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Album\Entities\Album;
use Modules\Album\Entities\Purchase;

class TapPaymentService
{
    public function createCharge(Purchase $purchase, User $user, Album $album): array
    {
        $payload = [
            'amount' => (float) $purchase->amount,
            'currency' => $purchase->currency,
            'threeDSecure' => true,
            'save_card' => false,
            'description' => 'Purchase: ' . $album->title,
            'statement_descriptor' => substr(config('app.name', 'Album'), 0, 22),
            'metadata' => [
                'purchase_id' => $purchase->uuid,
                'purchase_db_id' => $purchase->id,
                'album_id' => $album->id,
                'user_id' => $user->id,
            ],
            'reference' => [
                'transaction' => $purchase->uuid,
                'order' => (string) $purchase->id,
            ],
            'receipt' => [
                'email' => true,
                'sms' => false,
            ],
            'customer' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'source' => [
                'id' => config('tap.source_id', 'src_all'),
            ],
            'redirect' => [
                'url' => config('tap.redirect_url') . '?purchase=' . $purchase->uuid,
            ],
            'post' => [
                'url' => config('tap.webhook_url'),
            ],
        ];

        $response = Http::withToken(config('tap.secret_key'))
            ->acceptJson()
            ->post(rtrim(config('tap.base_url'), '/') . '/charges', $payload);

        if (! $response->successful()) {
            Log::error('Tap charge creation failed', [
                'status' => $response->status(),
                'body' => $response->json(),
                'purchase_id' => $purchase->id,
            ]);

            abort(502, 'Unable to initiate payment. Please try again later.');
        }

        return $response->json();
    }

    public function retrieveCharge(string $chargeId): array
    {
        $response = Http::withToken(config('tap.secret_key'))
            ->acceptJson()
            ->get(rtrim(config('tap.base_url'), '/') . '/charges/' . $chargeId);

        if (! $response->successful()) {
            Log::warning('Tap charge retrieval failed', [
                'charge_id' => $chargeId,
                'status' => $response->status(),
            ]);

            return [];
        }

        return $response->json();
    }

    public function isChargeSuccessful(array $charge): bool
    {
        $status = strtoupper($charge['status'] ?? '');

        return in_array($status, ['CAPTURED', 'AUTHORIZED'], true);
    }

    public function isChargePending(array $charge): bool
    {
        $status = strtoupper($charge['status'] ?? '');

        return in_array($status, ['INITIATED', 'IN_PROGRESS', 'PENDING'], true);
    }

    public function isChargeFailed(array $charge): bool
    {
        $status = strtoupper($charge['status'] ?? '');

        return in_array($status, ['FAILED', 'CANCELLED', 'DECLINED', 'ABANDONED', 'VOID'], true);
    }

    public function verifyWebhookSignature(?string $payload, ?string $signature): bool
    {
        if (! $signature || ! $payload) {
            return app()->environment('testing', 'local');
        }

        $hash = hash_hmac('sha256', $payload, config('tap.secret_key'));

        return hash_equals($hash, $signature);
    }
}

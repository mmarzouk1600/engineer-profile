<?php

namespace Modules\Album\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Album\Services\PurchaseService;
use Modules\Album\Services\TapPaymentService;

/**
 * @OA\Tag(
 *     name="Tap Payments",
 *     description="Server-side Tap Payments webhook & redirect handling"
 * )
 */
class TapWebhookController extends Controller
{
    public function __construct(
        private TapPaymentService $tapPaymentService,
        private PurchaseService $purchaseService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/payments/tap/webhook",
     *     summary="Tap Payments webhook — updates purchase/payment status server-side",
     *     description="Idempotent: safe to receive the same event multiple times. Never trusts the payload alone — always re-verifies the charge status directly against Tap's API before marking a purchase as paid.",
     *     tags={"Tap Payments"},
     *     @OA\Response(response=200, description="Webhook processed"),
     *     @OA\Response(response=400, description="Invalid payload or signature")
     * )
     */
    public function handle(Request $request)
    {
        $signature = $request->header('hashstring') ?? $request->header('x-tap-signature');

        if (! $this->tapPaymentService->verifyWebhookSignature($request->getContent(), $signature)) {
            Log::warning('Tap webhook signature verification failed.');

            return response()->json(['status' => 'error', 'message' => 'Invalid signature.'], 400);
        }

        $chargeId = $request->input('id');

        if (! $chargeId) {
            return response()->json(['status' => 'error', 'message' => 'Missing charge id.'], 400);
        }

        // Never trust the webhook payload's status field alone — re-fetch
        // the charge from Tap directly to get the authoritative status.
        $charge = $this->tapPaymentService->retrieveCharge($chargeId);

        if (empty($charge)) {
            return response()->json(['status' => 'error', 'message' => 'Charge not found.'], 400);
        }

        $payment = $this->purchaseService->findByChargeId($chargeId);

        if (! $payment) {
            Log::warning('Tap webhook: no matching payment found.', ['charge_id' => $chargeId]);

            return response()->json(['status' => 'ignored', 'message' => 'No matching payment.']);
        }

        $purchase = $payment->purchase;

        // Idempotent: if already paid, do nothing further.
        if ($purchase->isPaid()) {
            return response()->json(['status' => 'success', 'message' => 'Already processed.']);
        }

        if ($this->tapPaymentService->isChargeSuccessful($charge)) {
            $this->purchaseService->markAsPaid($purchase, $payment, $charge);
        } elseif ($this->tapPaymentService->isChargeFailed($charge)) {
            $this->purchaseService->markAsFailed($purchase, $payment, $charge);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * @OA\Get(
     *     path="/payment/tap/redirect",
     *     summary="Browser redirect target after the customer completes payment on Tap's page",
     *     description="Re-verifies the charge status server-side before reporting success — the frontend redirect alone is never trusted to confirm payment.",
     *     tags={"Tap Payments"},
     *     @OA\Parameter(name="tap_id", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="purchase", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Payment status")
     * )
     */
    public function redirect(Request $request)
    {
        $chargeId = $request->query('tap_id');
        $purchaseUuid = $request->query('purchase');

        $purchase = $purchaseUuid ? $this->purchaseService->findByPurchaseUuid($purchaseUuid) : null;

        if (! $purchase) {
            return redirect()->route('home')->with('message', 'Purchase not found.');
        }

        if (! $purchase->isPaid() && $chargeId) {
            $charge = $this->tapPaymentService->retrieveCharge($chargeId);
            $payment = $purchase->payment;

            if ($payment && $this->tapPaymentService->isChargeSuccessful($charge)) {
                $this->purchaseService->markAsPaid($purchase, $payment, $charge);
            } elseif ($payment && $this->tapPaymentService->isChargeFailed($charge)) {
                $this->purchaseService->markAsFailed($purchase, $payment, $charge);
            }
        }

        $purchase->refresh();

        return redirect()->route('albums.show', $purchase->album->slug)->with([
            'message' => $purchase->isPaid()
                ? 'Payment successful! Your files are ready to download.'
                : 'Payment was not completed. Please try again.',
            'icon' => $purchase->isPaid() ? 'success' : 'error',
        ]);
    }
}

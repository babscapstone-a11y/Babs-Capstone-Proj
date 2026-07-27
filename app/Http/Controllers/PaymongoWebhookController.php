<?php

namespace App\Http\Controllers;

use App\Models\PaymentProof;
use App\Services\PaymongoClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * POST /webhooks/paymongo — server-to-server notification PayMongo sends
 * when a payment intent resolves. This is a reliability backstop for the
 * synchronous check already done in CheckoutController::paymongoReturn();
 * it requires PayMongo to be able to reach this server (a public URL, e.g.
 * via an ngrok tunnel in local development) and a webhook signing secret
 * registered in the PayMongo dashboard and set as PAYMONGO_WEBHOOK_SECRET.
 * Until that's set up, this endpoint exists but won't receive traffic.
 */
class PaymongoWebhookController extends Controller
{
    public function __construct(private PaymongoClient $paymongo)
    {
    }

    public function handle(Request $request): Response
    {
        $signatureHeader = $request->header('Paymongo-Signature');

        if (! $this->paymongo->verifyWebhookSignature($request->getContent(), $signatureHeader)) {
            Log::warning('PayMongo webhook signature verification failed.');

            return response('Invalid signature', 400);
        }

        $event = $request->input('data.attributes.type');
        $intentId = $request->input('data.attributes.data.id')
            ?? $request->input('data.attributes.data.attributes.payment_intent_id');

        if (! $intentId) {
            return response('OK', 200);
        }

        $paymentProof = PaymentProof::where('paymongo_payment_intent_id', $intentId)->first();

        if (! $paymentProof) {
            return response('OK', 200);
        }

        if ($event === 'payment_intent.succeeded' && $paymentProof->status !== 'paid') {
            $paymentId = $request->input('data.attributes.data.attributes.payments.0.id', $intentId);

            $paymentProof->update([
                'status'           => 'paid',
                'paid_at'          => now(),
                'reference_number' => $paymentId,
            ]);
        } elseif ($event === 'payment_intent.payment_failed' && $paymentProof->status !== 'paid') {
            $paymentProof->update(['status' => 'failed']);
        }

        return response('OK', 200);
    }
}

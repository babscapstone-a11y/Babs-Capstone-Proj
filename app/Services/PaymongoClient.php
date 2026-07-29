<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper around PayMongo's REST API (no official PHP SDK exists).
 * Auth is HTTP Basic with the secret/public key as the username and an
 * empty password, per PayMongo's API docs.
 */
class PaymongoClient
{
    private const BASE_URL = 'https://api.paymongo.com/v1';

    private function secretKey(): string
    {
        $key = config('services.paymongo.secret_key');

        if (! $key) {
            throw new RuntimeException('PayMongo secret key is not configured. Set PAYMONGO_SECRET_KEY in .env.');
        }

        return $key;
    }

    private function http()
    {
        return Http::withBasicAuth($this->secretKey(), '')
            ->acceptJson()
            ->asJson();
    }

    /**
     * Create a Payment Intent for the given amount (in centavos), restricted
     * to whichever payment method type(s) will be attached to it.
     */
    public function createPaymentIntent(int $amountCentavos, string $description, array $allowedMethods = ['gcash']): array
    {
        $response = $this->http()->post(self::BASE_URL . '/payment_intents', [
            'data' => [
                'attributes' => [
                    'amount'                 => $amountCentavos,
                    'currency'               => 'PHP',
                    'description'            => $description,
                    'payment_method_allowed' => $allowedMethods,
                    'capture_type'           => 'automatic',
                ],
            ],
        ]);

        return $response->throw()->json('data');
    }

    /**
     * Create a reusable Payment Method of the given type (e.g. "gcash",
     * "qrph").
     */
    public function createPaymentMethod(string $type, array $billing = []): array
    {
        $attributes = ['type' => $type];

        if ($billing) {
            $attributes['billing'] = $billing;
        }

        $response = $this->http()->post(self::BASE_URL . '/payment_methods', [
            'data' => ['attributes' => $attributes],
        ]);

        return $response->throw()->json('data');
    }

    /**
     * Attach a Payment Method to a Payment Intent. What the customer does
     * next depends on the method type — extractNextAction() below reads it
     * out of the response.
     */
    public function attachPaymentMethod(string $paymentIntentId, string $paymentMethodId, string $returnUrl): array
    {
        $response = $this->http()->post(self::BASE_URL . "/payment_intents/{$paymentIntentId}/attach", [
            'data' => [
                'attributes' => [
                    'payment_method' => $paymentMethodId,
                    'return_url'     => $returnUrl,
                ],
            ],
        ]);

        return $response->throw()->json('data');
    }

    /**
     * Reads the actionable payload out of an attached Payment Intent.
     * GCash (and similar redirect-based methods) return
     * `next_action.redirect.url` — a page to send the customer's browser to.
     * QR Ph returns `next_action.code.image_url` — an already-rendered
     * Base64 QR image with nowhere to redirect, since a *different* device
     * is expected to scan it.
     */
    public function extractNextAction(array $attachedIntent): array
    {
        $nextAction = $attachedIntent['attributes']['next_action'] ?? [];

        if ($url = $nextAction['redirect']['url'] ?? null) {
            return ['type' => 'redirect', 'value' => $url];
        }

        if ($image = $nextAction['code']['image_url'] ?? null) {
            return ['type' => 'qr_image', 'value' => $image];
        }

        throw new RuntimeException('PayMongo did not return a usable next action.');
    }

    /**
     * Retrieve the current status of a Payment Intent — the authoritative
     * check used on the customer's return redirect, since there is no
     * webhook tunnel available in local development.
     */
    public function retrievePaymentIntent(string $paymentIntentId): array
    {
        $response = $this->http()->get(self::BASE_URL . "/payment_intents/{$paymentIntentId}");

        return $response->throw()->json('data');
    }

    /**
     * Payment Intents don't expose a simple "still waiting" vs "failed"
     * status — right after attach() a GCash intent sits at
     * `awaiting_next_action` for as long as the customer takes to act, and
     * there's no separate top-level "failed" status at all. A failed
     * attempt shows up as a `last_payment_error`, or a `failed` entry in the
     * `payments` array, while the intent itself may still read
     * `awaiting_payment_method` (ready to retry). This normalizes all of
     * that into the three states callers actually care about.
     */
    public function interpretIntentStatus(array $paymongoIntent): string
    {
        $attributes = $paymongoIntent['attributes'] ?? [];

        if (($attributes['status'] ?? null) === 'succeeded') {
            return 'succeeded';
        }

        if (! empty($attributes['last_payment_error'])) {
            return 'failed';
        }

        foreach ($attributes['payments'] ?? [] as $payment) {
            if (($payment['attributes']['status'] ?? null) === 'failed') {
                return 'failed';
            }
        }

        return 'pending';
    }

    /**
     * Verify the `Paymongo-Signature` header PayMongo sends with webhook
     * requests. Header format: "t=<timestamp>,te=<test_sig>,li=<live_sig>" —
     * the signed payload is "<timestamp>.<raw body>", HMAC-SHA256'd with the
     * webhook's signing secret.
     */
    public function verifyWebhookSignature(string $payload, ?string $signatureHeader): bool
    {
        $webhookSecret = config('services.paymongo.webhook_secret');

        if (! $webhookSecret || ! $signatureHeader) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);
            if ($key !== null && $value !== null) {
                $parts[trim($key)] = trim($value);
            }
        }

        $timestamp = $parts['t'] ?? null;
        $signature = $parts['live'] ?? $parts['li'] ?? $parts['test'] ?? $parts['te'] ?? null;

        if (! $timestamp || ! $signature) {
            return false;
        }

        $expected = hash_hmac('sha256', "{$timestamp}.{$payload}", $webhookSecret);

        return hash_equals($expected, $signature);
    }
}

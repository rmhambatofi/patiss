<?php

namespace App\Services;

use Stripe\Checkout\Session;
use Stripe\StripeClient;

/**
 * The only place the application talks to the Stripe API (KTD4, KTD5).
 * Tests bind a fake in its place; the client is built on first use.
 */
class StripeGateway
{
    private ?StripeClient $client = null;

    public function __construct(private ?string $secret = null) {}

    /**
     * @param  array<string, mixed>  $params
     */
    public function createCheckoutSession(array $params): Session
    {
        return $this->client()->checkout->sessions->create($params);
    }

    public function retrieveCheckoutSession(string $sessionId): Session
    {
        return $this->client()->checkout->sessions->retrieve($sessionId);
    }

    public function expireCheckoutSession(string $sessionId): void
    {
        $this->client()->checkout->sessions->expire($sessionId);
    }

    /**
     * Refunds the whole payment and returns the refund id. The idempotency key
     * makes a retried webhook reuse the refund instead of failing on a second one.
     */
    public function refundPaymentIntent(string $paymentIntentId): string
    {
        return $this->client()->refunds->create(
            ['payment_intent' => $paymentIntentId],
            ['idempotency_key' => 'refund-'.$paymentIntentId],
        )->id;
    }

    private function client(): StripeClient
    {
        return $this->client ??= new StripeClient(['api_key' => $this->secret]);
    }
}

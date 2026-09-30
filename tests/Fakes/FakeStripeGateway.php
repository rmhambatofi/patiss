<?php

namespace Tests\Fakes;

use App\Services\StripeGateway;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiConnectionException;

/**
 * In-memory Stripe: records every call and answers like the Checkout API.
 */
class FakeStripeGateway extends StripeGateway
{
    /** @var list<array<string, mixed>> */
    public array $created = [];

    /** @var list<string> */
    public array $expired = [];

    /** @var list<string> */
    public array $refunded = [];

    /** @var array<string, Session> */
    public array $sessions = [];

    public bool $failExpire = false;

    public function __construct() {}

    public function createCheckoutSession(array $params): Session
    {
        $this->created[] = $params;
        $id = 'cs_test_fake'.count($this->created);

        return $this->sessions[$id] = Session::constructFrom([
            'id' => $id,
            'object' => 'checkout.session',
            'url' => 'https://checkout.stripe.com/c/pay/'.$id,
            'status' => 'open',
            'payment_status' => 'unpaid',
            'created' => now()->getTimestamp(),
            'expires_at' => $params['expires_at'],
            'metadata' => $params['metadata'] ?? [],
        ]);
    }

    public function retrieveCheckoutSession(string $sessionId): Session
    {
        return $this->sessions[$sessionId];
    }

    public function expireCheckoutSession(string $sessionId): void
    {
        if ($this->failExpire) {
            throw new ApiConnectionException('Stripe injoignable');
        }

        $this->expired[] = $sessionId;
    }

    public function refundPaymentIntent(string $paymentIntentId): string
    {
        $this->refunded[] = $paymentIntentId;

        return 're_test_fake'.count($this->refunded);
    }
}

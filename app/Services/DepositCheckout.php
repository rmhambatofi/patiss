<?php

namespace App\Services;

use App\Enums\Fulfilment;
use App\Enums\RequestStatus;
use App\Models\DepositLink;
use Illuminate\Support\Carbon;

/**
 * The client side of a deposit link (KTD4): may it still be paid, and which
 * Stripe Checkout session does it open. Only the webhook marks it paid.
 */
class DepositCheckout
{
    public const PAID = 'paid';

    public const REPLACED = 'replaced';

    public const EXPIRED = 'expired';

    public const FULL = 'full';

    public const SESSION_MINUTES = 30;

    /** Stripe refuses an expiry under 30 minutes after its own creation time: margin for the clock gap. */
    private const CLOCK_MARGIN_SECONDS = 60;

    public function __construct(private StripeGateway $stripe, private CapacityGuard $capacity) {}

    public function findByToken(string $token): ?DepositLink
    {
        return DepositLink::with('cakeRequest.cakeType')
            ->where('token_hash', DepositLink::hashToken($token))
            ->first();
    }

    /**
     * Why this link cannot be paid now, or null when it can (KTD4, KTD5).
     */
    public function blocker(DepositLink $link): ?string
    {
        $request = $link->cakeRequest;

        return match (true) {
            $link->status === DepositLink::STATUS_PAID => self::PAID,
            $link->status !== DepositLink::STATUS_ACTIVE => self::REPLACED,
            $link->expires_at->isPast() => self::EXPIRED,
            $request->status === RequestStatus::Remboursee => self::FULL,
            $request->status !== RequestStatus::Proposee => self::REPLACED,
            $request->event_date->isBefore(today()) => self::EXPIRED,
            $this->capacity->isFull($request->event_date) => self::FULL,
            default => null,
        };
    }

    /**
     * URL of the Checkout page for this link: the session still open for it, or a new 30-minute one.
     */
    public function checkoutUrl(DepositLink $link, string $token): string
    {
        if ($link->stripe_session_id !== null && $link->stripe_session_expires_at?->isFuture()) {
            $session = $this->stripe->retrieveCheckoutSession($link->stripe_session_id);

            if ($session->status === 'open') {
                return $session->url;
            }

            if ($session->status === 'complete') {
                return route('deposit.return', ['session_id' => $session->id]);
            }
        }

        $request = $link->cakeRequest;
        $params = [
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $link->deposit_cents,
                    'product_data' => ['name' => "Acompte gâteau, demande {$request->reference}"],
                ],
            ]],
            'payment_intent_data' => ['description' => "Acompte demande {$request->reference}"],
            'metadata' => [
                'deposit_link_id' => (string) $link->id,
                'cake_request_id' => (string) $request->id,
            ],
            'customer_email' => $request->email,
            'locale' => 'fr',
            'expires_at' => now()->addMinutes(self::SESSION_MINUTES)->addSeconds(self::CLOCK_MARGIN_SECONDS)->getTimestamp(),
            'success_url' => route('deposit.return').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('deposit.show', $token),
        ];

        if ($request->fulfilment === Fulfilment::Delivery) {
            $params['shipping_address_collection'] = ['allowed_countries' => ['FR']];
        }

        $session = $this->stripe->createCheckoutSession($params);

        $link->update([
            'stripe_session_id' => $session->id,
            'stripe_session_expires_at' => Carbon::createFromTimestamp($session->expires_at, config('app.timezone')),
        ]);

        return $session->url;
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\Fulfilment;
use App\Enums\RequestStatus;
use App\Mail\DepositRefunded;
use App\Mail\DepositRefundedForArtisan;
use App\Mail\OrderConfirmed;
use App\Models\CakeRequest;
use App\Models\Commune;
use App\Models\DepositLink;
use App\Models\Payment;
use App\Services\CapacityGuard;
use App\Services\StripeGateway;
use App\Settings\ShopSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Webhook;

/**
 * The only place a deposit becomes paid (KTD4, KTD5). A payment that can no
 * longer reserve its date is refunded in full, and never gets the confirmation
 * that carries the pickup address (R15).
 */
class StripeWebhookController extends Controller
{
    /** Reasons a paid session is refunded, as told to the client. */
    public const REASON_FULL = 'full';

    public const REASON_INVALID = 'invalid';

    public function __construct(
        private StripeGateway $stripe,
        private CapacityGuard $capacity,
        private ShopSettings $settings,
    ) {}

    public function __invoke(Request $request): Response
    {
        $secret = (string) config('services.stripe.webhook_secret');
        if ($secret === '') {
            Log::error('Webhook Stripe refusé : STRIPE_WEBHOOK_SECRET n’est pas configuré.');

            return response('Webhook secret not configured', 400);
        }

        try {
            $event = Webhook::constructEvent($request->getContent(), (string) $request->header('Stripe-Signature'), $secret);
        } catch (SignatureVerificationException|UnexpectedValueException) {
            return response('Invalid signature', 400);
        }

        match ($event->type) {
            'checkout.session.completed' => $this->completed($event->data->object->toArray()),
            'checkout.session.expired' => $this->expired($event->data->object->toArray()),
            default => null,
        };

        return response('OK');
    }

    /**
     * @param  array<string, mixed>  $session
     */
    private function completed(array $session): void
    {
        if (($session['payment_status'] ?? null) !== 'paid') {
            return;
        }

        if (Payment::where('stripe_session_id', $session['id'])->exists()) {
            return;
        }

        $link = DepositLink::with('cakeRequest')->find(data_get($session, 'metadata.deposit_link_id'));
        $request = $link?->cakeRequest;
        $lockKey = $request !== null
            ? 'capacity:'.$request->event_date->toDateString()
            : 'stripe-session:'.$session['id'];

        Cache::lock($lockKey, 10)->block(5, fn () => DB::transaction(function () use ($session, $link, $request) {
            // Re-read under the lock: another webhook may have just reserved or recorded.
            if (Payment::where('stripe_session_id', $session['id'])->exists()) {
                return;
            }
            $link?->refresh();
            $request?->refresh();

            $reason = $this->refusalReason($session, $link, $request);

            $reason === null
                ? $this->reserve($session, $link, $request)
                : $this->refund($session, $link, $request, $reason);
        }));
    }

    /**
     * @param  array<string, mixed>  $session
     */
    private function refusalReason(array $session, ?DepositLink $link, ?CakeRequest $request): ?string
    {
        $stillValid = $link !== null
            && $request !== null
            && $link->status === DepositLink::STATUS_ACTIVE
            // A session opened before the link expired stays payable for its 30 minutes (KTD4).
            && (int) ($session['created'] ?? PHP_INT_MAX) <= $link->expires_at->getTimestamp()
            && $request->status === RequestStatus::Proposee;

        if (! $stillValid) {
            return self::REASON_INVALID;
        }

        return $this->capacity->isFull($request->event_date) ? self::REASON_FULL : null;
    }

    /**
     * @param  array<string, mixed>  $session
     */
    private function reserve(array $session, DepositLink $link, CakeRequest $request): void
    {
        $this->recordPayment($session, $link, $request, Payment::STATUS_PAID);
        $link->update(['status' => DepositLink::STATUS_PAID]);

        $request->fill(['status' => RequestStatus::Reservee, 'last_activity_at' => now()]);

        $shipping = data_get($session, 'collected_information.shipping_details') ?? data_get($session, 'shipping_details');
        if ($request->fulfilment === Fulfilment::Delivery && is_array($shipping)) {
            $postalCode = data_get($shipping, 'address.postal_code');
            $city = data_get($shipping, 'address.city');

            $request->fill([
                'delivery_name' => $shipping['name'] ?? null,
                'delivery_line1' => data_get($shipping, 'address.line1'),
                'delivery_line2' => data_get($shipping, 'address.line2'),
                'delivery_postal_code' => $postalCode,
                'delivery_city' => $city,
                'delivery_commune_mismatch' => ! $this->matchesCommune($request->commune, $postalCode, $city),
            ]);
        }

        $request->save();

        Mail::to($request->email)->queue(new OrderConfirmed($request, $link));
    }

    /**
     * @param  array<string, mixed>  $session
     */
    private function refund(array $session, ?DepositLink $link, ?CakeRequest $request, string $reason): void
    {
        // Refund first: if Stripe fails, nothing is recorded and Stripe retries the webhook.
        $refundId = $this->stripe->refundPaymentIntent((string) $session['payment_intent']);
        $payment = $this->recordPayment($session, $link, $request, Payment::STATUS_REFUNDED, $refundId);

        if ($reason === self::REASON_FULL) {
            $request->update(['status' => RequestStatus::Remboursee, 'last_activity_at' => now()]);
        }

        Log::warning('Acompte Stripe remboursé', [
            'reference' => $request?->reference,
            'session' => $session['id'],
            'reason' => $reason,
        ]);

        if ($request !== null) {
            Mail::to($request->email)->queue(new DepositRefunded($request, $payment->amount_cents, $reason));
        }

        if (filled($this->settings->notification_email)) {
            Mail::to($this->settings->notification_email)
                ->queue(new DepositRefundedForArtisan($request?->reference, $payment->amount_cents, $reason));
        }
    }

    /**
     * @param  array<string, mixed>  $session
     */
    private function recordPayment(array $session, ?DepositLink $link, ?CakeRequest $request, string $status, ?string $refundId = null): Payment
    {
        return Payment::create([
            'deposit_link_id' => $link?->id,
            'cake_request_id' => $request?->id,
            'stripe_session_id' => $session['id'],
            'payment_intent_id' => $session['payment_intent'] ?? null,
            'amount_cents' => (int) ($session['amount_total'] ?? 0),
            'status' => $status,
            'refund_id' => $refundId,
            'payer_email' => data_get($session, 'customer_details.email'),
        ]);
    }

    private function matchesCommune(?Commune $commune, ?string $postalCode, ?string $city): bool
    {
        return $commune !== null
            && in_array(trim((string) $postalCode), $commune->postal_codes ?? [], true)
            && Commune::normalizeForSearch((string) $city) === Commune::normalizeForSearch($commune->name);
    }

    /**
     * @param  array<string, mixed>  $session
     */
    private function expired(array $session): void
    {
        DepositLink::where('stripe_session_id', $session['id'])->update([
            'stripe_session_id' => null,
            'stripe_session_expires_at' => null,
        ]);
    }
}

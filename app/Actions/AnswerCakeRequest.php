<?php

namespace App\Actions;

use App\Enums\RequestStatus;
use App\Mail\DepositOffer;
use App\Mail\RequestDeclined;
use App\Models\CakeRequest;
use App\Models\DepositLink;
use App\Services\StripeGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\ApiErrorException;

/**
 * The artisan's answer to a request (U7, F3): a priced offer with a deposit link,
 * or a refusal. Either way, at most one link stays active per request, and the
 * Stripe session still open on a deactivated link is expired (KTD4).
 */
class AnswerCakeRequest
{
    /** Statuses the artisan can still answer; a paid or refunded request is never touched. */
    public const PROPOSABLE = [
        RequestStatus::Nouvelle,
        RequestStatus::Proposee,
        RequestStatus::Expiree,
        RequestStatus::Refusee,
    ];

    public const DECLINABLE = [
        RequestStatus::Nouvelle,
        RequestStatus::Proposee,
        RequestStatus::Expiree,
    ];

    public function __construct(private StripeGateway $stripe) {}

    /**
     * @param  array{total_cents: int, deposit_cents: int, allergens: list<string>, slot: string, message: ?string, validity_days: int}  $offer
     */
    public function propose(CakeRequest $request, array $offer): DepositLink
    {
        $token = bin2hex(random_bytes(32));
        $openSessions = [];

        $link = DB::transaction(function () use ($request, $offer, $token, &$openSessions) {
            $openSessions = $this->deactivateLinks($request);

            $link = $request->depositLinks()->create([
                'token_hash' => DepositLink::hashToken($token),
                'total_cents' => $offer['total_cents'],
                'deposit_cents' => $offer['deposit_cents'],
                'allergens' => $offer['allergens'],
                'slot' => $offer['slot'],
                'message' => filled($offer['message'] ?? null) ? $offer['message'] : null,
                'expires_at' => now()->addDays($offer['validity_days']),
                'status' => DepositLink::STATUS_ACTIVE,
            ]);

            $request->update(['status' => RequestStatus::Proposee, 'last_activity_at' => now()]);

            Mail::to($request->email)->queue(new DepositOffer($link, url('/acompte/'.$token)));

            return $link;
        });

        $this->expireSessions($openSessions);

        return $link;
    }

    public function decline(CakeRequest $request, string $message): void
    {
        $openSessions = DB::transaction(function () use ($request, $message) {
            $openSessions = $this->deactivateLinks($request);
            $request->update(['status' => RequestStatus::Refusee, 'last_activity_at' => now()]);

            Mail::to($request->email)->queue(new RequestDeclined($request, $message));

            return $openSessions;
        });

        $this->expireSessions($openSessions);
    }

    public static function defaultRefusal(CakeRequest $request): string
    {
        $date = $request->event_date->locale('fr')->translatedFormat('l j F Y');

        return "Bonjour {$request->name},\n\n"
            ."Merci beaucoup pour votre demande. Malheureusement, je ne peux pas réaliser votre gâteau pour le {$date}.\n\n"
            .'J’espère avoir l’occasion de vous régaler une prochaine fois.';
    }

    /**
     * @return list<string> Stripe sessions still open on the links just deactivated.
     */
    private function deactivateLinks(CakeRequest $request): array
    {
        $openSessions = $request->depositLinks()
            ->where('status', DepositLink::STATUS_ACTIVE)
            ->whereNotNull('stripe_session_id')
            ->where('stripe_session_expires_at', '>', now())
            ->pluck('stripe_session_id')
            ->all();

        $request->depositLinks()
            ->where('status', DepositLink::STATUS_ACTIVE)
            ->update(['status' => DepositLink::STATUS_REPLACED]);

        return $openSessions;
    }

    /**
     * Called after the commit: a Stripe failure never blocks the artisan's answer.
     * A session that still gets paid is refunded by the webhook, which checks the link.
     *
     * @param  list<string>  $sessionIds
     */
    private function expireSessions(array $sessionIds): void
    {
        foreach ($sessionIds as $sessionId) {
            try {
                $this->stripe->expireCheckoutSession($sessionId);
            } catch (ApiErrorException $e) {
                Log::warning('Session Stripe non expirée après remplacement du lien', [
                    'session' => $sessionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}

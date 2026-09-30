<?php

namespace App\Actions;

use App\Enums\RequestStatus;
use App\Mail\DepositOffer;
use App\Mail\RequestDeclined;
use App\Models\CakeRequest;
use App\Models\DepositLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * The artisan's answer to a request (U7, F3): a priced offer with a deposit link,
 * or a refusal. Either way, at most one link stays active per request.
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

    /**
     * @param  array{total_cents: int, deposit_cents: int, allergens: list<string>, slot: string, message: ?string, validity_days: int}  $offer
     */
    public function propose(CakeRequest $request, array $offer): DepositLink
    {
        $token = bin2hex(random_bytes(32));

        return DB::transaction(function () use ($request, $offer, $token) {
            $this->deactivateLinks($request);

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
    }

    public function decline(CakeRequest $request, string $message): void
    {
        DB::transaction(function () use ($request, $message) {
            $this->deactivateLinks($request);
            $request->update(['status' => RequestStatus::Refusee, 'last_activity_at' => now()]);

            Mail::to($request->email)->queue(new RequestDeclined($request, $message));
        });
    }

    public static function defaultRefusal(CakeRequest $request): string
    {
        $date = $request->event_date->locale('fr')->translatedFormat('l j F Y');

        return "Bonjour {$request->name},\n\n"
            ."Merci beaucoup pour votre demande. Malheureusement, je ne peux pas réaliser votre gâteau pour le {$date}.\n\n"
            .'J’espère avoir l’occasion de vous régaler une prochaine fois.';
    }

    private function deactivateLinks(CakeRequest $request): void
    {
        $request->depositLinks()
            ->where('status', DepositLink::STATUS_ACTIVE)
            ->update(['status' => DepositLink::STATUS_REPLACED]);
    }
}

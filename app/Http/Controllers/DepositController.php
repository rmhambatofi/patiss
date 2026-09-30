<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Models\DepositLink;
use App\Models\Payment;
use App\Services\DepositCheckout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public deposit pages (F3, KTD4). None of them changes the state of a request:
 * only the Stripe webhook does.
 */
class DepositController extends Controller
{
    public function __construct(private DepositCheckout $checkout) {}

    public function show(string $token): View
    {
        $link = $this->checkout->findByToken($token) ?? abort(404);

        if ($blocker = $this->checkout->blocker($link)) {
            return $this->blocked($link, $blocker);
        }

        return view('acompte.show', [
            'link' => $link,
            'request' => $link->cakeRequest,
            'token' => $token,
        ]);
    }

    public function pay(Request $http, string $token): View|RedirectResponse
    {
        $link = $this->checkout->findByToken($token) ?? abort(404);

        if ($blocker = $this->checkout->blocker($link)) {
            return $this->blocked($link, $blocker);
        }

        $http->validate(
            ['cgv' => ['accepted']],
            ['cgv.accepted' => 'Veuillez accepter les conditions générales de vente pour payer l’acompte.'],
        );

        return redirect()->away($this->checkout->checkoutUrl($link, $token), 303);
    }

    /**
     * Back from Stripe: shows where the payment stands, as recorded by the webhook.
     */
    public function return(Request $http): View
    {
        $sessionId = (string) $http->query('session_id', '');
        abort_if($sessionId === '', 404);

        $payment = Payment::with('cakeRequest')->where('stripe_session_id', $sessionId)->first();
        $link = $payment === null ? DepositLink::with('cakeRequest')->where('stripe_session_id', $sessionId)->first() : null;
        abort_if($payment === null && $link === null, 404);

        $request = $payment?->cakeRequest ?? $link?->cakeRequest;

        $state = match (true) {
            $payment === null => 'pending',
            $payment->status === Payment::STATUS_PAID => 'reserved',
            $request?->status === RequestStatus::Remboursee => 'full',
            default => 'refunded',
        };

        return view('acompte.merci', ['state' => $state, 'reference' => $request?->reference]);
    }

    private function blocked(DepositLink $link, string $blocker): View
    {
        $reference = $link->cakeRequest->reference;

        if ($blocker === DepositCheckout::FULL) {
            return view('acompte.complet', ['reference' => $reference]);
        }

        return view('acompte.expire', ['reference' => $reference, 'reason' => $blocker]);
    }
}

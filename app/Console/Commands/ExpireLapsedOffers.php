<?php

namespace App\Console\Commands;

use App\Enums\RequestStatus;
use App\Models\CakeRequest;
use App\Models\DepositLink;
use App\Services\DepositCheckout;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

/**
 * Proposee --> Expiree once the offer can no longer be paid: its link lapsed or
 * the event date passed. A Stripe session opened just before the link expired
 * stays payable for its 30 minutes (KTD4) and the webhook only reserves
 * Proposee requests, hence the grace after the link's expiry.
 */
class ExpireLapsedOffers extends Command
{
    protected $signature = 'requests:expire-offers';

    protected $description = 'Passe en « Expirée » les propositions dont le lien ou la date sont dépassés';

    public function handle(): int
    {
        $linkCutoff = now()->subMinutes(DepositCheckout::SESSION_MINUTES + 1);

        // Base query: neither last_activity_at nor updated_at move, so the purge clock (R19) is unchanged.
        $expired = CakeRequest::query()
            ->where('status', RequestStatus::Proposee)
            ->where(fn (Builder $query) => $query
                ->where('event_date', '<', today()->toDateString())
                ->orWhereDoesntHave('depositLinks', fn (Builder $q) => $q
                    ->where('status', DepositLink::STATUS_ACTIVE)
                    ->where('expires_at', '>=', $linkCutoff)))
            ->toBase()
            ->update(['status' => RequestStatus::Expiree->value]);

        $this->info("Propositions expirées : {$expired}");

        return self::SUCCESS;
    }
}

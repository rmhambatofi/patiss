<?php

namespace App\Console\Commands;

use App\Enums\RequestStatus;
use App\Models\CakeRequest;
use App\Models\DepositLink;
use App\Models\Payment;
use App\Models\RequestStat;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Deletes requests 3 months after the event, or 3 months after the last
 * activity when never paid (R19, KTD9), and keeps one anonymous row each.
 */
class PurgeExpiredRequests extends Command
{
    protected $signature = 'requests:purge';

    protected $description = 'Supprime les demandes échues (R19) et garde une ligne statistique anonyme';

    public function handle(): int
    {
        $purged = 0;
        $failed = 0;

        foreach ($this->expiredQuery()->orderBy('id')->pluck('id') as $id) {
            try {
                DB::transaction(fn () => $this->purge(CakeRequest::findOrFail($id)));
                $purged++;
            } catch (Throwable $e) {
                // One broken request must not keep the others' personal data alive.
                $failed++;
                report($e);
            }
        }

        Log::info('Purge des demandes échues', ['purged' => $purged, 'failed' => $failed]);
        $this->info("Demandes purgées : {$purged}");

        if ($failed > 0) {
            $this->error("Demandes en échec : {$failed}");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Paid or refunded: 3 months after the event. Otherwise: 3 months after
     * the last activity, or the last update when none was recorded.
     *
     * @return Builder<CakeRequest>
     */
    private function expiredQuery(): Builder
    {
        $eventCutoff = today()->subMonths(3)->toDateString();
        $activityCutoff = now()->subMonths(3);

        return CakeRequest::query()->where(function (Builder $query) use ($eventCutoff, $activityCutoff) {
            $query
                ->where(fn (Builder $q) => $q
                    ->whereIn('status', [RequestStatus::Reservee, RequestStatus::Remboursee])
                    ->where('event_date', '<', $eventCutoff))
                ->orWhere(fn (Builder $q) => $q
                    ->whereIn('status', [RequestStatus::Nouvelle, RequestStatus::Proposee, RequestStatus::Expiree, RequestStatus::Refusee])
                    ->where(fn (Builder $q) => $q
                        ->where('last_activity_at', '<', $activityCutoff)
                        ->orWhere(fn (Builder $q) => $q
                            ->whereNull('last_activity_at')
                            ->where('updated_at', '<', $activityCutoff))));
        });
    }

    private function purge(CakeRequest $request): void
    {
        $payments = Payment::where('cake_request_id', $request->id);

        RequestStat::create([
            'kind' => $request->kind,
            'cake_type_id' => $request->cake_type_id,
            'source' => $request->source,
            'department_code' => $request->commune?->department_code,
            'fulfilment' => $request->fulfilment,
            'total_cents' => $request->depositLinks()->where('status', DepositLink::STATUS_PAID)->value('total_cents'),
            'paid' => $request->status === RequestStatus::Reservee
                || (clone $payments)->where('status', Payment::STATUS_PAID)->exists(),
            'event_month' => $request->event_date->format('Y-m'),
        ]);

        // On the local disk, an absent directory is a no-op, not an error.
        Storage::disk('local')->deleteDirectory('requests/'.$request->reference);

        // Stripe ids, amount, status and refund stay for accounting; the e-mail goes.
        (clone $payments)->update(['payer_email' => null]);

        $request->depositLinks()->delete();
        $request->delete();
    }
}

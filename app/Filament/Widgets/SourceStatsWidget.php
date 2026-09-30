<?php

namespace App\Filament\Widgets;

use App\Enums\RequestStatus;
use App\Enums\Source;
use App\Models\CakeRequest;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * "Comment avez-vous connu l'artisan ?" over the last 30 days (R8, Success Criteria).
 */
class SourceStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Sources des demandes, 30 derniers jours';

    protected function getStats(): array
    {
        $rows = CakeRequest::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('source, count(*) as requests, sum(case when status = ? then 1 else 0 end) as paid', [RequestStatus::Reservee->value])
            ->groupBy('source')
            ->get()
            ->keyBy(fn (CakeRequest $row) => $row->source->value);

        return collect(Source::cases())
            ->map(function (Source $source) use ($rows) {
                $requests = (int) ($rows[$source->value]->requests ?? 0);
                $paid = (int) ($rows[$source->value]->paid ?? 0);

                return Stat::make($source->label(), $requests.' '.($requests > 1 ? 'demandes' : 'demande'))
                    ->description($paid.' '.($paid > 1 ? 'acomptes payés' : 'acompte payé'));
            })
            ->all();
    }
}

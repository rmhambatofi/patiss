<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Models\CakeRequest;
use App\Models\CapacityOverride;
use App\Models\DepositLink;
use App\Settings\ShopSettings;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Daily capacity (R20, KTD5): a date is full once its paid deposits reach its capacity.
 * Open links are only shown, never counted. Callers needing a lock take their own.
 */
class CapacityGuard
{
    public function __construct(private ShopSettings $settings) {}

    public function capacityFor(CarbonInterface|string $date): int
    {
        return CapacityOverride::whereDate('date', $this->day($date))->value('capacity')
            ?? $this->settings->default_daily_capacity;
    }

    public function paidCount(CarbonInterface|string $date): int
    {
        return CakeRequest::whereDate('event_date', $this->day($date))
            ->where('status', RequestStatus::Reservee)
            ->count();
    }

    public function openLinksCount(CarbonInterface|string $date): int
    {
        return DepositLink::open()
            ->whereHas('cakeRequest', fn ($query) => $query->whereDate('event_date', $this->day($date)))
            ->count();
    }

    public function isFull(CarbonInterface|string $date): bool
    {
        return $this->paidCount($date) >= $this->capacityFor($date);
    }

    /** "Acomptes payés 1 / capacité 2 · liens ouverts 1" */
    public function loadLabel(CarbonInterface|string $date): string
    {
        return sprintf(
            'Acomptes payés %d / capacité %d · liens ouverts %d',
            $this->paidCount($date),
            $this->capacityFor($date),
            $this->openLinksCount($date),
        );
    }

    private function day(CarbonInterface|string $date): string
    {
        return Carbon::parse($date)->toDateString();
    }
}

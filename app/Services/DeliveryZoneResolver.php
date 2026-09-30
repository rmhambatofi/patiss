<?php

namespace App\Services;

use App\Models\Commune;
use App\Models\DeliveryTier;

/**
 * Finds the delivery tier that applies to a commune (R16, KTD6).
 *
 * Result shape: the matching DeliveryTier (read `price_cents` for the fee
 * and `max_km` for the zone), or null meaning "retrait uniquement" (pickup
 * only). The tier is the smallest one whose max_km is greater than or equal
 * to the commune's distance from Dammartin-en-Goële, so boundaries are
 * inclusive (25 km exactly falls in the 25 km tier). Null is returned when
 * the commune is unknown, when no tier is configured, or when the commune is
 * beyond the last tier.
 */
class DeliveryZoneResolver
{
    public function resolve(?Commune $commune): ?DeliveryTier
    {
        if ($commune === null) {
            return null;
        }

        $distance = (float) $commune->distance_km;

        return DeliveryTier::query()
            ->orderBy('max_km')
            ->get()
            ->first(fn (DeliveryTier $tier) => (float) $tier->max_km >= $distance);
    }
}

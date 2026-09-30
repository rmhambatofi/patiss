<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Services\DeliveryZoneResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommuneSearchController extends Controller
{
    /**
     * Up to 10 communes matching a name or postal-code prefix, each with its
     * delivery fee (in cents) or the pickup-only flag. Local table only.
     */
    public function __invoke(Request $request, DeliveryZoneResolver $resolver): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $communes = Commune::query()
            ->matching($term)
            ->orderBy('search_name')
            ->limit(10)
            ->get();

        return response()->json($communes->map(function (Commune $commune) use ($resolver) {
            $tier = $resolver->resolve($commune);

            return [
                'insee_code' => $commune->insee_code,
                'name' => $commune->name,
                'postal_codes' => $commune->postal_codes,
                'pickup_only' => $tier === null,
                'delivery_fee_cents' => $tier?->price_cents,
            ];
        })->values());
    }
}

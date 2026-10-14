<?php

namespace App\Console\Commands;

use App\Models\Commune;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Imports the communes of Île-de-France and Oise from geo.api.gouv.fr with
 * their distance from Dammartin-en-Goële (KTD6). Safe to run again: communes
 * are upserted by INSEE code and those the API no longer returns are removed.
 */
class ImportCommunes extends Command
{
    protected $signature = 'communes:import';

    protected $description = 'Importe les communes d\'Île-de-France et de l\'Oise et leur distance à Dammartin-en-Goële';

    private const EARTH_RADIUS_KM = 6371.0;

    public function handle(): int
    {
        $origin = config('shop.origin');
        $rows = [];

        foreach (config('shop.departments') as $department) {
            $communes = Http::acceptJson()
                ->timeout(30)
                ->get("https://geo.api.gouv.fr/departements/{$department}/communes", [
                    'fields' => 'nom,code,codesPostaux,centre',
                    'format' => 'json',
                ])
                ->throw()
                ->json();

            foreach ($communes as $commune) {
                if (! isset($commune['centre']['coordinates'])) {
                    $this->warn("Commune {$commune['code']} ignorée : pas de centre.");

                    continue;
                }

                [$longitude, $latitude] = $commune['centre']['coordinates'];

                $rows[$commune['code']] = [
                    'insee_code' => $commune['code'],
                    'name' => $commune['nom'],
                    'search_name' => Commune::normalizeForSearch($commune['nom']),
                    'postal_codes' => json_encode(array_values($commune['codesPostaux'] ?? [])),
                    'department_code' => (string) $department,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'distance_km' => round($this->distanceKm($origin['latitude'], $origin['longitude'], $latitude, $longitude), 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::transaction(function () use ($rows) {
            foreach (array_chunk($rows, 500) as $chunk) {
                Commune::upsert(
                    $chunk,
                    ['insee_code'],
                    ['name', 'search_name', 'postal_codes', 'department_code', 'latitude', 'longitude', 'distance_km', 'updated_at'],
                );
            }

            Commune::whereNotIn('insee_code', array_keys($rows))->delete();
        });

        $this->info(count($rows).' communes importées.');

        return self::SUCCESS;
    }

    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * self::EARTH_RADIUS_KM * asin(sqrt($a));
    }
}

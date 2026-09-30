<?php

use App\Models\Commune;
use Illuminate\Support\Facades\Http;

function fakeGeoApi(array $overrides = []): void
{
    $responses = [];

    foreach (['75', '77', '78', '91', '92', '93', '94', '95', '60'] as $code) {
        $fixture = base_path("tests/Fixtures/geo/communes-{$code}.json");
        $body = file_exists($fixture) ? json_decode(file_get_contents($fixture), true) : [];
        $responses["geo.api.gouv.fr/departements/{$code}/communes*"] = $overrides[$code] ?? Http::response($body);
    }

    Http::preventStrayRequests();
    Http::fake($responses);
}

it('imports the communes of Île-de-France and Oise with their distance from Dammartin-en-Goële', function () {
    fakeGeoApi();

    $this->artisan('communes:import')->assertSuccessful();

    expect(Commune::count())->toBe(4);

    $meaux = Commune::where('insee_code', '77284')->firstOrFail();
    expect($meaux->name)->toBe('Meaux')
        ->and($meaux->postal_codes)->toBe(['77100'])
        ->and($meaux->department_code)->toBe('77')
        ->and((float) $meaux->latitude)->toEqualWithDelta(48.9573, 0.0001)
        ->and((float) $meaux->longitude)->toEqualWithDelta(2.9035, 0.0001)
        ->and((float) $meaux->distance_km)->toEqualWithDelta(19.45, 0.05);

    expect((float) Commune::where('insee_code', '77153')->value('distance_km'))->toEqualWithDelta(0.0, 0.05);
    expect((float) Commune::where('insee_code', '75056')->value('distance_km'))->toEqualWithDelta(32.19, 0.05);
    expect(Commune::where('insee_code', '60612')->value('department_code'))->toBe('60');
    expect(Commune::where('insee_code', '75056')->first()->postal_codes)->toHaveCount(21);

    Http::assertSentCount(9);
});

it('leaves the same number of communes when run twice', function () {
    fakeGeoApi();

    $this->artisan('communes:import')->assertSuccessful();
    $this->artisan('communes:import')->assertSuccessful();

    expect(Commune::count())->toBe(4);
});

it('removes communes the API no longer returns', function () {
    fakeGeoApi(['60' => Http::sequence()
        ->push(json_decode(file_get_contents(base_path('tests/Fixtures/geo/communes-60.json')), true))
        ->push([]),
    ]);

    $this->artisan('communes:import')->assertSuccessful();
    expect(Commune::where('insee_code', '60612')->exists())->toBeTrue();

    $this->artisan('communes:import')->assertSuccessful();

    expect(Commune::where('insee_code', '60612')->exists())->toBeFalse()
        ->and(Commune::count())->toBe(3);
});

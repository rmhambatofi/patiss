<?php

use App\Models\Commune;
use App\Models\DeliveryTier;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

it('finds Dammartin-en-Goële from an accent- and case-insensitive name prefix', function () {
    Commune::factory()->create(['name' => 'Dammartin-en-Goële', 'insee_code' => '77153', 'postal_codes' => ['77230'], 'distance_km' => 0]);
    Commune::factory()->create(['name' => 'Meaux', 'insee_code' => '77284', 'postal_codes' => ['77100']]);

    $this->getJson(route('communes.search', ['q' => 'DAMMAR']))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.name', 'Dammartin-en-Goële')
        ->assertJsonPath('0.insee_code', '77153')
        ->assertJsonPath('0.postal_codes', ['77230']);

    $this->getJson(route('communes.search', ['q' => 'dammartin en goele']))
        ->assertJsonPath('0.insee_code', '77153');
});

it('finds communes by postal code prefix', function () {
    Commune::factory()->create(['name' => 'Paris', 'insee_code' => '75056', 'postal_codes' => ['75001', '75116']]);
    Commune::factory()->create(['name' => 'Meaux', 'insee_code' => '77284', 'postal_codes' => ['77100']]);

    $this->getJson(route('communes.search', ['q' => '7511']))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.insee_code', '75056');
});

it('returns nothing for an empty or one-character query', function (?string $q) {
    Commune::factory()->create(['name' => 'Dammartin-en-Goële']);

    $this->getJson(route('communes.search', ['q' => $q]))
        ->assertOk()
        ->assertExactJson([]);
})->with([null, '', 'd']);

it('returns at most 10 communes', function () {
    foreach (range(1, 12) as $i) {
        Commune::factory()->create(['name' => "Saint-Village {$i}"]);
    }

    $this->getJson(route('communes.search', ['q' => 'saint']))->assertJsonCount(10);
});

// Covers AE2.
it('shows the fee of the zone for an Oise commune within a tier', function () {
    DeliveryTier::factory()->create(['max_km' => 10, 'price_cents' => 500]);
    DeliveryTier::factory()->create(['max_km' => 25, 'price_cents' => 1200]);
    DeliveryTier::factory()->create(['max_km' => 40, 'price_cents' => 2000]);
    Commune::factory()->create(['name' => 'Senlis', 'insee_code' => '60612', 'department_code' => '60', 'postal_codes' => ['60300'], 'distance_km' => 18]);

    $this->getJson(route('communes.search', ['q' => 'senl']))
        ->assertJsonPath('0.insee_code', '60612')
        ->assertJsonPath('0.pickup_only', false)
        ->assertJsonPath('0.delivery_fee_cents', 1200);
});

it('flags pickup only for a commune beyond the last tier', function () {
    DeliveryTier::factory()->create(['max_km' => 40, 'price_cents' => 2000]);
    Commune::factory()->create(['name' => 'Beauvais', 'postal_codes' => ['60000'], 'distance_km' => 62.3]);

    $this->getJson(route('communes.search', ['q' => 'beauv']))
        ->assertJsonPath('0.pickup_only', true)
        ->assertJsonPath('0.delivery_fee_cents', null);
});

it('lists the configured tiers on the public delivery page', function () {
    DeliveryTier::factory()->create(['max_km' => 25, 'price_cents' => 1200]);
    DeliveryTier::factory()->create(['max_km' => 10, 'price_cents' => 500]);

    $this->get(route('delivery.index'))
        ->assertOk()
        ->assertSee('Retrait gratuit')
        ->assertSeeInOrder(["jusqu'à 10 km : 5 €", "jusqu'à 25 km : 12 €"])
        ->assertSee('Dammartin-en-Goële')
        ->assertSee('uniquement le retrait');
});

it('says delivery tariffs are coming soon when no tier is configured', function () {
    $this->get(route('delivery.index'))
        ->assertOk()
        ->assertSee('Tarifs de livraison bientôt disponibles')
        ->assertDontSee('jusqu\'à');
});

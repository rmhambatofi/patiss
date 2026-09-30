<?php

use App\Filament\Resources\DeliveryTiers\Pages\CreateDeliveryTier;
use App\Filament\Resources\DeliveryTiers\Pages\ListDeliveryTiers;
use App\Models\DeliveryTier;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['has_email_authentication' => true]));
});

it('saves a tier entered in km and euros', function () {
    Livewire::test(CreateDeliveryTier::class)
        ->fillForm(['max_km' => '12,5', 'price_cents' => '7,50'])
        ->call('create')
        ->assertHasNoFormErrors();

    $tier = DeliveryTier::sole();
    expect($tier->max_km)->toBe('12.50')->and($tier->price_cents)->toBe(750);
});

it('refuses a negative distance or an empty price with a validation message', function (array $data, string $field) {
    Livewire::test(CreateDeliveryTier::class)
        ->fillForm(['max_km' => '10', 'price_cents' => '5', ...$data])
        ->call('create')
        ->assertHasFormErrors([$field]);

    expect(DeliveryTier::count())->toBe(0);
})->with([
    'distance négative' => [['max_km' => '-5'], 'max_km'],
    'distance nulle' => [['max_km' => '0'], 'max_km'],
    'prix vide' => [['price_cents' => ''], 'price_cents'],
    'prix négatif' => [['price_cents' => '-2'], 'price_cents'],
]);

it('lists tiers as "jusqu\'à N km : X €" ordered by distance', function () {
    DeliveryTier::factory()->create(['max_km' => 40, 'price_cents' => 2500]);
    DeliveryTier::factory()->create(['max_km' => 10, 'price_cents' => 500]);

    Livewire::test(ListDeliveryTiers::class)
        ->assertSeeInOrder(["jusqu'à 10 km", '5 €', "jusqu'à 40 km", '25 €']);
});

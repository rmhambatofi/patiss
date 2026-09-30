<?php

use App\Models\Commune;
use App\Models\DeliveryTier;
use App\Services\DeliveryZoneResolver;

function seedStandardTiers(): void
{
    DeliveryTier::factory()->create(['max_km' => 40, 'price_cents' => 2000]);
    DeliveryTier::factory()->create(['max_km' => 10, 'price_cents' => 500]);
    DeliveryTier::factory()->create(['max_km' => 25, 'price_cents' => 1200]);
}

function feeAt(float $km): ?int
{
    $commune = Commune::factory()->create(['distance_km' => $km]);

    return app(DeliveryZoneResolver::class)->resolve($commune)?->price_cents;
}

it('gives the fee of the smallest tier covering the distance, boundaries inclusive', function () {
    seedStandardTiers();

    expect(feeAt(8))->toBe(500)
        ->and(feeAt(10))->toBe(500)
        ->and(feeAt(25))->toBe(1200)
        ->and(feeAt(39.9))->toBe(2000)
        ->and(feeAt(40))->toBe(2000);
});

it('gives pickup only beyond the last tier', function () {
    seedStandardTiers();

    expect(feeAt(41))->toBeNull();
});

it('gives pickup only when no tier is configured', function () {
    expect(feeAt(3))->toBeNull();
});

it('gives pickup only for an unknown commune', function () {
    seedStandardTiers();

    expect(app(DeliveryZoneResolver::class)->resolve(null))->toBeNull();
});

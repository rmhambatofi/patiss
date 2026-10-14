<?php

use App\Enums\RequestStatus;
use App\Models\CakeRequest;
use App\Models\CapacityOverride;
use App\Models\DepositLink;
use App\Services\CapacityGuard;
use App\Settings\ShopSettings;
use Illuminate\Support\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-10-01 10:00:00', 'Europe/Paris'));
    $settings = app(ShopSettings::class);
    $settings->default_daily_capacity = 2;
    $settings->save();

    CakeRequest::factory()->count(2)->create(['event_date' => '2026-11-14', 'status' => RequestStatus::Reservee]);
    // Other statuses and other dates never count as paid.
    CakeRequest::factory()->create(['event_date' => '2026-11-14', 'status' => RequestStatus::Proposee]);
    CakeRequest::factory()->create(['event_date' => '2026-11-15', 'status' => RequestStatus::Reservee]);
});

afterEach(fn () => Carbon::setTestNow());

it('is full with a capacity of 2 and 2 paid deposits on the 14th', function () {
    $guard = app(CapacityGuard::class);

    expect($guard->capacityFor('2026-11-14'))->toBe(2)
        ->and($guard->paidCount('2026-11-14'))->toBe(2)
        ->and($guard->isFull(Carbon::parse('2026-11-14')))->toBeTrue()
        ->and($guard->isFull('2026-11-15'))->toBeFalse();
});

it('is available when that date has its own capacity of 3', function () {
    CapacityOverride::create(['date' => '2026-11-14', 'capacity' => 3]);

    $guard = app(CapacityGuard::class);
    expect($guard->capacityFor('2026-11-14'))->toBe(3)
        ->and($guard->isFull('2026-11-14'))->toBeFalse();
});

it('shows open links without counting them toward full', function () {
    CapacityOverride::create(['date' => '2026-11-14', 'capacity' => 3]);
    $onDate = fn () => CakeRequest::factory()->create(['event_date' => '2026-11-14'])->id;
    DepositLink::factory()->count(2)->create(['cake_request_id' => $onDate]);
    DepositLink::factory()->create(['cake_request_id' => $onDate, 'expires_at' => now()->subMinute()]);
    DepositLink::factory()->create(['cake_request_id' => $onDate, 'status' => DepositLink::STATUS_REPLACED]);

    $guard = app(CapacityGuard::class);
    expect($guard->openLinksCount('2026-11-14'))->toBe(2)
        ->and($guard->openLinksCount('2026-11-15'))->toBe(0)
        ->and($guard->isFull('2026-11-14'))->toBeFalse();
});

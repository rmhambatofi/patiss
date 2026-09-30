<?php

use App\Enums\RequestStatus;
use App\Models\CakeRequest;
use App\Models\DepositLink;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-10-15 12:00:00', 'Europe/Paris'));
});

afterEach(fn () => Carbon::setTestNow());

/**
 * A proposed request whose only active link expires at the given time.
 */
function proposedWithLinkExpiringAt(Carbon $expiresAt, array $attributes = []): CakeRequest
{
    $request = CakeRequest::factory()->create(array_merge([
        'status' => RequestStatus::Proposee,
        'event_date' => '2026-11-14',
        'last_activity_at' => Carbon::parse('2026-10-10 09:00:00', 'Europe/Paris'),
    ], $attributes));

    DepositLink::factory()->for($request)->create([
        'status' => DepositLink::STATUS_ACTIVE,
        'expires_at' => $expiresAt,
    ]);

    return $request;
}

it('expires an offer whose link lapsed beyond the 30-minute checkout grace, without touching the purge clock', function () {
    $request = proposedWithLinkExpiringAt(now()->subMinutes(32));

    $this->artisan('requests:expire-offers')->assertSuccessful();

    $request->refresh();
    expect($request->status)->toBe(RequestStatus::Expiree)
        ->and($request->last_activity_at->equalTo(Carbon::parse('2026-10-10 09:00:00', 'Europe/Paris')))->toBeTrue();
});

it('keeps an offer proposed while a checkout session opened just before link expiry may still be paid', function () {
    $request = proposedWithLinkExpiringAt(now()->subMinutes(20));

    $this->artisan('requests:expire-offers')->assertSuccessful();

    expect($request->refresh()->status)->toBe(RequestStatus::Proposee);
});

it('keeps an offer proposed while its link is still valid', function () {
    $request = proposedWithLinkExpiringAt(now()->addDay());

    $this->artisan('requests:expire-offers')->assertSuccessful();

    expect($request->refresh()->status)->toBe(RequestStatus::Proposee);
});

it('expires an offer whose event date has passed, even with a valid link', function () {
    $request = proposedWithLinkExpiringAt(now()->addDay(), ['event_date' => '2026-10-14']);
    $today = proposedWithLinkExpiringAt(now()->addDay(), ['event_date' => '2026-10-15']);

    $this->artisan('requests:expire-offers')->assertSuccessful();

    expect($request->refresh()->status)->toBe(RequestStatus::Expiree)
        ->and($today->refresh()->status)->toBe(RequestStatus::Proposee);
});

it('expires a proposed request left without any active link, and leaves other statuses alone', function () {
    $orphan = CakeRequest::factory()->create(['status' => RequestStatus::Proposee, 'event_date' => '2026-11-14']);
    DepositLink::factory()->for($orphan)->create(['status' => DepositLink::STATUS_REPLACED, 'expires_at' => now()->addDay()]);
    $reserved = proposedWithLinkExpiringAt(now()->subDay(), ['status' => RequestStatus::Reservee]);
    $new = CakeRequest::factory()->create(['status' => RequestStatus::Nouvelle, 'event_date' => '2026-10-01']);

    $this->artisan('requests:expire-offers')->assertSuccessful();

    expect($orphan->refresh()->status)->toBe(RequestStatus::Expiree)
        ->and($reserved->refresh()->status)->toBe(RequestStatus::Reservee)
        ->and($new->refresh()->status)->toBe(RequestStatus::Nouvelle);
});

it('schedules the offer expiry hourly without overlapping', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn (Event $event) => str_contains($event->command, 'requests:expire-offers'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 * * * *')
        ->and($event->withoutOverlapping)->toBeTrue();
});

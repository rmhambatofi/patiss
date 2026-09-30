<?php

use App\Enums\Fulfilment;
use App\Enums\RequestStatus;
use App\Enums\Source;
use App\Models\CakeRequest;
use App\Models\Commune;
use App\Models\DepositLink;
use App\Models\Payment;
use App\Models\RequestPhoto;
use App\Models\RequestStat;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-10-15 03:30:00', 'Europe/Paris'));
    Storage::fake('local');
});

afterEach(fn () => Carbon::setTestNow());

/**
 * A reserved request whose deposit was paid, with one private photo on disk.
 */
function reservedRequestWithPayment(string $reference, string $eventDate): CakeRequest
{
    $commune = Commune::factory()->create(['name' => 'Meaux', 'department_code' => '77']);

    $request = CakeRequest::factory()->create([
        'reference' => $reference,
        'name' => 'Léa Martin',
        'email' => 'lea@example.test',
        'phone' => '06 12 34 56 78',
        'allergies' => 'Arachide',
        'customization' => 'Joyeux anniversaire Léa',
        'event_date' => $eventDate,
        'fulfilment' => Fulfilment::Delivery,
        'commune_id' => $commune->id,
        'delivery_name' => 'Léa Martin',
        'delivery_line1' => '3 rue des Lilas',
        'delivery_postal_code' => '77100',
        'delivery_city' => 'Meaux',
        'source' => Source::Instagram,
        'status' => RequestStatus::Reservee,
        'last_activity_at' => Carbon::parse($eventDate)->subDays(10),
    ]);

    $link = DepositLink::factory()->for($request)->create([
        'total_cents' => 8500,
        'deposit_cents' => 2550,
        'status' => DepositLink::STATUS_PAID,
    ]);
    DepositLink::factory()->for($request)->create(['status' => DepositLink::STATUS_REPLACED, 'total_cents' => 9000]);

    Payment::create([
        'deposit_link_id' => $link->id,
        'cake_request_id' => $request->id,
        'stripe_session_id' => 'cs_test_'.$reference,
        'payment_intent_id' => 'pi_test_'.$reference,
        'amount_cents' => 2550,
        'status' => Payment::STATUS_PAID,
        'payer_email' => 'lea@example.test',
    ]);

    $path = "requests/{$reference}/photo-1.jpg";
    Storage::disk('local')->put($path, 'jpeg bytes');
    $request->photos()->create(['kind' => RequestPhoto::KIND_TO_REPRODUCE, 'path' => $path, 'original_name' => 'gateau.jpg']);

    return $request;
}

it('purges a reserved request whose event was 3 months and 1 day ago, with its photos, and leaves an anonymous row', function () {
    $request = reservedRequestWithPayment('RSV234', now()->subMonths(3)->subDay()->toDateString());
    $cakeTypeId = $request->cake_type_id;

    $this->artisan('requests:purge')->expectsOutputToContain('1')->assertSuccessful();

    expect(CakeRequest::count())->toBe(0)
        ->and(RequestPhoto::count())->toBe(0)
        ->and(DepositLink::count())->toBe(0)
        ->and(Storage::disk('local')->exists('requests/RSV234'))->toBeFalse()
        ->and(Storage::disk('local')->exists('requests/RSV234/photo-1.jpg'))->toBeFalse();

    $stat = RequestStat::sole();
    expect($stat->kind)->toBe(CakeRequest::KIND_TYPE)
        ->and($stat->cake_type_id)->toBe($cakeTypeId)
        ->and($stat->source)->toBe(Source::Instagram)
        ->and($stat->department_code)->toBe('77')
        ->and($stat->fulfilment)->toBe(Fulfilment::Delivery)
        ->and($stat->total_cents)->toBe(8500)
        ->and($stat->paid)->toBeTrue()
        ->and($stat->event_month)->toBe('2026-07');
});

it('stores no personal data in the anonymous row', function () {
    reservedRequestWithPayment('ANO234', now()->subMonths(4)->toDateString());

    $this->artisan('requests:purge')->assertSuccessful();

    $row = (array) DB::table('request_stats')->sole();
    expect(array_keys($row))->toEqualCanonicalizing([
        'id', 'kind', 'cake_type_id', 'source', 'department_code', 'fulfilment',
        'total_cents', 'paid', 'event_month', 'created_at',
    ]);

    $serialized = json_encode($row);
    foreach (['Léa', 'lea@example.test', '06 12 34 56 78', 'Arachide', 'anniversaire', 'Lilas', 'Meaux', 'ANO234', '2026-06-15'] as $personal) {
        expect($serialized)->not->toContain($personal);
    }
});

it('keeps a reserved request whose event was 2 months ago', function () {
    $request = reservedRequestWithPayment('KEEP23', now()->subMonths(2)->toDateString());

    $this->artisan('requests:purge')->assertSuccessful();

    expect(CakeRequest::whereKey($request->id)->exists())->toBeTrue()
        ->and($request->photos()->count())->toBe(1)
        ->and($request->depositLinks()->count())->toBe(2)
        ->and(Storage::disk('local')->exists('requests/KEEP23/photo-1.jpg'))->toBeTrue()
        ->and(Payment::sole()->payer_email)->toBe('lea@example.test')
        ->and(RequestStat::count())->toBe(0);
});

it('purges a refused request without activity for 3 months and 1 day', function () {
    CakeRequest::factory()->create([
        'status' => RequestStatus::Refusee,
        'event_date' => now()->addMonth()->toDateString(),
        'last_activity_at' => now()->subMonths(3)->subDay(),
    ]);
    $recent = CakeRequest::factory()->create([
        'status' => RequestStatus::Refusee,
        'event_date' => now()->addMonth()->toDateString(),
        'last_activity_at' => now()->subMonths(2),
    ]);

    $this->artisan('requests:purge')->assertSuccessful();

    expect(CakeRequest::pluck('id')->all())->toBe([$recent->id])
        ->and(RequestStat::sole()->paid)->toBeFalse()
        ->and(RequestStat::sole()->total_cents)->toBeNull();
});

it('falls back to updated_at when a never-paid request has no last activity', function () {
    CakeRequest::factory()->create([
        'status' => RequestStatus::Nouvelle,
        'last_activity_at' => null,
        'updated_at' => now()->subMonths(4),
    ]);
    $fresh = CakeRequest::factory()->create([
        'status' => RequestStatus::Nouvelle,
        'last_activity_at' => null,
    ]);

    $this->artisan('requests:purge')->assertSuccessful();

    expect(CakeRequest::pluck('id')->all())->toBe([$fresh->id]);
});

it('keeps the Stripe ids and amount of a payment but drops the payer e-mail', function () {
    reservedRequestWithPayment('PAY234', now()->subMonths(4)->toDateString());

    $this->artisan('requests:purge')->assertSuccessful();

    $payment = Payment::sole();
    expect($payment->stripe_session_id)->toBe('cs_test_PAY234')
        ->and($payment->payment_intent_id)->toBe('pi_test_PAY234')
        ->and($payment->amount_cents)->toBe(2550)
        ->and($payment->status)->toBe(Payment::STATUS_PAID)
        ->and($payment->payer_email)->toBeNull()
        ->and($payment->cake_request_id)->toBeNull()
        ->and($payment->deposit_link_id)->toBeNull();
});

it('keeps purging the other requests when a private file is already gone', function () {
    $missing = reservedRequestWithPayment('MIS234', now()->subMonths(4)->toDateString());
    Storage::disk('local')->deleteDirectory('requests/MIS234');
    reservedRequestWithPayment('OTH234', now()->subMonths(4)->toDateString());

    $this->artisan('requests:purge')->assertSuccessful();

    expect(CakeRequest::count())->toBe(0)
        ->and(RequestStat::count())->toBe(2)
        ->and(Storage::disk('local')->exists('requests/OTH234'))->toBeFalse();
});

it('changes nothing when run twice the same day', function () {
    reservedRequestWithPayment('TWI234', now()->subMonths(4)->toDateString());
    $kept = reservedRequestWithPayment('TWK234', now()->subMonth()->toDateString());

    $this->artisan('requests:purge')->assertSuccessful();
    $stats = RequestStat::all()->toArray();
    $payments = DB::table('payments')->orderBy('id')->get()->toArray();

    $this->artisan('requests:purge')->expectsOutputToContain('0')->assertSuccessful();

    expect(RequestStat::all()->toArray())->toBe($stats)
        ->and(DB::table('payments')->orderBy('id')->get()->toArray())->toEqual($payments)
        ->and(CakeRequest::pluck('id')->all())->toBe([$kept->id]);
});

it('logs the number of purged requests', function () {
    Log::spy();
    reservedRequestWithPayment('LOG234', now()->subMonths(4)->toDateString());
    CakeRequest::factory()->create(['status' => RequestStatus::Expiree, 'last_activity_at' => now()->subMonths(5)]);

    $this->artisan('requests:purge')->assertSuccessful();

    Log::shouldHaveReceived('info')->withArgs(fn (string $message, array $context = []) => ($context['purged'] ?? null) === 2)->once();
});

it('is scheduled every day', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn (Event $event) => str_contains($event->command, 'requests:purge'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('30 3 * * *');
});

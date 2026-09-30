<?php

use App\Actions\AnswerCakeRequest;
use App\Enums\Allergen;
use App\Enums\Fulfilment;
use App\Enums\RequestStatus;
use App\Filament\Resources\CakeRequests\Pages\ViewCakeRequest;
use App\Models\CakeRequest;
use App\Models\CapacityOverride;
use App\Models\Commune;
use App\Models\DepositLink;
use App\Models\Payment;
use App\Models\User;
use App\Settings\ShopSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

const PICKUP_ADDRESS = '7 impasse des Tilleuls, 77230 Dammartin-en-Goële';

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-10-01 10:00:00', 'Europe/Paris'));
    config(['services.stripe.webhook_secret' => 'whsec_test_secret']);
    $this->stripe = fakeStripe();

    $settings = app(ShopSettings::class);
    $settings->pickup_address = PICKUP_ADDRESS;
    $settings->google_review_url = 'https://g.page/r/patiss-avis/review';
    $settings->notification_email = 'atelier@example.test';
    $settings->default_daily_capacity = 2;
    $settings->save();

    $this->request = CakeRequest::factory()->create([
        'reference' => 'ABC234',
        'name' => 'Léa Martin',
        'email' => 'client@example.test',
        'event_date' => '2026-11-14',
        'fulfilment' => Fulfilment::Pickup,
        'status' => RequestStatus::Proposee,
        'last_activity_at' => now()->subDays(2),
    ]);
    $this->link = DepositLink::factory()->for($this->request)->create([
        'total_cents' => 8500,
        'deposit_cents' => 2550,
        'allergens' => [Allergen::Gluten, Allergen::Nuts],
        'slot' => 'Samedi 14 novembre, retrait entre 10 h et 12 h',
    ]);
});

afterEach(fn () => Carbon::setTestNow());

/**
 * A second proposed request for the same Saturday, with its own link.
 */
function secondRequestSameDay(): DepositLink
{
    $request = CakeRequest::factory()->create([
        'reference' => 'XYZ789',
        'email' => 'second@example.test',
        'event_date' => '2026-11-14',
        'fulfilment' => Fulfilment::Pickup,
        'status' => RequestStatus::Proposee,
    ]);

    return DepositLink::factory()->for($request)->create(['deposit_cents' => 3000, 'total_cents' => 9000]);
}

function mailsTo(string $address): Collection
{
    return sentMails()->filter(fn (array $mail) => in_array($address, $mail['to'], true))->values();
}

it('rejects a webhook with an invalid signature with 400 and changes nothing', function () {
    postStripeWebhook(checkoutCompletedEvent($this->link), secret: 'whsec_forged')->assertStatus(400);

    expect($this->request->fresh()->status)->toBe(RequestStatus::Proposee)
        ->and($this->link->fresh()->status)->toBe(DepositLink::STATUS_ACTIVE)
        ->and(Payment::count())->toBe(0)
        ->and(sentMails())->toBeEmpty();
});

it('rejects every webhook while no signing secret is configured', function () {
    config(['services.stripe.webhook_secret' => null]);

    postStripeWebhook(checkoutCompletedEvent($this->link), secret: '')->assertStatus(400);

    expect(Payment::count())->toBe(0)
        ->and($this->request->fresh()->status)->toBe(RequestStatus::Proposee);
});

it('reserves the date, records the payment and sends the confirmation with pickup address, allergens, balance and review link', function () {
    postStripeWebhook(checkoutCompletedEvent($this->link))->assertOk();

    $request = $this->request->fresh();
    expect($request->status)->toBe(RequestStatus::Reservee)
        ->and($request->last_activity_at->equalTo(now()))->toBeTrue()
        ->and($this->link->fresh()->status)->toBe(DepositLink::STATUS_PAID);

    $payment = Payment::sole();
    expect($payment->stripe_session_id)->toBe('cs_test_link'.$this->link->id)
        ->and($payment->payment_intent_id)->toBe('pi_test_link'.$this->link->id)
        ->and($payment->amount_cents)->toBe(2550)
        ->and($payment->status)->toBe(Payment::STATUS_PAID)
        ->and($payment->deposit_link_id)->toBe($this->link->id)
        ->and($payment->cake_request_id)->toBe($this->request->id)
        ->and($payment->payer_email)->toBe('payeur@example.test')
        ->and($this->stripe->refunded)->toBe([]);

    $mails = mailsTo('client@example.test');
    expect($mails)->toHaveCount(1);
    expect($mails[0]['subject'])->toContain('ABC234')
        ->and($mails[0]['body'])
        ->toContain(PICKUP_ADDRESS)
        ->toContain('Samedi 14 novembre, retrait entre 10 h et 12 h')
        ->toContain('samedi 14 novembre 2026')
        ->toContain('gluten, fruits à coque')
        ->toContain('85 €')
        ->toContain('25,50 €')
        ->toContain('59,50 €')
        ->toContain('à régler à la remise du gâteau')
        ->toContain('https://g.page/r/patiss-avis/review');
});

it('leaves the review link out of the confirmation when none is set', function () {
    $settings = app(ShopSettings::class);
    $settings->google_review_url = null;
    $settings->save();

    postStripeWebhook(checkoutCompletedEvent($this->link))->assertOk();

    expect(mailsTo('client@example.test')[0]['body'])->toContain(PICKUP_ADDRESS)->not->toContain('avis Google');
});

it('processes the same webhook only once: one payment and one e-mail', function () {
    $event = checkoutCompletedEvent($this->link);

    postStripeWebhook($event)->assertOk();
    postStripeWebhook($event)->assertOk();

    expect(Payment::count())->toBe(1)
        ->and(mailsTo('client@example.test'))->toHaveCount(1)
        ->and($this->request->fresh()->status)->toBe(RequestStatus::Reservee);
});

it('reserves nothing when the session is completed but unpaid', function () {
    postStripeWebhook(checkoutCompletedEvent($this->link, ['payment_status' => 'unpaid']))->assertOk();

    expect($this->request->fresh()->status)->toBe(RequestStatus::Proposee)
        ->and($this->link->fresh()->status)->toBe(DepositLink::STATUS_ACTIVE)
        ->and(Payment::count())->toBe(0)
        ->and(sentMails())->toBeEmpty();
});

it('refunds in full a session paid after the artisan proposed a new link, without reserving or sending the address', function () {
    $event = checkoutCompletedEvent($this->link);
    app(AnswerCakeRequest::class)->propose($this->request, [
        'total_cents' => 9000,
        'deposit_cents' => 2700,
        'allergens' => ['gluten'],
        'slot' => 'Samedi, 11 h',
        'message' => null,
        'validity_days' => 3,
    ]);

    postStripeWebhook($event)->assertOk();

    expect($this->request->fresh()->status)->toBe(RequestStatus::Proposee)
        ->and($this->stripe->refunded)->toBe(['pi_test_link'.$this->link->id]);

    $payment = Payment::sole();
    expect($payment->status)->toBe(Payment::STATUS_REFUNDED)
        ->and($payment->refund_id)->toBe('re_test_fake1')
        ->and($payment->amount_cents)->toBe(2550);

    $refundMail = mailsTo('client@example.test')->firstWhere(fn ($mail) => str_contains($mail['subject'], 'rembours'));
    expect($refundMail)->not->toBeNull()
        ->and($refundMail['body'])->toContain('25,50 €')->toContain('n’est plus valable');

    $notice = mailsTo('atelier@example.test');
    expect($notice)->toHaveCount(1)
        ->and($notice[0]['body'])->toContain('ABC234')->toContain('25,50 €')->toContain(url('/atelier'));

    sentMails()->each(fn (array $mail) => expect($mail['body'])->not->toContain('impasse des Tilleuls'));
});

it('refunds in full a session paid after the request was declined, without reserving or sending the address', function () {
    $event = checkoutCompletedEvent($this->link);
    app(AnswerCakeRequest::class)->decline($this->request, 'Désolé, je ne suis pas disponible.');

    postStripeWebhook($event)->assertOk();

    expect($this->request->fresh()->status)->toBe(RequestStatus::Refusee)
        ->and($this->link->fresh()->status)->toBe(DepositLink::STATUS_REPLACED)
        ->and(Payment::sole()->status)->toBe(Payment::STATUS_REFUNDED)
        ->and($this->stripe->refunded)->toBe(['pi_test_link'.$this->link->id])
        ->and(mailsTo('atelier@example.test'))->toHaveCount(1);

    sentMails()->each(fn (array $mail) => expect($mail['body'])->not->toContain('impasse des Tilleuls'));
});

it('accepts a session opened before the link expired, but refunds one opened after', function () {
    $this->link->update(['expires_at' => now()->subMinutes(10)]);

    postStripeWebhook(checkoutCompletedEvent($this->link, ['created' => now()->subMinutes(20)->getTimestamp()]))->assertOk();
    expect($this->request->fresh()->status)->toBe(RequestStatus::Reservee);

    $other = secondRequestSameDay();
    $other->update(['expires_at' => now()->subMinutes(10)]);
    postStripeWebhook(checkoutCompletedEvent($other, ['created' => now()->subMinutes(5)->getTimestamp()]))->assertOk();

    expect($other->cakeRequest->fresh()->status)->not->toBe(RequestStatus::Reservee)
        ->and($this->stripe->refunded)->toBe(['pi_test_link'.$other->id]);
});

it('covers R20: two payments for a capacity-1 date leave one reserved and one refunded, client and artisan told', function () {
    CapacityOverride::create(['date' => '2026-11-14', 'capacity' => 1]);
    $second = secondRequestSameDay();

    postStripeWebhook(checkoutCompletedEvent($this->link))->assertOk();
    postStripeWebhook(checkoutCompletedEvent($second))->assertOk();

    expect(CakeRequest::where('status', RequestStatus::Reservee)->pluck('reference')->all())->toBe(['ABC234'])
        ->and(CakeRequest::where('status', RequestStatus::Remboursee)->pluck('reference')->all())->toBe(['XYZ789'])
        ->and($this->stripe->refunded)->toBe(['pi_test_link'.$second->id])
        ->and($second->fresh()->status)->not->toBe(DepositLink::STATUS_PAID)
        ->and(Payment::where('status', Payment::STATUS_PAID)->count())->toBe(1)
        ->and(Payment::where('status', Payment::STATUS_REFUNDED)->sole()->refund_id)->toBe('re_test_fake1');

    $secondMails = mailsTo('second@example.test');
    expect($secondMails)->toHaveCount(1)
        ->and($secondMails[0]['body'])->toContain('complète')->toContain('30 €')->not->toContain('impasse des Tilleuls');

    $notice = mailsTo('atelier@example.test');
    expect($notice)->toHaveCount(1)
        ->and($notice[0]['body'])->toContain('XYZ789')->toContain('30 €');
});

it('holds a per-date lock and a transaction while counting the capacity', function () {
    CapacityOverride::create(['date' => '2026-11-14', 'capacity' => 1]);
    $lockSeen = null;
    $levelSeen = null;
    CakeRequest::updating(function () use (&$lockSeen, &$levelSeen) {
        $lockSeen ??= ! Cache::lock('capacity:2026-11-14', 10)->get();
        $levelSeen ??= DB::transactionLevel();
    });

    postStripeWebhook(checkoutCompletedEvent($this->link))->assertOk();

    expect($lockSeen)->toBeTrue()->and($levelSeen)->toBeGreaterThan(0);
});

it('copies the Stripe shipping address into the request and flags a commune that differs from the priced one', function () {
    $commune = Commune::factory()->create(['name' => 'Meaux', 'postal_codes' => ['77100']]);
    $this->request->update(['fulfilment' => Fulfilment::Delivery, 'commune_id' => $commune->id, 'delivery_fee_cents' => 1000]);
    $shipping = fn (string $postal, string $city) => ['collected_information' => ['shipping_details' => [
        'name' => 'Léa Martin',
        'address' => ['line1' => '3 rue du Marché', 'line2' => 'Bât. B', 'postal_code' => $postal, 'city' => $city, 'country' => 'FR', 'state' => null],
    ]]];

    postStripeWebhook(checkoutCompletedEvent($this->link, $shipping('77100', 'Meaux')))->assertOk();

    $request = $this->request->fresh();
    expect($request->status)->toBe(RequestStatus::Reservee)
        ->and($request->delivery_name)->toBe('Léa Martin')
        ->and($request->delivery_line1)->toBe('3 rue du Marché')
        ->and($request->delivery_line2)->toBe('Bât. B')
        ->and($request->delivery_postal_code)->toBe('77100')
        ->and($request->delivery_city)->toBe('Meaux')
        ->and($request->delivery_commune_mismatch)->toBeFalse()
        ->and(DB::table('cake_requests')->where('id', $request->id)->value('delivery_line1'))->not->toContain('rue du Marché');

    $confirmation = mailsTo('client@example.test')[0]['body'];
    expect($confirmation)->toContain('3 rue du Marché')->toContain('77100 Meaux')->not->toContain('impasse des Tilleuls');

    $other = secondRequestSameDay();
    $other->cakeRequest->update(['fulfilment' => Fulfilment::Delivery, 'commune_id' => $commune->id, 'delivery_fee_cents' => 1000]);
    postStripeWebhook(checkoutCompletedEvent($other, $shipping('77230', 'Dammartin-en-Goële')))->assertOk();

    expect($other->cakeRequest->fresh()->delivery_commune_mismatch)->toBeTrue();

    $this->actingAs(User::factory()->create(['has_email_authentication' => true]));
    Livewire::test(ViewCakeRequest::class, ['record' => $other->cakeRequest->getRouteKey()])
        ->assertSee('3 rue du Marché')
        ->assertSee('Commune différente du tarif');
    Livewire::test(ViewCakeRequest::class, ['record' => $this->request->getRouteKey()])
        ->assertSee('3 rue du Marché')
        ->assertDontSee('Commune différente du tarif');
});

it('forgets the Stripe session of a link when Stripe reports it expired', function () {
    $this->link->update(['stripe_session_id' => 'cs_test_old', 'stripe_session_expires_at' => now()->addMinutes(3)]);

    postStripeWebhook([
        'id' => 'evt_test_expired',
        'object' => 'event',
        'type' => 'checkout.session.expired',
        'data' => ['object' => ['id' => 'cs_test_old', 'object' => 'checkout.session', 'status' => 'expired', 'payment_status' => 'unpaid']],
    ])->assertOk();

    $link = $this->link->fresh();
    expect($link->stripe_session_id)->toBeNull()
        ->and($link->stripe_session_expires_at)->toBeNull()
        ->and($link->status)->toBe(DepositLink::STATUS_ACTIVE);
});

it('acknowledges other event types without doing anything', function () {
    postStripeWebhook(['id' => 'evt_test_other', 'object' => 'event', 'type' => 'payment_intent.created', 'data' => ['object' => ['id' => 'pi_x', 'object' => 'payment_intent']]])
        ->assertOk();

    expect(Payment::count())->toBe(0)->and($this->request->fresh()->status)->toBe(RequestStatus::Proposee);
});

<?php

use App\Actions\AnswerCakeRequest;
use App\Enums\Allergen;
use App\Enums\Fulfilment;
use App\Enums\RequestStatus;
use App\Filament\Resources\CakeRequests\Pages\ViewCakeRequest;
use App\Models\CakeRequest;
use App\Models\CapacityOverride;
use App\Models\DepositLink;
use App\Models\Payment;
use App\Models\User;
use App\Settings\ShopSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;

const LINK_TOKEN = 'raw-token-0123456789abcdef0123456789abcdef';

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-10-01 10:00:00', 'Europe/Paris'));
    $this->stripe = fakeStripe();

    $settings = app(ShopSettings::class);
    $settings->pickup_address = '7 impasse des Tilleuls, 77230 Dammartin-en-Goële';
    $settings->default_daily_capacity = 2;
    $settings->save();

    $this->request = CakeRequest::factory()->create([
        'reference' => 'ABC234',
        'email' => 'client@example.test',
        'event_date' => '2026-11-14',
        'fulfilment' => Fulfilment::Pickup,
        'status' => RequestStatus::Proposee,
    ]);
    $this->link = DepositLink::factory()->for($this->request)->create([
        'token_hash' => DepositLink::hashToken(LINK_TOKEN),
        'total_cents' => 8500,
        'deposit_cents' => 2550,
        'allergens' => [Allergen::Gluten, Allergen::Nuts],
        'slot' => 'Samedi 14 novembre, retrait entre 10 h et 12 h',
        'expires_at' => now()->addDays(3),
    ]);
});

afterEach(fn () => Carbon::setTestNow());

function payLink(string $token = LINK_TOKEN): TestResponse
{
    return test()->post('/acompte/'.$token, ['cgv' => '1']);
}

it('shows the recap, the balance due at handover and the required CGV acceptance', function () {
    $this->get('/acompte/'.LINK_TOKEN)
        ->assertOk()
        ->assertSee('ABC234')
        ->assertSee('samedi 14 novembre 2026')
        ->assertSee('Samedi 14 novembre, retrait entre 10 h et 12 h')
        ->assertSee('85 €')
        ->assertSee('25,50 €')
        ->assertSee('59,50 €')
        ->assertSee('à régler à la remise du gâteau')
        ->assertSee('gluten, fruits à coque')
        ->assertSee('name="cgv"', false)
        ->assertSee(route('legal.terms'), false)
        ->assertDontSee('impasse des Tilleuls');
});

it('returns 404 for an unknown token', function () {
    $this->get('/acompte/not-a-real-token')->assertNotFound();
    $this->post('/acompte/not-a-real-token', ['cgv' => '1'])->assertNotFound();
});

it('covers AE6: an expired link shows "lien expiré" and never opens a Stripe session', function () {
    $this->link->update(['expires_at' => now()->subMinute()]);

    $this->get('/acompte/'.LINK_TOKEN)
        ->assertOk()
        ->assertSee('Ce lien a expiré')
        ->assertSee('ABC234')
        ->assertSee('répondez à l’e-mail')
        ->assertDontSee('name="cgv"', false);
    payLink()->assertOk()->assertSee('Ce lien a expiré');

    expect($this->stripe->created)->toBe([]);
});

it('shows "lien expiré" once the event date has passed, and never opens a Stripe session', function () {
    $this->request->update(['event_date' => '2026-09-30']);

    $this->get('/acompte/'.LINK_TOKEN)
        ->assertOk()
        ->assertSee('Ce lien a expiré')
        ->assertDontSee('name="cgv"', false);
    payLink()->assertOk()->assertSee('Ce lien a expiré');

    expect($this->stripe->created)->toBe([]);
});

it('keeps the link payable on the event date itself', function () {
    $this->request->update(['event_date' => '2026-10-01']);

    payLink()->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_fake1');

    expect($this->stripe->created)->toHaveCount(1);
});

it('shows "lien remplacé" for a link deactivated by a new proposal', function () {
    $this->link->update(['status' => DepositLink::STATUS_REPLACED]);

    $this->get('/acompte/'.LINK_TOKEN)->assertOk()->assertSee('Ce lien a été remplacé')->assertSee('ABC234');
    payLink()->assertOk()->assertSee('Ce lien a été remplacé');

    expect($this->stripe->created)->toBe([]);
});

it('shows "acompte déjà réglé" for a paid link, without a new session', function () {
    $this->link->update(['status' => DepositLink::STATUS_PAID]);
    $this->request->update(['status' => RequestStatus::Reservee]);

    $this->get('/acompte/'.LINK_TOKEN)->assertOk()->assertSee('Acompte déjà réglé')->assertSee('ABC234');
    payLink()->assertOk()->assertSee('Acompte déjà réglé');

    expect($this->stripe->created)->toBe([]);
});

it('covers AE5: once the only place of the day is paid, the second link shows "date complète", cannot pay, and the atelier sees it blocked', function () {
    CapacityOverride::create(['date' => '2026-11-14', 'capacity' => 1]);
    CakeRequest::factory()->create(['event_date' => '2026-11-14', 'status' => RequestStatus::Reservee]);

    $this->get('/acompte/'.LINK_TOKEN)
        ->assertOk()
        ->assertSee('Date complète')
        ->assertSee('ABC234')
        ->assertDontSee('name="cgv"', false);
    payLink()->assertOk()->assertSee('Date complète');

    expect($this->stripe->created)->toBe([])
        ->and($this->link->fresh()->stripe_session_id)->toBeNull();

    $this->actingAs(User::factory()->create(['has_email_authentication' => true]));
    Livewire::test(ViewCakeRequest::class, ['record' => $this->request->getRouteKey()])
        ->assertSee('Bloqué : date complète');
});

it('requires the CGV to be accepted before opening a payment', function () {
    $this->from('/acompte/'.LINK_TOKEN)
        ->post('/acompte/'.LINK_TOKEN, [])
        ->assertRedirect('/acompte/'.LINK_TOKEN)
        ->assertSessionHasErrors('cgv');

    expect($this->stripe->created)->toBe([]);
});

it('opens a 30-minute card-only Checkout session for the exact deposit in euros, without address collection for a pickup', function () {
    payLink()->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_fake1');

    expect($this->stripe->created)->toHaveCount(1);
    $params = $this->stripe->created[0];

    expect($params['mode'])->toBe('payment')
        ->and($params['payment_method_types'])->toBe(['card'])
        ->and($params['line_items'])->toHaveCount(1)
        ->and($params['line_items'][0]['quantity'])->toBe(1)
        ->and($params['line_items'][0]['price_data']['currency'])->toBe('eur')
        ->and($params['line_items'][0]['price_data']['unit_amount'])->toBe(2550)
        ->and($params['line_items'][0]['price_data']['product_data']['name'])->toContain('ABC234')
        ->and($params['payment_intent_data']['description'])->toContain('ABC234')
        ->and($params['metadata'])->toBe(['deposit_link_id' => (string) $this->link->id, 'cake_request_id' => (string) $this->request->id])
        ->and($params['customer_email'])->toBe('client@example.test')
        ->and($params['success_url'])->toBe(route('deposit.return').'?session_id={CHECKOUT_SESSION_ID}')
        ->and($params['cancel_url'])->toBe(url('/acompte/'.LINK_TOKEN))
        ->and($params)->not->toHaveKey('shipping_address_collection');

    // Stripe's minimum is 30 minutes after its own creation time: one minute of margin covers the clock gap.
    $expiresIn = $params['expires_at'] - now()->getTimestamp();
    expect($expiresIn)->toBeGreaterThanOrEqual(30 * 60)->toBeLessThanOrEqual(31 * 60);

    $link = $this->link->fresh();
    expect($link->stripe_session_id)->toBe('cs_test_fake1')
        ->and($link->stripe_session_expires_at->getTimestamp())->toBe($params['expires_at'])
        ->and($link->status)->toBe(DepositLink::STATUS_ACTIVE)
        ->and($this->request->fresh()->status)->toBe(RequestStatus::Proposee);
});

it('collects a French shipping address only for a delivery', function () {
    $this->request->update(['fulfilment' => Fulfilment::Delivery]);

    payLink()->assertRedirect();

    expect($this->stripe->created[0]['shipping_address_collection'])->toBe(['allowed_countries' => ['FR']]);
});

it('reuses the open Stripe session on a second click, and opens a new one once it has expired', function () {
    payLink()->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_fake1');
    payLink()->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_fake1');

    expect($this->stripe->created)->toHaveCount(1);

    Carbon::setTestNow(now()->addMinutes(40));
    payLink()->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_fake2');

    expect($this->stripe->created)->toHaveCount(2)
        ->and($this->link->fresh()->stripe_session_id)->toBe('cs_test_fake2');
});

it('only displays the state on the return page and never marks anything paid by itself', function () {
    payLink();

    $this->get('/acompte/retour?session_id=cs_test_fake1')
        ->assertOk()
        ->assertSee('Paiement en cours de confirmation')
        ->assertSee('ABC234');

    expect($this->request->fresh()->status)->toBe(RequestStatus::Proposee)
        ->and($this->link->fresh()->status)->toBe(DepositLink::STATUS_ACTIVE)
        ->and(Payment::count())->toBe(0);

    Payment::create([
        'deposit_link_id' => $this->link->id,
        'cake_request_id' => $this->request->id,
        'stripe_session_id' => 'cs_test_fake1',
        'payment_intent_id' => 'pi_1',
        'amount_cents' => 2550,
        'status' => Payment::STATUS_PAID,
    ]);
    $this->get('/acompte/retour?session_id=cs_test_fake1')
        ->assertOk()
        ->assertSee('Date réservée, confirmation envoyée par e-mail');

    $this->request->update(['status' => RequestStatus::Remboursee]);
    Payment::query()->update(['status' => Payment::STATUS_REFUNDED]);
    $this->get('/acompte/retour?session_id=cs_test_fake1')
        ->assertOk()
        ->assertSee('Date complète, paiement remboursé');

    $this->get('/acompte/retour?session_id=cs_unknown')->assertNotFound();
});

it('expires the open Stripe session through the gateway when the artisan proposes again or declines', function () {
    $answer = app(AnswerCakeRequest::class);
    $offer = [
        'total_cents' => 9000,
        'deposit_cents' => 2700,
        'allergens' => ['gluten'],
        'slot' => 'Samedi, 11 h',
        'message' => null,
        'validity_days' => 3,
    ];
    $this->link->update(['stripe_session_id' => 'cs_test_open', 'stripe_session_expires_at' => now()->addMinutes(20)]);

    $newLink = $answer->propose($this->request, $offer);
    expect($this->stripe->expired)->toBe(['cs_test_open']);

    $newLink->update(['stripe_session_id' => 'cs_test_open2', 'stripe_session_expires_at' => now()->addMinutes(20)]);
    $answer->decline($this->request->fresh(), 'Désolé.');
    expect($this->stripe->expired)->toBe(['cs_test_open', 'cs_test_open2']);
});

it('still proposes when Stripe cannot expire the old session, and logs it', function () {
    Log::spy();
    $this->stripe->failExpire = true;
    $this->link->update(['stripe_session_id' => 'cs_test_open', 'stripe_session_expires_at' => now()->addMinutes(20)]);

    $newLink = app(AnswerCakeRequest::class)->propose($this->request, [
        'total_cents' => 9000,
        'deposit_cents' => 2700,
        'allergens' => ['gluten'],
        'slot' => 'Samedi, 11 h',
        'message' => null,
        'validity_days' => 3,
    ]);

    expect($newLink->status)->toBe(DepositLink::STATUS_ACTIVE)
        ->and($this->link->fresh()->status)->toBe(DepositLink::STATUS_REPLACED);
    Log::shouldHaveReceived('warning')->once();
});

it('covers AE1: a proposed request that is never paid is not reserved and no e-mail ever carries the pickup address', function () {
    $this->link->delete();
    app(AnswerCakeRequest::class)->propose($this->request, [
        'total_cents' => 8500,
        'deposit_cents' => 2550,
        'allergens' => ['gluten'],
        'slot' => 'Samedi, 11 h',
        'message' => null,
        'validity_days' => 3,
    ]);
    $link = DepositLink::sole();
    $link->update(['token_hash' => DepositLink::hashToken(LINK_TOKEN)]);

    $this->get('/acompte/'.LINK_TOKEN)->assertOk();
    payLink()->assertRedirect();
    Carbon::setTestNow(now()->addDays(4));
    $this->get('/acompte/'.LINK_TOKEN)->assertSee('Ce lien a expiré');

    expect($this->request->fresh()->status)->not->toBe(RequestStatus::Reservee)
        ->and(Payment::count())->toBe(0)
        ->and(sentMails())->not->toBeEmpty();
    sentMails()->each(fn (array $mail) => expect($mail['body'])->not->toContain('impasse des Tilleuls'));
});

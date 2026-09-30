<?php

use App\Enums\Allergen;
use App\Enums\Fulfilment;
use App\Enums\RequestStatus;
use App\Enums\Source;
use App\Filament\Resources\CakeRequests\Pages\ListCakeRequests;
use App\Filament\Resources\CakeRequests\Pages\ViewCakeRequest;
use App\Filament\Widgets\SourceStatsWidget;
use App\Mail\DepositOffer;
use App\Mail\RequestDeclined;
use App\Models\CakeRequest;
use App\Models\CapacityOverride;
use App\Models\Commune;
use App\Models\DepositLink;
use App\Models\RequestPhoto;
use App\Models\User;
use App\Settings\ShopSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Mail::fake();
    Carbon::setTestNow(Carbon::parse('2026-10-01 10:00:00', 'Europe/Paris'));

    $settings = app(ShopSettings::class);
    $settings->deposit_rate_percent = 30;
    $settings->default_link_validity_days = 3;
    $settings->default_daily_capacity = 2;
    $settings->save();

    $this->request = CakeRequest::factory()->create([
        'reference' => 'ABC234',
        'email' => 'client@example.test',
        'name' => 'Léa Martin',
        'event_date' => '2026-11-14',
        'fulfilment' => Fulfilment::Delivery,
        'commune_id' => Commune::factory(),
        'delivery_fee_cents' => 1000,
    ]);

    $this->actingAs(User::factory()->create(['has_email_authentication' => true]));
});

afterEach(fn () => Carbon::setTestNow());

/**
 * Proposes 85 € with gluten and milk on the request's view page.
 */
function proposeOffer(CakeRequest $request, array $overrides = []): void
{
    Livewire::test(ViewCakeRequest::class, ['record' => $request->getRouteKey()])
        ->callAction('propose', array_merge([
            'total' => '85',
            'deposit' => '25,50',
            'allergens' => [Allergen::Gluten->value, Allergen::Milk->value],
            'slot' => 'Samedi 14 novembre, livraison entre 14 h et 16 h',
            'message' => 'Merci pour votre confiance !',
            'validity_days' => 3,
        ], $overrides))
        ->assertHasNoActionErrors();
}

it('covers F3: proposes 85 € at 30 %, creates a 3-day link and queues the offer e-mail', function () {
    Livewire::test(ViewCakeRequest::class, ['record' => $this->request->getRouteKey()])
        ->mountAction('propose')
        ->assertSchemaStateSet(['total' => '10', 'validity_days' => 3])
        ->set('mountedActions.0.data.total', '85')
        ->assertSchemaStateSet(['deposit' => '25,50'])
        ->fillForm([
            'allergens' => [Allergen::Gluten->value, Allergen::Milk->value],
            'slot' => 'Samedi 14 novembre, livraison entre 14 h et 16 h',
            'message' => 'Merci pour votre confiance !',
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors();

    $link = DepositLink::sole();
    expect($link->cake_request_id)->toBe($this->request->id)
        ->and($link->total_cents)->toBe(8500)
        ->and($link->deposit_cents)->toBe(2550)
        ->and($link->status)->toBe(DepositLink::STATUS_ACTIVE)
        ->and($link->expires_at->equalTo(Carbon::parse('2026-10-04 10:00:00', 'Europe/Paris')))->toBeTrue()
        ->and($link->allergens->all())->toBe([Allergen::Gluten, Allergen::Milk])
        ->and($link->slot)->toBe('Samedi 14 novembre, livraison entre 14 h et 16 h');

    $this->request->refresh();
    expect($this->request->status)->toBe(RequestStatus::Proposee)
        ->and($this->request->last_activity_at->equalTo(now()))->toBeTrue();

    Mail::assertQueued(DepositOffer::class, function (DepositOffer $mail) use ($link) {
        $html = $mail->render();
        preg_match('#/acompte/([A-Za-z0-9]+)#', $html, $m);

        return $mail->hasTo('client@example.test')
            && str_contains($mail->envelope()->subject, 'ABC234')
            && isset($m[1]) && hash('sha256', $m[1]) === $link->token_hash
            && str_contains($html, url('/acompte/'.$m[1]))
            && str_contains($html, '85 €')
            && str_contains($html, '25,50 €')
            && str_contains($html, '59,50 €')
            && str_contains($html, 'à régler à la remise du gâteau')
            && str_contains($html, 'dimanche 4 octobre 2026 à 10h00')
            && str_contains($html, 'gluten')
            && str_contains($html, 'lait')
            && str_contains($html, 'Samedi 14 novembre, livraison entre 14 h et 16 h')
            && str_contains($html, 'Merci pour votre confiance !');
    });
});

it('replaces the previous link when the request is proposed again', function () {
    proposeOffer($this->request);
    $first = DepositLink::sole();

    proposeOffer($this->request->refresh(), ['total' => '90', 'deposit' => '27']);

    expect($first->refresh()->status)->toBe(DepositLink::STATUS_REPLACED);
    $active = DepositLink::where('status', DepositLink::STATUS_ACTIVE)->sole();
    expect($active->id)->not->toBe($first->id)
        ->and($active->total_cents)->toBe(9000)
        ->and($active->deposit_cents)->toBe(2700);
    Mail::assertQueuedCount(2);
});

it('refuses a request with an editable message, queues it and deactivates the open link', function () {
    proposeOffer($this->request);

    Livewire::test(ViewCakeRequest::class, ['record' => $this->request->getRouteKey()])
        ->mountAction('decline')
        ->assertSchemaStateSet(function (array $state) {
            expect($state['message'])->toContain('Léa Martin')->toContain('samedi 14 novembre 2026');

            return [];
        })
        ->fillForm(['message' => 'Désolée, je suis complète ce jour-là.'])
        ->callMountedAction()
        ->assertHasNoActionErrors();

    expect($this->request->refresh()->status)->toBe(RequestStatus::Refusee)
        ->and(DepositLink::sole()->status)->toBe(DepositLink::STATUS_REPLACED);
    Mail::assertQueued(RequestDeclined::class, fn (RequestDeclined $mail) => $mail->hasTo('client@example.test')
        && str_contains($mail->envelope()->subject, 'ABC234')
        && str_contains($mail->render(), 'Désolée, je suis complète ce jour-là.'));
});

it('asks for an explicit "none of the 14 allergens" confirmation when no allergen is ticked', function () {
    Livewire::test(ViewCakeRequest::class, ['record' => $this->request->getRouteKey()])
        ->callAction('propose', [
            'total' => '85', 'deposit' => '25,50', 'allergens' => [],
            'slot' => 'Samedi 14 novembre', 'validity_days' => 3,
        ])
        ->assertHasActionErrors(['no_allergen_confirmed']);
    expect(DepositLink::count())->toBe(0);
    Mail::assertNothingQueued();

    proposeOffer($this->request, ['allergens' => [], 'no_allergen_confirmed' => true]);

    expect(DepositLink::sole()->allergens->all())->toBe([]);
    Mail::assertQueued(DepositOffer::class, fn (DepositOffer $mail) => str_contains($mail->render(), 'aucun des 14 allergènes'));
});

it('refuses a deposit larger than the total price', function () {
    Livewire::test(ViewCakeRequest::class, ['record' => $this->request->getRouteKey()])
        ->callAction('propose', [
            'total' => '85', 'deposit' => '90', 'allergens' => [Allergen::Eggs->value],
            'slot' => 'Samedi 14 novembre', 'validity_days' => 3,
        ])
        ->assertHasActionErrors(['deposit']);
    expect(DepositLink::count())->toBe(0);
});

it('shows the request and the day load: paid deposits, capacity and open links', function () {
    CapacityOverride::create(['date' => '2026-11-14', 'capacity' => 3]);
    CakeRequest::factory()->create(['event_date' => '2026-11-14', 'status' => RequestStatus::Reservee]);
    proposeOffer($this->request);
    // An expired link on the same date is not counted as open.
    DepositLink::factory()->create([
        'cake_request_id' => CakeRequest::factory()->create(['event_date' => '2026-11-14'])->id,
        'expires_at' => now()->subMinute(),
    ]);

    $photo = $this->request->photos()->create([
        'kind' => RequestPhoto::KIND_TO_REPRODUCE, 'path' => 'requests/ABC234/a.jpg', 'original_name' => 'a.jpg',
    ]);

    Livewire::test(ViewCakeRequest::class, ['record' => $this->request->getRouteKey()])
        ->assertSee(route('atelier.request-photo', $photo))
        ->assertSee('À reproduire')
        ->assertSee('ABC234')
        ->assertSee('Léa Martin')
        ->assertSee($this->request->phone)
        ->assertSee($this->request->customization)
        ->assertSee('Acomptes payés 1 / capacité 3 · liens ouverts 1');
});

it('lists requests by event date with their type, status and "hors délai" badge', function () {
    $late = CakeRequest::factory()->free()->create(['reference' => 'LATE22', 'event_date' => '2026-10-05', 'out_of_lead_time' => true]);
    $refused = CakeRequest::factory()->create(['reference' => 'REFU22', 'event_date' => '2026-10-21', 'status' => RequestStatus::Refusee]);

    Livewire::test(ListCakeRequests::class)
        ->assertCanSeeTableRecords([$late, $refused, $this->request], inOrder: true)
        ->assertSee('Demande libre')
        ->assertSee('Hors délai')
        ->filterTable('status', RequestStatus::Refusee->value)
        ->assertCanSeeTableRecords([$refused])
        ->assertCanNotSeeTableRecords([$late, $this->request]);
});

it('never serves a private request photo to a guest', function () {
    Storage::fake('local');
    Storage::disk('local')->put('requests/ABC234/photo.jpg', 'jpeg-bytes');
    $photo = $this->request->photos()->create([
        'kind' => RequestPhoto::KIND_INSPIRATION,
        'path' => 'requests/ABC234/photo.jpg',
        'original_name' => 'photo.jpg',
    ]);
    $url = route('atelier.request-photo', $photo);

    $this->get($url)->assertOk();
    expect($this->get($url)->streamedContent())->toBe('jpeg-bytes');

    auth()->logout();
    $this->get($url)->assertForbidden();
    $this->get('/storage/requests/ABC234/photo.jpg')->assertForbidden();
});

it('counts requests and paid deposits per source over the last 30 days', function () {
    CakeRequest::factory()->count(2)->create(['source' => Source::Instagram]);
    CakeRequest::factory()->create(['source' => Source::Instagram, 'status' => RequestStatus::Reservee]);
    CakeRequest::factory()->create(['source' => Source::Instagram, 'created_at' => now()->subDays(31)]);

    Livewire::test(SourceStatsWidget::class)
        ->assertSeeInOrder(['Instagram', '3 demandes', '1 acompte payé'])
        ->assertSeeInOrder(['Recherche Google / fiche Google', '1 demande', '0 acompte payé']);
});

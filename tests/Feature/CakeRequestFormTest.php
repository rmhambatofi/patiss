<?php

use App\Models\CakeRequest;
use App\Models\CakeType;
use App\Models\CakeTypePhoto;
use App\Models\Commune;
use App\Models\DeliveryTier;
use App\Settings\ShopSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Mail::fake();

    $settings = app(ShopSettings::class);
    $settings->legal_address = '12 rue du Commerce, 77230 Dammartin-en-Goële';
    $settings->siret = '12345678900011';
    $settings->deposit_rate_percent = 30;
    $settings->cancellation_terms = 'Acompte remboursé si annulation plus de 7 jours avant.';
    $settings->default_link_validity_days = 3;
    $settings->free_request_lead_days = 14;
    $settings->save();

    $this->type = CakeType::factory()->create([
        'name' => 'Layer cake',
        'slug' => 'layer-cake',
        'portion_formats' => [6, 8, 12],
        'min_lead_days' => 10,
    ]);
    CakeTypePhoto::factory()->for($this->type)->create();
});

function typeRequestPayload(array $overrides = []): array
{
    return array_merge([
        'portions' => 8,
        'customization' => 'Thème licorne, prénom Léa',
        'event_date' => now()->addDays(20)->toDateString(),
        'fulfilment' => 'pickup',
        'allergies' => '',
        'name' => 'Camille Martin',
        'email' => 'camille@example.com',
        'phone' => '06 12 34 56 78',
        'source' => 'google',
    ], $overrides);
}

function freeRequestPayload(array $overrides = []): array
{
    return array_merge([
        'description' => 'Un gâteau en forme de château fort',
        'portions' => 20,
        'event_date' => now()->addDays(30)->toDateString(),
        'fulfilment' => 'pickup',
        'name' => 'Camille Martin',
        'email' => 'camille@example.com',
        'phone' => '06 12 34 56 78',
        'source' => 'instagram',
    ], $overrides);
}

it('shows the type form with the deposit, cancellation terms and legal links before the submit button', function () {
    $this->get('/demande/layer-cake')
        ->assertOk()
        ->assertSee('Layer cake')
        ->assertSee('Acompte de 30 %', false)
        ->assertSee('Acompte remboursé si annulation plus de 7 jours avant.')
        ->assertSee(url('/cgv'))
        ->assertSee(url('/confidentialite'))
        ->assertSee('Comment avez-vous connu');
});

it('returns 404 for a type that is not visible', function () {
    CakeType::factory()->unpublished()->create(['slug' => 'cache']);

    $this->get('/demande/cache')->assertNotFound();
    $this->post('/demande/cache', typeRequestPayload())->assertNotFound();
});

// Covers F1.
it('accepts a valid type request with two photos and shows its reference on the thanks page', function () {
    $response = $this->post('/demande/layer-cake', typeRequestPayload([
        'photo_a_reproduire' => UploadedFile::fake()->image('licorne.jpg', 800, 600),
        'inspirations' => [UploadedFile::fake()->image('idee.png', 400, 400)],
    ]));

    $request = CakeRequest::sole();
    $response->assertRedirect('/demande/merci');
    expect($response->headers->get('Location'))->not->toContain($request->reference);

    $this->followRedirects($response)->assertOk()->assertSee($request->reference);
    expect($request->cake_type_id)->toBe($this->type->id)
        ->and($request->photos)->toHaveCount(2);
});

it('refuses a request without an answer to how the client heard of the artisan', function () {
    $this->post('/demande/layer-cake', typeRequestPayload(['source' => '']))
        ->assertSessionHasErrors('source');

    expect(CakeRequest::count())->toBe(0);
});

// Covers AE4.
it('accepts a date closer than the lead time, flags it and warned about it on the form', function () {
    $this->get('/demande/layer-cake')
        ->assertSee('au moins 10 jours')
        ->assertSee("l'artisan vous confirmera s'il peut la prendre", false);

    $this->post('/demande/layer-cake', typeRequestPayload(['event_date' => now()->addDays(3)->toDateString()]))
        ->assertRedirect('/demande/merci');

    expect(CakeRequest::sole()->out_of_lead_time)->toBeTrue();
});

it('does not flag a date beyond the lead time', function () {
    $this->post('/demande/layer-cake', typeRequestPayload(['event_date' => now()->addDays(10)->toDateString()]))
        ->assertRedirect('/demande/merci');

    expect(CakeRequest::sole()->out_of_lead_time)->toBeFalse();
});

it('refuses a past date and a date more than 12 months ahead', function (string $date) {
    $this->post('/demande/layer-cake', typeRequestPayload(['event_date' => $date]))
        ->assertSessionHasErrors('event_date');

    expect(CakeRequest::count())->toBe(0);
})->with([
    'hier' => fn () => now()->subDay()->toDateString(),
    'dans 13 mois' => fn () => now()->addMonths(13)->toDateString(),
]);

it('refuses a portion count that is not one of the type formats', function () {
    $this->post('/demande/layer-cake', typeRequestPayload(['portions' => 7]))
        ->assertSessionHasErrors('portions');
});

it('requires the allergy consent when allergies are filled', function () {
    $this->post('/demande/layer-cake', typeRequestPayload(['allergies' => 'Arachide pour un invité']))
        ->assertSessionHasErrors('allergy_consent');
    expect(CakeRequest::count())->toBe(0);

    $this->post('/demande/layer-cake', typeRequestPayload([
        'allergies' => 'Arachide pour un invité',
        'allergy_consent' => '1',
    ]))->assertRedirect('/demande/merci');

    expect(CakeRequest::sole()->allergy_consent_at)->not->toBeNull();
});

it('refuses delivery to a commune beyond the last tier even when the browser is bypassed', function () {
    DeliveryTier::factory()->create(['max_km' => 25, 'price_cents' => 1000]);
    Commune::factory()->create(['insee_code' => '60057', 'distance_km' => 80]);

    $this->post('/demande/layer-cake', typeRequestPayload([
        'fulfilment' => 'delivery',
        'commune_insee' => '60057',
    ]))->assertSessionHasErrors(['commune_insee' => 'Livraison impossible pour cette commune : retrait uniquement']);

    $this->post('/demande/layer-cake', typeRequestPayload([
        'fulfilment' => 'delivery',
        'commune_insee' => '99999',
    ]))->assertSessionHasErrors('commune_insee');

    expect(CakeRequest::count())->toBe(0);
});

it('accepts delivery to a served commune and snapshots its zone fee', function () {
    DeliveryTier::factory()->create(['max_km' => 25, 'price_cents' => 1000]);
    $commune = Commune::factory()->create(['insee_code' => '60057', 'distance_km' => 12]);

    $this->post('/demande/layer-cake', typeRequestPayload([
        'fulfilment' => 'delivery',
        'commune_insee' => '60057',
    ]))->assertRedirect('/demande/merci');

    expect(CakeRequest::sole())
        ->commune_id->toBe($commune->id)
        ->delivery_fee_cents->toBe(1000);
});

it('refuses a sixth file, a 12 MB file and a PDF renamed as .jpg', function (Closure $files, string $errorKey) {
    $this->post('/demande/layer-cake', typeRequestPayload($files()))
        ->assertSessionHasErrors($errorKey);

    expect(CakeRequest::count())->toBe(0);
})->with([
    'sixième fichier' => [fn () => [
        'photo_a_reproduire' => UploadedFile::fake()->image('a.jpg'),
        'inspirations' => array_map(fn ($i) => UploadedFile::fake()->image("i$i.jpg"), range(1, 5)),
    ], 'inspirations'],
    '12 Mo' => [fn () => [
        'photo_a_reproduire' => UploadedFile::fake()->image('lourde.jpg')->size(12 * 1024),
    ], 'photo_a_reproduire'],
    'PDF renommé' => [fn () => [
        // A real UploadedFile (not a testing fake, whose MIME type comes from its name) so the content is inspected.
        'inspirations' => [new UploadedFile(
            tap(tempnam(sys_get_temp_dir(), 'pdf'), fn ($path) => file_put_contents($path, "%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\ntrailer << /Root 1 0 R >>\n%%EOF\n")),
            'scan.jpg',
            'image/jpeg',
            null,
            true,
        )],
    ], 'inspirations.0'],
]);

it('accepts five photos in total', function () {
    $this->post('/demande/layer-cake', typeRequestPayload([
        'photo_a_reproduire' => UploadedFile::fake()->image('a.jpg'),
        'inspirations' => array_map(fn ($i) => UploadedFile::fake()->image("i$i.jpg"), range(1, 4)),
    ]))->assertRedirect('/demande/merci');

    expect(CakeRequest::sole()->photos)->toHaveCount(5);
});

it('shows the free request form with the global lead time', function () {
    $this->get('/demande-libre')
        ->assertOk()
        ->assertSee('au moins 14 jours')
        ->assertSee('Acompte de 30 %', false);
});

it('accepts a free request without any photo', function () {
    $this->post('/demande-libre', freeRequestPayload())
        ->assertRedirect('/demande/merci');

    expect(CakeRequest::sole())
        ->kind->toBe('libre')
        ->cake_type_id->toBeNull()
        ->portions->toBe(20)
        ->customization->toBe('Un gâteau en forme de château fort')
        ->out_of_lead_time->toBeFalse();
});

it('flags a free request closer than the global lead time', function () {
    $this->post('/demande-libre', freeRequestPayload(['event_date' => now()->addDays(5)->toDateString()]))
        ->assertRedirect('/demande/merci');

    expect(CakeRequest::sole()->out_of_lead_time)->toBeTrue();
});

it('silently ignores a submission whose honeypot is filled', function () {
    $this->post('/demande/layer-cake', typeRequestPayload(['site_web' => 'http://spam.example']))
        ->assertRedirect('/demande/merci');

    expect(CakeRequest::count())->toBe(0);
    Mail::assertNothingQueued();
    Mail::assertNothingSent();
});

it('answers 429 beyond the rate limit for the same IP', function () {
    foreach (range(1, 5) as $i) {
        $this->post('/demande-libre', freeRequestPayload())->assertRedirect('/demande/merci');
    }

    $this->post('/demande-libre', freeRequestPayload())->assertStatus(429);
    $this->post('/demande/layer-cake', typeRequestPayload())->assertStatus(429);

    expect(CakeRequest::count())->toBe(5);
});

it('keeps the forms closed while the required settings are missing', function () {
    $settings = app(ShopSettings::class);
    $settings->siret = null;
    $settings->save();

    $this->get('/demande/layer-cake')->assertStatus(503);
    $this->post('/demande-libre', freeRequestPayload())->assertStatus(503);
    expect(CakeRequest::count())->toBe(0);
});

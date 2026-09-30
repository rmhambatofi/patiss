<?php

use App\Enums\RequestStatus;
use App\Mail\NewRequestForArtisan;
use App\Mail\RequestReceived;
use App\Models\CakeRequest;
use App\Models\CakeType;
use App\Models\CakeTypePhoto;
use App\Models\Commune;
use App\Models\DeliveryTier;
use App\Services\ImageSanitizer;
use App\Settings\ShopSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');

    $settings = app(ShopSettings::class);
    $settings->legal_address = '12 rue du Commerce, 77230 Dammartin-en-Goële';
    $settings->siret = '12345678900011';
    $settings->deposit_rate_percent = 30;
    $settings->cancellation_terms = 'Acompte remboursé si annulation plus de 7 jours avant.';
    $settings->default_link_validity_days = 3;
    $settings->notification_email = 'atelier@patisserie.test';
    $settings->save();

    $this->type = CakeType::factory()->create(['name' => 'Layer cake', 'slug' => 'layer-cake', 'min_lead_days' => 7]);
    CakeTypePhoto::factory()->for($this->type)->create();
    DeliveryTier::factory()->create(['max_km' => 25, 'price_cents' => 1250]);
    Commune::factory()->create(['insee_code' => '60057', 'name' => 'Chantilly', 'distance_km' => 20]);

    $this->payload = [
        'portions' => 12,
        'customization' => 'Thème licorne, prénom Léa',
        'event_date' => now()->addDays(20)->toDateString(),
        'fulfilment' => 'delivery',
        'commune_insee' => '60057',
        'allergies' => 'Allergie sévère aux arachides',
        'allergy_consent' => '1',
        'name' => 'Camille Martin',
        'email' => 'camille@example.com',
        'phone' => '06 12 34 56 78',
        'source' => 'bouche_a_oreille',
    ];
});

it('stores the request with a short unambiguous reference and the new status', function () {
    $this->post('/demande/layer-cake', $this->payload)->assertRedirect('/demande/merci');

    $request = CakeRequest::sole();
    expect($request->reference)->toMatch('/^[A-HJ-NP-Z2-9]{6}$/')
        ->and($request->status)->toBe(RequestStatus::Nouvelle)
        ->and($request->last_activity_at)->not->toBeNull()
        ->and($request->phone)->toBe('06 12 34 56 78');
});

it('queues two mails: the acknowledgement to the client and the notification to the artisan', function () {
    Mail::fake();

    $this->post('/demande/layer-cake', $this->payload);

    Mail::assertQueued(RequestReceived::class, fn ($mail) => $mail->hasTo('camille@example.com'));
    Mail::assertQueued(NewRequestForArtisan::class, fn ($mail) => $mail->hasTo('atelier@patisserie.test'));
    Mail::assertQueuedCount(2);
    Mail::assertNothingSent();
});

it('skips the artisan notification when no notification address is set', function () {
    Mail::fake();
    $settings = app(ShopSettings::class);
    $settings->notification_email = null;
    $settings->save();

    $this->post('/demande/layer-cake', $this->payload);

    Mail::assertQueued(RequestReceived::class);
    Mail::assertNotQueued(NewRequestForArtisan::class);
});

it('writes a client acknowledgement recapping the request and the price confirmation', function () {
    $this->post('/demande/layer-cake', $this->payload);
    $request = CakeRequest::sole();

    $mail = new RequestReceived($request);
    $mail->assertHasSubject("Votre demande {$request->reference} a bien été reçue");
    $mail->assertSeeInHtml($request->reference)
        ->assertSeeInHtml('Layer cake')
        ->assertSeeInHtml('12 parts')
        ->assertSeeInHtml(now()->addDays(20)->locale('fr')->translatedFormat('j F Y'))
        ->assertSeeInHtml('Livraison')
        ->assertSeeInHtml('Chantilly')
        ->assertSeeInHtml('12,50 €')
        ->assertSeeInHtml('Thème licorne, prénom Léa')
        ->assertSeeInHtml('confirmera le prix exact sous 48 heures', false);
});

it('keeps allergies, phone, customization and attachments out of the artisan notification', function () {
    $this->post('/demande/layer-cake', $this->payload + [
        'photo_a_reproduire' => UploadedFile::fake()->image('licorne.jpg'),
    ]);
    $request = CakeRequest::sole();

    $mail = new NewRequestForArtisan($request);
    $mail->assertHasSubject("Nouvelle demande {$request->reference}");
    $mail->assertSeeInHtml('Layer cake')
        ->assertSeeInHtml('Chantilly')
        ->assertSeeInHtml(url('/atelier'))
        ->assertDontSeeInHtml('arachides')
        ->assertDontSeeInHtml('06 12 34 56 78')
        ->assertDontSeeInHtml('licorne')
        ->assertDontSeeInText('arachides')
        ->assertDontSeeInText('06 12 34 56 78');

    $mailer = Mail::mailer('array');
    $mailer->to('atelier@patisserie.test')->sendNow($mail);
    $sent = $mailer->getSymfonyTransport()->messages()->last()->getOriginalMessage();
    expect($sent->getAttachments())->toBe([])
        ->and($sent->getHtmlBody().$sent->getTextBody())->not->toContain('arachides')->not->toContain('06 12 34 56 78');
});

it('does not store the allergies, phone or customization in clear text', function () {
    $this->post('/demande/layer-cake', $this->payload);

    $row = (array) DB::table('cake_requests')->first();
    expect($row['allergies'])->not->toContain('arachides')
        ->and($row['phone'])->not->toContain('06 12')
        ->and($row['customization'])->not->toContain('licorne')
        ->and(CakeRequest::sole()->allergies)->toBe('Allergie sévère aux arachides');
});

it('writes photos on the private disk, re-encoded, and does not serve them without a signed URL', function () {
    $this->post('/demande/layer-cake', $this->payload + [
        'photo_a_reproduire' => UploadedFile::fake()->image('licorne.png', 300, 300),
        'inspirations' => [UploadedFile::fake()->image('idee.jpg')],
    ]);
    $request = CakeRequest::sole();

    expect($request->photos->pluck('kind')->all())->toBe(['a_reproduire', 'inspiration']);
    foreach ($request->photos as $photo) {
        expect($photo->path)->toStartWith("requests/{$request->reference}/")->toEndWith('.jpg');
        Storage::disk('local')->assertExists($photo->path);
        expect(Storage::disk('local')->path($photo->path))->not->toStartWith(public_path());
        $this->get('/storage/'.$photo->path)->assertStatus(403);
    }
    expect($request->photos->first()->original_name)->toBe('licorne.png');
});

it('keeps an undecodable HEIC as-is on the private disk', function () {
    $heic = UploadedFile::fake()->createWithContent('gateau.heic', "\x00\x00\x00\x18ftypheic\x00\x00\x00\x00mif1heic".str_repeat("\x00", 64));

    $this->post('/demande/layer-cake', $this->payload + ['photo_a_reproduire' => $heic])
        ->assertRedirect('/demande/merci');

    $photo = CakeRequest::sole()->photos->sole();
    expect($photo->path)->toStartWith('requests/')->toEndWith('.heic');
    Storage::disk('local')->assertExists($photo->path);
})->skip(fn () => ! in_array((new finfo(FILEINFO_MIME_TYPE))->buffer("\x00\x00\x00\x18ftypheic\x00\x00\x00\x00mif1heic".str_repeat("\x00", 64)), ['image/heic', 'image/heif']), 'finfo does not recognise HEIC here');

it('rolls the request back and shows an error when a photo cannot be written', function () {
    Mail::fake();
    $this->mock(ImageSanitizer::class)->shouldReceive('store')->andThrow(new RuntimeException('disque plein'));

    $this->from('/demande/layer-cake')
        ->post('/demande/layer-cake', $this->payload + [
            'photo_a_reproduire' => UploadedFile::fake()->image('licorne.jpg'),
        ])
        ->assertRedirect('/demande/layer-cake')
        ->assertSessionHasErrors('photos');

    expect(CakeRequest::count())->toBe(0)
        ->and(DB::table('request_photos')->count())->toBe(0);
    Mail::assertNothingQueued();
});

it('leaves the request stored and the mail queued for retry when SMTP is down', function () {
    config([
        'queue.default' => 'database',
        'mail.default' => 'smtp',
        'mail.mailers.smtp.host' => '127.0.0.1',
        'mail.mailers.smtp.port' => 1,
        'mail.mailers.smtp.timeout' => 1,
    ]);

    $this->post('/demande/layer-cake', $this->payload)->assertRedirect('/demande/merci');

    expect(CakeRequest::count())->toBe(1)
        ->and(DB::table('jobs')->count())->toBe(2);

    Artisan::call('queue:work', ['connection' => 'database', '--once' => true, '--sleep' => 0]);

    expect(CakeRequest::count())->toBe(1)
        ->and(DB::table('jobs')->count())->toBe(2)
        ->and(DB::table('jobs')->max('attempts'))->toBe(1)
        ->and(DB::table('failed_jobs')->count())->toBe(0);
});

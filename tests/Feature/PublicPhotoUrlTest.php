<?php

use App\Models\CakeType;
use App\Models\CakeTypePhoto;
use Illuminate\Support\Facades\Storage;

it('keeps the public disk URL relative so the atelier upload preview stays same-origin', function () {
    expect(Storage::disk('public')->url('cake-types/a.jpg'))->toBe('/storage/cake-types/a.jpg');
});

it('serves cake photos from the host the page was requested on, whatever APP_URL says', function () {
    config(['app.url' => 'https://tahpatisserie.fr']);
    $type = CakeType::factory()->create(['is_published' => true]);
    $photo = CakeTypePhoto::factory()->for($type)->create(['path' => 'cake-types/photo.jpg']);

    $this->get('http://127.0.0.1:8000/gateaux')
        ->assertOk()
        ->assertSee('http://127.0.0.1:8000/storage/cake-types/photo.jpg', false)
        ->assertDontSee('https://tahpatisserie.fr/storage/', false);
});

it('generates https URLs behind a proxy that forwards the https scheme', function () {
    $type = CakeType::factory()->create(['is_published' => true]);
    CakeTypePhoto::factory()->for($type)->create(['path' => 'cake-types/photo.jpg']);

    $this->withHeader('X-Forwarded-Proto', 'https')
        ->get('http://tahpatisserie.fr/gateaux')
        ->assertOk()
        ->assertSee('https://tahpatisserie.fr/storage/cake-types/photo.jpg', false);
});

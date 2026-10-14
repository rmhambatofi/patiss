<?php

use App\Enums\CreationCategory;
use App\Models\Creation;
use App\Settings\ShopSettings;

function vitrineSettings(array $overrides = []): ShopSettings
{
    $settings = app(ShopSettings::class);
    $settings->business_name = 'Tah’Pâtisserie';
    $settings->artisan_first_name = 'Tahina';
    $settings->whatsapp_number = '+33600000000';
    $settings->instagram_handle = 'tahpatisserie';
    $settings->facebook_url = 'https://facebook.com/tahpatisserie';
    $settings->contact_email = 'bonjour@tahpatisserie.fr';
    $settings->workshop_address = '12 rue des Lilas, 69001 Lyon';
    $settings->workshop_hours = 'Mar – Sam : 9h – 18h';

    foreach ($overrides as $name => $value) {
        $settings->{$name} = $value;
    }

    $settings->save();

    return $settings;
}

it('shows the visible creations with their category, and hides the others', function () {
    Creation::factory()->create(['name' => 'Layer cake fleuri', 'category' => CreationCategory::GateauxDeFete, 'description' => 'Génoise vanille, crème légère, fleurs fraîches.', 'sort_order' => 2]);
    Creation::factory()->create(['name' => 'Tarte aux fruits rouges', 'category' => CreationCategory::Tartes, 'sort_order' => 1]);
    Creation::factory()->unpublished()->create(['name' => 'Brouillon secret']);
    Creation::factory()->withoutImage()->create(['name' => 'Sans photo']);

    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['Tarte aux fruits rouges', 'Layer cake fleuri'])
        ->assertSee('Génoise vanille, crème légère, fleurs fraîches.')
        ->assertSee('Gâteaux de fête')
        ->assertSee('data-filter="tartes"', false)
        ->assertDontSee('Brouillon secret')
        ->assertDontSee('Sans photo');
});

it('opens with the featured creations, then the first of the gallery, four at most', function () {
    foreach (['Un', 'Deux', 'Trois', 'Quatre', 'Cinq'] as $i => $name) {
        Creation::factory()->create(['name' => $name, 'sort_order' => $i]);
    }
    Creation::factory()->featured()->create(['name' => 'Vedette', 'sort_order' => 99]);

    $html = $this->get('/')->assertOk()->getContent();
    $hero = substr($html, 0, strpos($html, 'id="creations"'));

    expect($hero)->toContain('Vedette')->toContain('Un')->toContain('Trois')->not->toContain('Quatre');
});

it('presents the artisan, the contact channels and the workshop from the settings', function () {
    vitrineSettings();

    $this->get('/')
        ->assertOk()
        ->assertSee('Je m’appelle Tahina.')
        ->assertSee('Dans mon atelier à Lyon')
        ->assertSee('https://wa.me/33600000000?text=Bonjour%20Tahina%2C')
        ->assertSee('06 00 00 00 00')
        ->assertSee('https://instagram.com/tahpatisserie')
        ->assertSee('@tahpatisserie')
        ->assertSee('https://facebook.com/tahpatisserie')
        ->assertSee('mailto:bonjour@tahpatisserie.fr')
        ->assertSee('12 rue des Lilas, 69001 Lyon')
        ->assertSee('Mar – Sam : 9h – 18h')
        ->assertSee('Atelier, sur rendez-vous.')
        ->assertSee('Lyon</span>', false)
        ->assertSee('et alentours');
});

it('falls back gracefully without WhatsApp nor workshop address', function () {
    vitrineSettings(['whatsapp_number' => null, 'workshop_address' => null, 'workshop_hours' => null]);

    $this->get('/')
        ->assertOk()
        ->assertDontSee('wa.me')
        ->assertDontSee('Atelier, sur rendez-vous.')
        ->assertSee('Écrire un message')
        ->assertSee('Dans mon atelier,');
});

it('shows no cart, price nor order link on the vitrine', function () {
    Creation::factory()->create();

    $this->get('/')
        ->assertOk()
        ->assertDontSee(url('/demande-libre'))
        ->assertDontSee(url('/gateaux'))
        ->assertDontSee('Commander')
        ->assertDontSee('à partir de')
        ->assertDontSee('acompte');
});

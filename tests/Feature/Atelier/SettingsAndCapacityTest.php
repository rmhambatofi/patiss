<?php

use App\Filament\Pages\Settings;
use App\Filament\Resources\CapacityOverrides\Pages\CreateCapacityOverride;
use App\Filament\Resources\CapacityOverrides\Pages\ListCapacityOverrides;
use App\Models\CapacityOverride;
use App\Models\User;
use App\Settings\ShopSettings;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['has_email_authentication' => true]));
});

it('lists the missing required settings while the public forms are closed', function () {
    Livewire::test(Settings::class)
        ->assertSee('Les formulaires de demande sont fermés')
        ->assertSee('SIRET')
        ->assertSee('Conditions d’annulation');
});

it('saves the shop settings and opens the forms once the required ones are filled', function () {
    Livewire::test(Settings::class)
        ->fillForm([
            'business_name' => 'Pâtisserie Lova',
            'legal_address' => '1 rue de la Poste, 77230 Dammartin-en-Goële',
            'siret' => '12345678901234',
            'deposit_rate_percent' => 30,
            'cancellation_terms' => 'Acompte non remboursable à moins de 7 jours.',
            'default_link_validity_days' => 3,
            'default_daily_capacity' => 2,
            'free_request_lead_days' => 14,
            'pickup_address' => '5 allée des Tilleuls',
            'google_review_url' => 'https://g.page/r/exemple/review',
            'notification_email' => 'atelier@example.test',
            'artisan_first_name' => 'Lova',
            'whatsapp_number' => '+33612345678',
            'instagram_handle' => 'patisserie.lova',
            'facebook_url' => 'https://facebook.com/patisserielova',
            'contact_email' => 'bonjour@patisserie-lova.fr',
            'workshop_address' => '3 rue des Fours, 77230 Dammartin-en-Goële',
            'workshop_hours' => 'Mer – Sam : 10h – 18h',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = app(ShopSettings::class)->refresh();
    expect($settings->isOpenForRequests())->toBeTrue()
        ->and($settings->siret)->toBe('12345678901234')
        ->and($settings->pickup_address)->toBe('5 allée des Tilleuls')
        ->and($settings->default_daily_capacity)->toBe(2)
        ->and($settings->whatsapp_number)->toBe('+33612345678')
        ->and($settings->whatsappDisplay())->toBe('06 12 34 56 78')
        ->and($settings->workshopCity())->toBe('Dammartin-en-Goële');

    Livewire::test(Settings::class)->assertDontSee('Les formulaires de demande sont fermés');
});

it('records a capacity for a specific date and lists it as "date : N gâteaux"', function () {
    Livewire::test(CreateCapacityOverride::class)
        ->fillForm(['date' => '2026-12-24', 'capacity' => 3])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(CapacityOverride::sole())
        ->date->toDateString()->toBe('2026-12-24')
        ->capacity->toBe(3);

    Livewire::test(ListCapacityOverrides::class)->assertSee('24/12/2026')->assertSee('3 gâteaux');

    Livewire::test(CreateCapacityOverride::class)
        ->fillForm(['date' => '2026-12-24', 'capacity' => 1])
        ->call('create')
        ->assertHasFormErrors(['date']);
});

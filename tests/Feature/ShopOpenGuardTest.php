<?php

use App\Http\Middleware\EnsureShopIsOpen;
use App\Settings\ShopSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', EnsureShopIsOpen::class])->group(function () {
        Route::get('/_guard-probe', fn () => 'formulaire de demande');
        Route::post('/_guard-probe', fn () => 'demande enregistrée');
    });
});

function fillRequiredShopSettings(): void
{
    $settings = app(ShopSettings::class);
    $settings->legal_address = '12 rue du Commerce, 77230 Dammartin-en-Goële';
    $settings->siret = '12345678900011';
    $settings->deposit_rate_percent = 30;
    $settings->cancellation_terms = 'Acompte remboursé si annulation plus de 7 jours avant.';
    $settings->default_link_validity_days = 3;
    $settings->save();
}

it('closes the request form while the professional address is missing', function () {
    $this->get('/_guard-probe')
        ->assertStatus(503)
        ->assertSee('Commandes bientôt ouvertes')
        ->assertDontSee('formulaire de demande');

    $this->post('/_guard-probe')
        ->assertStatus(503)
        ->assertDontSee('demande enregistrée');
});

it('opens the request form once every required setting is filled', function () {
    fillRequiredShopSettings();

    $this->get('/_guard-probe')->assertOk()->assertSee('formulaire de demande');
});

it('stays closed when a single required setting is emptied again', function () {
    fillRequiredShopSettings();
    $settings = app(ShopSettings::class);
    $settings->siret = null;
    $settings->save();

    $this->get('/_guard-probe')->assertStatus(503);
});

it('stores the pickup address encrypted', function () {
    $settings = app(ShopSettings::class);
    $settings->pickup_address = '4 allée des Tilleuls, 77230 Dammartin-en-Goële';
    $settings->save();

    $raw = DB::table('settings')->where('group', 'shop')->where('name', 'pickup_address')->value('payload');

    expect($raw)->not->toContain('Tilleuls')
        ->and(app(ShopSettings::class)->pickup_address)->toBe('4 allée des Tilleuls, 77230 Dammartin-en-Goële');
});

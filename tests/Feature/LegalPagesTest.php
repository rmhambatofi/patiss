<?php

use App\Settings\ShopSettings;

function fillLegalSettings(): void
{
    $settings = app(ShopSettings::class);
    $settings->business_name = 'Pâtisserie Lalao';
    $settings->legal_address = '5 place du Marché, 77230 Dammartin-en-Goële';
    $settings->siret = '98765432100017';
    $settings->deposit_rate_percent = 35;
    $settings->cancellation_terms = 'Acompte conservé en cas d\'annulation moins de 10 jours avant l\'événement.';
    $settings->default_link_validity_days = 3;
    $settings->pickup_address = '42 allée des Tilleuls, 77230 Dammartin-en-Goële';
    $settings->notification_email = 'contact@patisserie-lalao.fr';
    $settings->save();
}

dataset('legal pages', [
    'mentions légales' => '/mentions-legales',
    'CGV' => '/cgv',
    'confidentialité' => '/confidentialite',
]);

it('shows the professional address and SIRET from the settings', function (string $url) {
    fillLegalSettings();

    $this->get($url)
        ->assertOk()
        ->assertSee('5 place du Marché, 77230 Dammartin-en-Goële')
        ->assertSee('98765432100017');
})->with('legal pages');

it('never shows the pickup address', function (string $url) {
    fillLegalSettings();

    $this->get($url)
        ->assertOk()
        ->assertDontSee('42 allée des Tilleuls');
})->with('legal pages');

it('shows a placeholder while the legal settings are empty', function (string $url) {
    $this->get($url)
        ->assertOk()
        ->assertSee('à compléter');
})->with('legal pages');

it('names the host o2switch in the legal notice', function () {
    fillLegalSettings();

    $this->get('/mentions-legales')
        ->assertOk()
        ->assertSee('o2switch')
        ->assertSee('Clermont-Ferrand')
        ->assertSee('contact@patisserie-lalao.fr');
});

it('shows the deposit rate, cancellation terms, balance at handover and the withdrawal exception in the CGV', function () {
    fillLegalSettings();

    $this->get('/cgv')
        ->assertOk()
        ->assertSee('35 %')
        ->assertSee('Acompte conservé en cas d\'annulation moins de 10 jours avant l\'événement.', escape: true)
        ->assertSee('solde')
        ->assertSee('à la remise du gâteau')
        ->assertSee('L221-28')
        ->assertSee('droit de rétractation');
});

it('names Stripe and o2switch as recipients in the privacy policy', function () {
    fillLegalSettings();

    $this->get('/confidentialite')
        ->assertOk()
        ->assertSee('Stripe')
        ->assertSee('o2switch')
        ->assertSee('CNIL')
        ->assertSee('contact@patisserie-lalao.fr');
});

<?php

use App\Enums\Allergen;
use App\Models\CakeType;
use App\Models\CakeTypePhoto;
use App\Settings\ShopSettings;

function visibleCakeType(array $attributes = []): CakeType
{
    $type = CakeType::factory()->create($attributes + ['is_published' => true]);
    CakeTypePhoto::factory()->for($type)->create(['alt' => 'Réalisation '.$type->name]);

    return $type;
}

it('shows a published type with photos in the collection and on its own page', function () {
    $type = visibleCakeType([
        'name' => 'Number cake',
        'slug' => 'number-cake',
        'description' => 'Un chiffre en pâte sablée garni de crème.',
        'from_price_cents' => 4500,
        'portion_formats' => [6, 8, 12],
        'customizable_options' => ['forme', 'inscription', 'couleurs'],
        'min_lead_days' => 7,
        'base_allergens' => [Allergen::Gluten, Allergen::Eggs, Allergen::Milk],
    ]);

    $this->get('/gateaux')
        ->assertOk()
        ->assertSee('Number cake')
        ->assertSee('à partir de 45 €')
        ->assertSee(route('collection.show', $type));

    $this->get('/gateaux/number-cake')
        ->assertOk()
        ->assertSee('Number cake')
        ->assertSee('Un chiffre en pâte sablée garni de crème.')
        ->assertSee('Réalisation Number cake')
        ->assertSee('à partir de 45 €')
        ->assertSee('6, 8 ou 12 parts')
        ->assertSee('inscription')
        ->assertSee('couleurs')
        ->assertSee('7 jours')
        ->assertSee('gluten')
        ->assertSee('œufs')
        ->assertSee('lait')
        ->assertDontSee('arachides')
        ->assertSee('la personnalisation peut ajouter des allergènes')
        ->assertSee(url('/demande/number-cake'));
});

it('formats prices with cents in French', function () {
    $type = CakeType::factory()->make(['from_price_cents' => 123450]);

    expect($type->fromPriceLabel())->toBe('à partir de 1 234,50 €');
});

it('hides an unpublished type and a type without photo', function () {
    $unpublished = CakeType::factory()->create(['name' => 'Fraisier secret', 'is_published' => false]);
    CakeTypePhoto::factory()->for($unpublished)->create();
    $withoutPhoto = CakeType::factory()->create(['name' => 'Layer cake nu', 'is_published' => true]);

    foreach ([$unpublished, $withoutPhoto] as $type) {
        $this->get('/gateaux/'.$type->slug)->assertNotFound();
    }

    $this->get('/gateaux')->assertOk()->assertDontSee('Fraisier secret')->assertDontSee('Layer cake nu');
    $this->get('/')->assertOk()->assertDontSee('Fraisier secret')->assertDontSee('Layer cake nu');
    $this->get('/sitemap.xml')->assertOk()
        ->assertDontSee('/gateaux/'.$unpublished->slug)
        ->assertDontSee('/gateaux/'.$withoutPhoto->slug);
});

it('lists visible types in the sitemap', function () {
    visibleCakeType(['slug' => 'layer-cake']);

    $response = $this->get('/sitemap.xml')->assertOk();

    expect($response->headers->get('Content-Type'))->toContain('application/xml');
    $response->assertSee('<loc>'.url('/').'</loc>', false)
        ->assertSee('<loc>'.url('/gateaux').'</loc>', false)
        ->assertSee('<loc>'.url('/gateaux/layer-cake').'</loc>', false);
});

it('names Dammartin-en-Goële and never the pickup address or a street address', function () {
    // Covers AE3.
    $settings = app(ShopSettings::class);
    $settings->pickup_address = '4 allée des Tilleuls, 77230 Dammartin-en-Goële';
    $settings->save();
    $type = visibleCakeType();

    foreach (['/', '/gateaux', '/gateaux/'.$type->slug] as $uri) {
        $this->get($uri)
            ->assertOk()
            ->assertSee('Dammartin-en-Goële')
            ->assertSee('"@type":"Bakery"', false)
            ->assertSee('"areaServed"', false)
            ->assertSee('Île-de-France')
            ->assertSee('Oise')
            ->assertDontSee('allée des Tilleuls')
            ->assertDontSee('streetAddress', false);
    }
});

it('states that no allergen is in the base recipe when the type has none', function () {
    $type = visibleCakeType(['base_allergens' => []]);

    $this->get('/gateaux/'.$type->slug)
        ->assertOk()
        ->assertSee('Aucun des 14 allergènes réglementaires n’est présent dans la recette de base.')
        ->assertSee('la personnalisation peut ajouter des allergènes');
});

it('shows the deposit rate, cancellation terms and a link to the CGV on a type page', function () {
    $settings = app(ShopSettings::class);
    $settings->deposit_rate_percent = 30;
    $settings->cancellation_terms = 'Acompte remboursé si annulation plus de 7 jours avant.';
    $settings->save();
    $type = visibleCakeType();

    $this->get('/gateaux/'.$type->slug)
        ->assertOk()
        ->assertSee('Acompte de 30 %')
        ->assertSee('Acompte remboursé si annulation plus de 7 jours avant.')
        ->assertSee(url('/cgv'));
});

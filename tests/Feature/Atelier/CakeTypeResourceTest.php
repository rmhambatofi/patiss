<?php

use App\Enums\Allergen;
use App\Filament\Resources\CakeTypes\Pages\CreateCakeType;
use App\Filament\Resources\CakeTypes\Pages\EditCakeType;
use App\Models\CakeType;
use App\Models\CakeTypePhoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['has_email_authentication' => true]));
});

it('creates a type whose uploaded photo loses its GPS data and shows it on the public page', function () {
    $photo = UploadedFile::fake()->createWithContent('gateau.jpg', jpegWithGpsExif());
    expect(exif_read_data($photo->getRealPath()))->toHaveKey('GPSLatitude');

    Livewire::test(CreateCakeType::class)
        ->fillForm([
            'name' => 'Layer cake',
            'description' => 'Trois étages de génoise et de crème.',
            'from_price_cents' => '55,50',
            'portion_formats' => ['8', '12'],
            'customizable_options' => ['couleurs', 'inscription'],
            'min_lead_days' => 10,
            'base_allergens' => [Allergen::Gluten->value, Allergen::Milk->value],
            'sort_order' => 1,
            'is_published' => true,
            'photos' => [
                ['path' => [$photo], 'alt' => 'Layer cake rose et or'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $type = CakeType::sole();
    expect($type->slug)->toBe('layer-cake')
        ->and($type->from_price_cents)->toBe(5550)
        ->and($type->portion_formats)->toBe([8, 12])
        ->and($type->customizable_options)->toBe(['couleurs', 'inscription'])
        ->and($type->base_allergens->all())->toBe([Allergen::Gluten, Allergen::Milk]);

    $stored = CakeTypePhoto::sole();
    expect($stored->alt)->toBe('Layer cake rose et or')
        ->and($stored->path)->toStartWith('cake-types/')->toEndWith('.jpg');
    Storage::disk('public')->assertExists($stored->path);
    $exif = @exif_read_data(Storage::disk('public')->path($stored->path));
    expect($exif)->not->toHaveKey('GPSLatitude')
        ->and($exif['SectionsFound'])->not->toContain('EXIF')
        ->and($exif['SectionsFound'])->not->toContain('GPS');
    expect(Storage::disk('public')->allFiles())->toBe([$stored->path]);

    $this->get('/gateaux/layer-cake')
        ->assertOk()
        ->assertSee('Layer cake rose et or')
        ->assertSee('à partir de 55,50 €')
        ->assertSee('8 ou 12 parts');
});

it('shows the new "from" price on the public page right after an edit', function () {
    $type = CakeType::factory()->create(['slug' => 'fraisier', 'from_price_cents' => 4000, 'is_published' => true]);
    $existing = CakeTypePhoto::factory()->for($type)->create();
    Storage::disk('public')->put($existing->path, jpegWithGpsExif());
    $this->get('/gateaux/fraisier')->assertSee('à partir de 40 €');

    Livewire::test(EditCakeType::class, ['record' => $type->getRouteKey()])
        ->assertFormSet(['from_price_cents' => '40'])
        ->fillForm(['from_price_cents' => '48'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($type->fresh()->from_price_cents)->toBe(4800);
    $this->get('/gateaux/fraisier')->assertSee('à partir de 48 €')->assertDontSee('à partir de 40 €');
});

it('refuses a negative price, a negative lead time, no portion format or a taken slug', function (array $data, string $field) {
    CakeType::factory()->create(['slug' => 'number-cake']);

    Livewire::test(CreateCakeType::class)
        ->fillForm([
            'name' => 'Nouveau',
            'slug' => 'nouveau',
            'description' => 'Un gâteau.',
            'from_price_cents' => '30',
            'portion_formats' => ['6'],
            'min_lead_days' => 3,
            ...$data,
        ])
        ->call('create')
        ->assertHasFormErrors([$field]);

    expect(CakeType::count())->toBe(1);
})->with([
    'prix négatif' => [['from_price_cents' => '-1'], 'from_price_cents'],
    'délai négatif' => [['min_lead_days' => -1], 'min_lead_days'],
    'aucun format' => [['portion_formats' => []], 'portion_formats'],
    'slug déjà pris' => [['slug' => 'number-cake'], 'slug'],
]);

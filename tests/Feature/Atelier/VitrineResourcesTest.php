<?php

use App\Enums\ContactOccasion;
use App\Enums\CreationCategory;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\Creations\Pages\CreateCreation;
use App\Filament\Resources\Creations\Pages\EditCreation;
use App\Filament\Resources\Creations\Pages\ListCreations;
use App\Models\ContactMessage;
use App\Models\Creation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['has_email_authentication' => true]));
});

it('creates a creation with a sanitized photo and shows it in the gallery', function () {
    $photo = UploadedFile::fake()->createWithContent('tarte.jpg', jpegWithGpsExif());

    Livewire::test(CreateCreation::class)
        ->fillForm([
            'name' => 'Tarte aux fruits rouges',
            'category' => CreationCategory::Tartes->value,
            'description' => 'Pâte sablée, crème d’amande, fruits du marché.',
            'image_path' => $photo,
            'sort_order' => 1,
            'is_featured' => true,
            'is_published' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $creation = Creation::sole();
    expect($creation->category)->toBe(CreationCategory::Tartes)
        ->and($creation->is_featured)->toBeTrue()
        ->and($creation->image_path)->toStartWith('creations/')->toEndWith('.jpg');
    Storage::disk('public')->assertExists($creation->image_path);
    $exif = @exif_read_data(Storage::disk('public')->path($creation->image_path));
    expect($exif)->not->toHaveKey('GPSLatitude');

    Livewire::test(ListCreations::class)->assertSee('Tarte aux fruits rouges')->assertSee('Tartes');

    $this->get('/')
        ->assertOk()
        ->assertSee('Tarte aux fruits rouges')
        ->assertSee($creation->imageUrl());
});

it('edits a creation that only has an external illustration', function () {
    $creation = Creation::factory()->create(['name' => 'Brouillon', 'image_url' => 'https://images.example.test/a.jpg']);

    Livewire::test(EditCreation::class, ['record' => $creation->getKey()])
        ->assertFormSet(['image_url' => 'https://images.example.test/a.jpg'])
        ->fillForm(['name' => 'Mousse chocolat', 'category' => CreationCategory::Entremets->value, 'is_published' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($creation->refresh())
        ->name->toBe('Mousse chocolat')
        ->category->toBe(CreationCategory::Entremets)
        ->is_published->toBeFalse();
});

it('lists the contact messages, newest first, and marks one as read when opened', function () {
    $old = ContactMessage::factory()->create(['first_name' => 'Ancien', 'created_at' => now()->subDay()]);
    $new = ContactMessage::factory()->create([
        'first_name' => 'Camille',
        'contact' => 'camille@mail.fr',
        'occasion' => ContactOccasion::Mariage,
        'message' => 'Un layer cake pour 40 personnes.',
    ]);

    Livewire::test(ListContactMessages::class)
        ->assertSeeInOrder(['Camille', 'Ancien'])
        ->assertSee('Nouveau');

    Livewire::test(ViewContactMessage::class, ['record' => $new->getKey()])
        ->assertSee('camille@mail.fr')
        ->assertSee('Mariage')
        ->assertSee('Un layer cake pour 40 personnes.');

    expect($new->refresh()->read_at)->not->toBeNull()
        ->and($old->refresh()->read_at)->toBeNull();
});

<?php

namespace App\Filament\Resources\CakeTypes;

use App\Enums\Allergen;
use App\Filament\Forms\DecimalInput;
use App\Filament\Resources\CakeTypes\Pages\CreateCakeType;
use App\Filament\Resources\CakeTypes\Pages\EditCakeType;
use App\Filament\Resources\CakeTypes\Pages\ListCakeTypes;
use App\Models\CakeType;
use App\Services\ImageSanitizer;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CakeTypeResource extends Resource
{
    protected static ?string $model = CakeType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCake;

    protected static ?string $modelLabel = 'type de gâteau';

    protected static ?string $pluralModelLabel = 'types de gâteaux';

    protected static ?string $navigationLabel = 'Collection';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Présentation')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            // The slug follows the name until it is edited by hand.
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                                if (($get('slug') ?? '') === Str::slug($old ?? '')) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Adresse de la page')
                            ->prefix('/gateaux/')
                            ->required()
                            ->maxLength(255)
                            ->alphaDash()
                            ->unique(ignoreRecord: true)
                            ->validationMessages(['unique' => 'Un autre type utilise déjà cette adresse.']),
                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(4),
                    ]),
                Section::make('Prix, formats et délai')
                    ->schema([
                        DecimalInput::euros('from_price_cents')
                            ->label('Prix « à partir de »'),
                        TagsInput::make('portion_formats')
                            ->label('Formats (nombre de parts)')
                            ->placeholder('Ajouter un nombre de parts')
                            ->helperText('Par exemple 6, 8, 12. Validez chaque nombre avec Entrée.')
                            ->required()
                            ->nestedRecursiveRules(['integer', 'min:1'])
                            ->validationMessages([
                                'required' => 'Indiquez au moins un format.',
                                '*.integer' => 'Chaque format est un nombre de parts.',
                                '*.min' => 'Chaque format est un nombre de parts.',
                            ])
                            ->dehydrateStateUsing(fn (array $state): array => array_values(array_map('intval', $state))),
                        TextInput::make('min_lead_days')
                            ->label('Délai minimum de commande')
                            ->suffix('jours')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->validationMessages(['min' => 'Le délai ne peut pas être négatif.']),
                        TagsInput::make('customizable_options')
                            ->label('Ce qui peut être personnalisé')
                            ->placeholder('Ajouter (forme, photo, inscription, couleurs…)')
                            ->default([])
                            ->dehydrateStateUsing(fn (?array $state): array => array_values($state ?? [])),
                    ]),
                Section::make('Allergènes de base')
                    ->schema([
                        CheckboxList::make('base_allergens')
                            ->hiddenLabel()
                            ->options(collect(Allergen::cases())->mapWithKeys(
                                fn (Allergen $allergen) => [$allergen->value => Str::ucfirst($allergen->label())],
                            )->all())
                            ->columns(2)
                            ->default([])
                            ->dehydrateStateUsing(fn (?array $state): array => array_values($state ?? [])),
                    ]),
                Section::make('Photos de réalisations')
                    ->description('Chaque photo est réduite et réencodée : ses métadonnées (lieu, appareil) sont retirées.')
                    ->schema([
                        Repeater::make('photos')
                            ->hiddenLabel()
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->addActionLabel('Ajouter une photo')
                            ->defaultItems(0)
                            ->schema([
                                FileUpload::make('path')
                                    ->label('Photo')
                                    ->image()
                                    ->disk('public')
                                    ->directory('cake-types')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(10 * 1024)
                                    ->required()
                                    // KTD7: no public image is written without being re-encoded.
                                    ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(ImageSanitizer::class)->store($file, 'public', 'cake-types')),
                                TextInput::make('alt')
                                    ->label('Description de la photo')
                                    ->helperText('Lue par les lecteurs d’écran et les moteurs de recherche.')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ]),
                Section::make('Publication')
                    ->schema([
                        TextInput::make('sort_order')
                            ->label('Ordre d’affichage')
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Toggle::make('is_published')
                            ->label('Publié sur le site')
                            ->helperText('Un type publié apparaît sur le site dès qu’il a au moins une photo.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')->label('Nom')->searchable(),
                TextColumn::make('from_price_cents')
                    ->label('À partir de')
                    ->formatStateUsing(fn (int $state): string => Money::format($state)),
                TextColumn::make('photos_count')->label('Photos')->counts('photos'),
                IconColumn::make('is_published')->label('Publié')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCakeTypes::route('/'),
            'create' => CreateCakeType::route('/create'),
            'edit' => EditCakeType::route('/{record}/edit'),
        ];
    }
}

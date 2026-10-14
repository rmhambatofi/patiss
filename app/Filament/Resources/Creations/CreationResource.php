<?php

namespace App\Filament\Resources\Creations;

use App\Enums\CreationCategory;
use App\Filament\Resources\Creations\Pages\CreateCreation;
use App\Filament\Resources\Creations\Pages\EditCreation;
use App\Filament\Resources\Creations\Pages\ListCreations;
use App\Models\Creation;
use App\Services\ImageSanitizer;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Galerie « Mes créations » de la vitrine.
 */
class CreationResource extends Resource
{
    protected static ?string $model = Creation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $modelLabel = 'création';

    protected static ?string $pluralModelLabel = 'créations';

    protected static ?string $navigationLabel = 'Créations';

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Pièce')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        Select::make('category')
                            ->label('Catégorie')
                            ->options(CreationCategory::options())
                            ->required()
                            ->native(false),
                        Textarea::make('description')
                            ->label('Description')
                            ->helperText('Une phrase courte et concrète : « Génoise vanille, crème légère, fleurs fraîches. »')
                            ->rows(3)
                            ->maxLength(500),
                    ]),
                Section::make('Photo')
                    ->description('La photo téléversée est réduite et réencodée (métadonnées retirées). À défaut, une adresse d’illustration externe est affichée.')
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Photo')
                            ->image()
                            ->disk('public')
                            ->directory('creations')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(10 * 1024)
                            // KTD7: no public image is written without being re-encoded.
                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(ImageSanitizer::class)->store($file, 'public', 'creations')),
                        TextInput::make('image_url')
                            ->label('Adresse d’une photo d’illustration')
                            ->helperText('Utilisée seulement sans photo téléversée. Mentionner la source dans le pied de page si besoin.')
                            ->url()
                            ->maxLength(2048),
                    ]),
                Section::make('Publication')
                    ->schema([
                        TextInput::make('sort_order')
                            ->label('Ordre d’affichage')
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Toggle::make('is_featured')
                            ->label('Mise en avant')
                            ->helperText('Les pièces mises en avant forment les quatre photos d’ouverture de la page.'),
                        Toggle::make('is_published')
                            ->label('Publiée sur le site')
                            ->default(true)
                            ->helperText('Une pièce publiée apparaît dès qu’elle a une photo.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->state(fn (Creation $record): ?string => $record->imageUrl())
                    ->square(),
                TextColumn::make('name')->label('Nom')->searchable(),
                TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->formatStateUsing(fn (CreationCategory $state): string => $state->label()),
                TextColumn::make('sort_order')->label('Ordre')->sortable(),
                IconColumn::make('is_featured')->label('Mise en avant')->boolean(),
                IconColumn::make('is_published')->label('Publiée')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCreations::route('/'),
            'create' => CreateCreation::route('/create'),
            'edit' => EditCreation::route('/{record}/edit'),
        ];
    }
}

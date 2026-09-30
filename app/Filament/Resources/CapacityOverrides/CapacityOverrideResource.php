<?php

namespace App\Filament\Resources\CapacityOverrides;

use App\Filament\Resources\CapacityOverrides\Pages\CreateCapacityOverride;
use App\Filament\Resources\CapacityOverrides\Pages\EditCapacityOverride;
use App\Filament\Resources\CapacityOverrides\Pages\ListCapacityOverrides;
use App\Models\CapacityOverride;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CapacityOverrideResource extends Resource
{
    protected static ?string $model = CapacityOverride::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $modelLabel = 'capacité d’une date';

    protected static ?string $pluralModelLabel = 'capacités par date';

    protected static ?string $navigationLabel = 'Capacité par date';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                DatePicker::make('date')
                    ->label('Date')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->unique(ignoreRecord: true)
                    ->validationMessages(['unique' => 'Cette date a déjà une capacité particulière.']),
                TextInput::make('capacity')
                    ->label('Nombre maximal de gâteaux')
                    ->helperText('Remplace la capacité par jour des réglages pour cette date. 0 ferme la date.')
                    ->suffix('gâteaux')
                    ->required()
                    ->integer()
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date')
            ->columns([
                TextColumn::make('date')->label('Date')->date('d/m/Y'),
                TextColumn::make('capacity')
                    ->label('Capacité')
                    ->formatStateUsing(fn (int $state): string => $state.' '.($state > 1 ? 'gâteaux' : 'gâteau')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCapacityOverrides::route('/'),
            'create' => CreateCapacityOverride::route('/create'),
            'edit' => EditCapacityOverride::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\DeliveryTiers;

use App\Filament\Forms\DecimalInput;
use App\Filament\Resources\DeliveryTiers\Pages\CreateDeliveryTier;
use App\Filament\Resources\DeliveryTiers\Pages\EditDeliveryTier;
use App\Filament\Resources\DeliveryTiers\Pages\ListDeliveryTiers;
use App\Models\DeliveryTier;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliveryTierResource extends Resource
{
    protected static ?string $model = DeliveryTier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $modelLabel = 'palier de livraison';

    protected static ?string $pluralModelLabel = 'paliers de livraison';

    protected static ?string $navigationLabel = 'Livraison';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                DecimalInput::kilometres('max_km')
                    ->label('Jusqu’à')
                    ->helperText('Distance à vol d’oiseau depuis Dammartin-en-Goële.'),
                DecimalInput::euros('price_cents')
                    ->label('Prix de la livraison'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('max_km')
            ->paginated(false)
            ->columns([
                TextColumn::make('max_km')
                    ->label('Distance')
                    ->formatStateUsing(fn (DeliveryTier $record): string => 'jusqu\'à '.$record->maxKmLabel().' km'),
                TextColumn::make('price_cents')
                    ->label('Prix')
                    ->formatStateUsing(fn (DeliveryTier $record): string => $record->priceLabel()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryTiers::route('/'),
            'create' => CreateDeliveryTier::route('/create'),
            'edit' => EditDeliveryTier::route('/{record}/edit'),
        ];
    }
}

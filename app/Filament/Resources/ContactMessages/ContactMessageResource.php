<?php

namespace App\Filament\Resources\ContactMessages;

use App\Enums\ContactOccasion;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Messages du formulaire de contact : liste et lecture, rien d'autre.
 */
class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $modelLabel = 'message';

    protected static ?string $pluralModelLabel = 'messages';

    protected static ?string $navigationLabel = 'Messages';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'first_name';

    public static function getNavigationBadge(): ?string
    {
        $unread = ContactMessage::whereNull('read_at')->count();

        return $unread > 0 ? (string) $unread : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('first_name')->label('Prénom'),
                        TextEntry::make('created_at')->label('Reçu le')->dateTime('d/m/Y H:i'),
                        TextEntry::make('contact')->label('Pour répondre')->copyable(),
                        TextEntry::make('occasion')
                            ->label('Occasion')
                            ->badge()
                            ->formatStateUsing(fn (ContactOccasion $state): string => $state->label()),
                        TextEntry::make('message')->label('Message')->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Reçu le')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->weight(fn (ContactMessage $record): ?string => $record->read_at ? null : 'bold'),
                TextColumn::make('occasion')
                    ->label('Occasion')
                    ->badge()
                    ->formatStateUsing(fn (ContactOccasion $state): string => $state->label()),
                TextColumn::make('read_at')
                    ->label('Statut')
                    ->badge()
                    ->state(fn (ContactMessage $record): string => $record->read_at ? 'Lu' : 'Nouveau')
                    ->color(fn (string $state): string => $state === 'Lu' ? 'gray' : 'info'),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
            'view' => ViewContactMessage::route('/{record}'),
        ];
    }
}

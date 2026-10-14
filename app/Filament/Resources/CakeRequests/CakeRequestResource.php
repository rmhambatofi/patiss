<?php

namespace App\Filament\Resources\CakeRequests;

use App\Enums\Fulfilment;
use App\Enums\RequestStatus;
use App\Filament\Resources\CakeRequests\Pages\ListCakeRequests;
use App\Filament\Resources\CakeRequests\Pages\ViewCakeRequest;
use App\Models\CakeRequest;
use App\Models\DepositLink;
use App\Models\RequestPhoto;
use App\Services\CapacityGuard;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Client requests (U7): read-only list and page, answered with "Proposer" or "Refuser".
 */
class CakeRequestResource extends Resource
{
    protected static ?string $model = CakeRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static ?string $modelLabel = 'demande';

    protected static ?string $pluralModelLabel = 'demandes';

    protected static ?string $navigationLabel = 'Demandes';

    protected static ?string $recordTitleAttribute = 'reference';

    protected static ?int $navigationSort = 0;

    public static function statusColor(RequestStatus $status): string
    {
        return match ($status) {
            RequestStatus::Nouvelle => 'info',
            RequestStatus::Proposee => 'warning',
            RequestStatus::Reservee => 'success',
            RequestStatus::Expiree, RequestStatus::Remboursee => 'gray',
            RequestStatus::Refusee => 'danger',
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('event_date')
            ->modifyQueryUsing(fn ($query) => $query->with('cakeType', 'commune'))
            ->columns([
                TextColumn::make('reference')->label('Référence')->searchable()->weight('bold'),
                TextColumn::make('event_date')->label('Date')->date('d/m/Y')->sortable(),
                TextColumn::make('cake_type_id')
                    ->label('Type')
                    ->state(fn (CakeRequest $record): string => $record->isFree() ? 'Demande libre' : $record->typeLabel()),
                TextColumn::make('commune_id')
                    ->label('Retrait / livraison')
                    ->state(fn (CakeRequest $record): string => $record->fulfilment === Fulfilment::Delivery
                        ? ($record->commune?->name ?? 'Livraison')
                        : 'Retrait'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (RequestStatus $state): string => $state->label())
                    ->color(fn (RequestStatus $state): string => static::statusColor($state)),
                TextColumn::make('out_of_lead_time')
                    ->label('Délai')
                    ->badge()
                    ->color('danger')
                    ->formatStateUsing(fn (bool $state): ?string => $state ? 'Hors délai' : null),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(collect(RequestStatus::cases())->mapWithKeys(fn (RequestStatus $s) => [$s->value => $s->label()])),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        $euros = fn (?int $cents): ?string => $cents === null ? null : Money::format($cents);

        return $schema
            ->columns(1)
            ->components([
                Section::make('Charge du jour')
                    ->schema([
                        TextEntry::make('day_load')
                            ->hiddenLabel()
                            ->state(fn (CakeRequest $record): string => app(CapacityGuard::class)->loadLabel($record->event_date)),
                    ]),
                Section::make('Demande')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('reference')->label('Référence'),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn (RequestStatus $state): string => $state->label())
                            ->color(fn (RequestStatus $state): string => static::statusColor($state)),
                        TextEntry::make('type')
                            ->label('Gâteau')
                            ->state(fn (CakeRequest $record): string => $record->isFree() ? 'Demande libre' : $record->typeLabel()),
                        TextEntry::make('portions')->label('Parts')->suffix(' parts'),
                        TextEntry::make('event_date')
                            ->label('Date de l’événement')
                            ->formatStateUsing(fn ($state): string => $state->locale('fr')->translatedFormat('l j F Y')),
                        TextEntry::make('out_of_lead_time')
                            ->label('Délai')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'danger' : 'success')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Hors délai' : 'Dans les délais'),
                        TextEntry::make('customization')
                            ->label(fn (CakeRequest $record): string => $record->isFree() ? 'Projet' : 'Personnalisation')
                            ->placeholder('Aucune')
                            ->columnSpanFull(),
                        TextEntry::make('allergies')
                            ->label('Allergies signalées')
                            ->placeholder('Aucune')
                            ->columnSpanFull(),
                    ]),
                Section::make('Retrait ou livraison')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('fulfilment')
                            ->label('Mode')
                            ->formatStateUsing(fn (Fulfilment $state): string => $state->label()),
                        TextEntry::make('commune.name')->label('Commune')->placeholder('—'),
                        TextEntry::make('delivery_fee_cents')
                            ->label('Tarif de zone')
                            ->formatStateUsing(fn (?int $state): ?string => $euros($state))
                            ->placeholder('—'),
                        // Typed by the client on the Stripe page, copied by the webhook (U8).
                        TextEntry::make('delivery_address')
                            ->label('Adresse de livraison (saisie au paiement)')
                            ->visible(fn (CakeRequest $record): bool => filled($record->delivery_line1))
                            ->state(fn (CakeRequest $record): string => collect([
                                $record->delivery_name,
                                $record->delivery_line1,
                                $record->delivery_line2,
                                trim($record->delivery_postal_code.' '.$record->delivery_city),
                            ])->filter()->join(', ')),
                        TextEntry::make('delivery_commune_mismatch')
                            ->hiddenLabel()
                            ->visible(fn (CakeRequest $record): bool => $record->delivery_commune_mismatch)
                            ->badge()
                            ->color('danger')
                            ->state('Commune différente du tarif'),
                    ]),
                Section::make('Client')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Nom'),
                        TextEntry::make('email')->label('E-mail')->copyable(),
                        TextEntry::make('phone')->label('Téléphone')->copyable(),
                        TextEntry::make('source')
                            ->label('A connu l’artisan par')
                            ->formatStateUsing(fn ($state): string => $state->label()),
                        TextEntry::make('created_at')->label('Reçue le')->dateTime('d/m/Y H:i'),
                    ]),
                Section::make('Proposition en cours')
                    ->visible(fn (CakeRequest $record): bool => static::activeLink($record) !== null)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('offer_total')
                            ->label('Prix total')
                            ->state(fn (CakeRequest $record): ?string => $euros(static::activeLink($record)?->total_cents)),
                        TextEntry::make('offer_deposit')
                            ->label('Acompte')
                            ->state(fn (CakeRequest $record): ?string => $euros(static::activeLink($record)?->deposit_cents)),
                        TextEntry::make('offer_expires')
                            ->label('Lien valable jusqu’au')
                            ->state(fn (CakeRequest $record): ?string => static::activeLink($record)?->expires_at->format('d/m/Y H:i')),
                        TextEntry::make('offer_slot')
                            ->label('Créneau')
                            ->state(fn (CakeRequest $record): ?string => static::activeLink($record)?->slot),
                        // AE5: the client can no longer pay this link while the date is full.
                        TextEntry::make('offer_blocked')
                            ->hiddenLabel()
                            ->visible(fn (CakeRequest $record): bool => $record->status === RequestStatus::Proposee
                                && app(CapacityGuard::class)->isFull($record->event_date))
                            ->badge()
                            ->color('danger')
                            ->state('Bloqué : date complète'),
                    ]),
                Section::make('Photos')
                    ->visible(fn (CakeRequest $record): bool => $record->photos->isNotEmpty())
                    ->schema([
                        RepeatableEntry::make('photos')
                            ->hiddenLabel()
                            ->grid(['default' => 1, 'sm' => 2])
                            ->schema([
                                ImageEntry::make('path')
                                    ->hiddenLabel()
                                    ->state(fn (RequestPhoto $record): string => route('atelier.request-photo', $record))
                                    ->url(fn (RequestPhoto $record): string => route('atelier.request-photo', $record), shouldOpenInNewTab: true)
                                    ->imageHeight(200),
                                TextEntry::make('kind')
                                    ->hiddenLabel()
                                    ->formatStateUsing(fn (string $state): string => $state === RequestPhoto::KIND_TO_REPRODUCE ? 'À reproduire' : 'Inspiration'),
                            ]),
                    ]),
            ]);
    }

    public static function activeLink(CakeRequest $record): ?DepositLink
    {
        return $record->depositLinks()->where('status', DepositLink::STATUS_ACTIVE)->latest('id')->first();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCakeRequests::route('/'),
            'view' => ViewCakeRequest::route('/{record}'),
        ];
    }
}

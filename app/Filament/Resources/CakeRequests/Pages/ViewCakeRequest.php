<?php

namespace App\Filament\Resources\CakeRequests\Pages;

use App\Actions\AnswerCakeRequest;
use App\Enums\Allergen;
use App\Filament\Forms\DecimalInput;
use App\Filament\Resources\CakeRequests\CakeRequestResource;
use App\Models\CakeRequest;
use App\Settings\ShopSettings;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

/**
 * @property CakeRequest $record
 */
class ViewCakeRequest extends ViewRecord
{
    protected static string $resource = CakeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->proposeAction(),
            $this->declineAction(),
        ];
    }

    /**
     * Priced answer with a deposit link (R12, R13, R17): one active link per request.
     */
    private function proposeAction(): Action
    {
        $settings = app(ShopSettings::class);

        return Action::make('propose')
            ->label('Proposer')
            ->icon('heroicon-o-paper-airplane')
            ->modalHeading('Proposer un prix et un lien d’acompte')
            ->modalSubmitActionLabel('Envoyer la proposition')
            ->visible(fn (CakeRequest $record): bool => in_array($record->status, AnswerCakeRequest::PROPOSABLE, true))
            ->fillForm(fn (CakeRequest $record): array => [
                'total' => $record->delivery_fee_cents,
                'deposit' => $record->delivery_fee_cents === null ? null : self::depositFor($record->delivery_fee_cents, $settings),
                'allergens' => $record->cakeType?->base_allergens?->map->value->all() ?? [],
                'validity_days' => $settings->default_link_validity_days,
            ])
            ->schema([
                DecimalInput::euros('total')
                    ->label('Prix total, livraison comprise')
                    ->helperText('Prérempli avec le tarif de zone : ajoutez le prix du gâteau.')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set) use ($settings) {
                        $cents = self::toCents($state);
                        if ($cents !== null) {
                            $set('deposit', number_format(self::depositFor($cents, $settings) / 100, 2, ',', ''));
                        }
                    }),
                DecimalInput::euros('deposit')
                    ->label('Acompte')
                    ->helperText("Calculé avec le taux de {$settings->deposit_rate_percent} %, modifiable.")
                    ->rule(fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get) {
                        $deposit = self::toCents($value);
                        $total = self::toCents($get('total'));
                        if ($deposit !== null && $deposit <= 0) {
                            $fail('L’acompte doit être supérieur à 0 €.');
                        } elseif ($deposit !== null && $total !== null && $deposit > $total) {
                            $fail('L’acompte ne peut pas dépasser le prix total.');
                        }
                    }),
                CheckboxList::make('allergens')
                    ->label('Allergènes du gâteau commandé')
                    ->options(collect(Allergen::cases())->mapWithKeys(fn (Allergen $a) => [$a->value => ucfirst($a->label())]))
                    ->columns(2)
                    ->live(),
                Checkbox::make('no_allergen_confirmed')
                    ->label('Je confirme que ce gâteau ne contient aucun des 14 allergènes réglementaires.')
                    ->visible(fn (Get $get): bool => blank($get('allergens')))
                    ->accepted()
                    ->validationMessages(['accepted' => 'Cochez au moins un allergène, ou confirmez qu’il n’y en a aucun.']),
                TextInput::make('slot')
                    ->label('Créneau de retrait ou de livraison')
                    ->placeholder('Samedi 14 novembre, entre 14 h et 16 h')
                    ->required()
                    ->maxLength(255),
                Textarea::make('message')
                    ->label('Message au client')
                    ->rows(3),
                TextInput::make('validity_days')
                    ->label('Validité du lien')
                    ->suffix('jours')
                    ->required()
                    ->integer()
                    ->minValue(1),
            ])
            ->action(function (CakeRequest $record, array $data) {
                app(AnswerCakeRequest::class)->propose($record, [
                    'total_cents' => $data['total'],
                    'deposit_cents' => $data['deposit'],
                    'allergens' => array_values($data['allergens'] ?? []),
                    'slot' => $data['slot'],
                    'message' => $data['message'] ?? null,
                    'validity_days' => (int) $data['validity_days'],
                ]);

                Notification::make()->success()->title('Proposition envoyée')->send();
            });
    }

    private function declineAction(): Action
    {
        return Action::make('decline')
            ->label('Refuser')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->modalHeading('Refuser la demande')
            ->modalSubmitActionLabel('Envoyer le refus')
            ->visible(fn (CakeRequest $record): bool => in_array($record->status, AnswerCakeRequest::DECLINABLE, true))
            ->fillForm(fn (CakeRequest $record): array => ['message' => AnswerCakeRequest::defaultRefusal($record)])
            ->schema([
                Textarea::make('message')
                    ->label('Message au client')
                    ->required()
                    ->rows(6),
            ])
            ->action(function (CakeRequest $record, array $data) {
                app(AnswerCakeRequest::class)->decline($record, $data['message']);

                Notification::make()->success()->title('Refus envoyé')->send();
            });
    }

    private static function depositFor(int $totalCents, ShopSettings $settings): int
    {
        return (int) round($totalCents * ($settings->deposit_rate_percent ?? 0) / 100);
    }

    private static function toCents(mixed $value): ?int
    {
        if (! is_string($value) || ! preg_match('/^\d+([.,]\d{1,2})?$/', $value)) {
            return null;
        }

        return (int) round((float) str_replace(',', '.', $value) * 100);
    }
}

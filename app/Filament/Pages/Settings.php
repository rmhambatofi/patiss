<?php

namespace App\Filament\Pages;

use App\Settings\ShopSettings;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Settings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = ShopSettings::class;

    protected static ?string $navigationLabel = 'Réglages';

    protected static ?string $title = 'Réglages';

    protected static ?int $navigationSort = 10;

    /**
     * Labels of the settings the public request forms need (KTD10).
     */
    private const REQUIRED_LABELS = [
        'legal_address' => 'Adresse professionnelle',
        'siret' => 'SIRET',
        'deposit_rate_percent' => 'Taux d’acompte',
        'cancellation_terms' => 'Conditions d’annulation',
        'default_link_validity_days' => 'Durée de validité des liens de paiement',
    ];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                $this->closedFormsNotice(),
                Section::make('Entreprise')
                    ->schema([
                        TextInput::make('business_name')
                            ->label('Nom commercial')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('legal_address')
                            ->label(self::REQUIRED_LABELS['legal_address'])
                            ->helperText('Affichée dans les mentions légales et les conditions de vente.')
                            ->rows(3),
                        TextInput::make('siret')
                            ->label(self::REQUIRED_LABELS['siret'])
                            ->regex('/^\d{14}$/')
                            ->validationMessages(['regex' => 'Le SIRET compte 14 chiffres, sans espace.']),
                        TextInput::make('notification_email')
                            ->label('E-mail de notification des demandes')
                            ->email(),
                        TextInput::make('google_review_url')
                            ->label('Lien d’avis Google')
                            ->url(),
                    ]),
                Section::make('Conditions de vente')
                    ->schema([
                        TextInput::make('deposit_rate_percent')
                            ->label(self::REQUIRED_LABELS['deposit_rate_percent'])
                            ->suffix('%')
                            ->integer()
                            ->minValue(1)
                            ->maxValue(100),
                        Textarea::make('cancellation_terms')
                            ->label(self::REQUIRED_LABELS['cancellation_terms'])
                            ->rows(5),
                        TextInput::make('default_link_validity_days')
                            ->label(self::REQUIRED_LABELS['default_link_validity_days'])
                            ->helperText('Durée proposée par défaut pour chaque lien d’acompte.')
                            ->suffix('jours')
                            ->integer()
                            ->minValue(1),
                    ]),
                Section::make('Organisation')
                    ->schema([
                        TextInput::make('default_daily_capacity')
                            ->label('Nombre maximal de gâteaux par jour')
                            ->helperText('Ajustable pour une date précise dans « Capacité par date ».')
                            ->suffix('gâteaux')
                            ->required()
                            ->integer()
                            ->minValue(0),
                        TextInput::make('free_request_lead_days')
                            ->label('Délai de la demande libre')
                            ->suffix('jours')
                            ->required()
                            ->integer()
                            ->minValue(0),
                        Textarea::make('pickup_address')
                            ->label('Adresse de retrait')
                            ->helperText('Jamais affichée sur le site : envoyée uniquement dans l’e-mail de confirmation, après paiement de l’acompte.')
                            ->rows(3),
                    ]),
            ]);
    }

    private function closedFormsNotice(): Callout
    {
        return Callout::make('Les formulaires de demande sont fermés')
            ->description(fn (): string => 'Réglages à remplir avant de recevoir des demandes : '
                .collect(app(ShopSettings::class)->missingRequiredSettings())
                    ->map(fn (string $name): string => self::REQUIRED_LABELS[$name] ?? $name)
                    ->implode(', ')
                .'.')
            ->warning()
            ->visible(fn (): bool => ! app(ShopSettings::class)->isOpenForRequests());
    }
}

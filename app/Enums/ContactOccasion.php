<?php

namespace App\Enums;

/**
 * Occasion choisie dans le formulaire de contact de la vitrine.
 */
enum ContactOccasion: string
{
    case Anniversaire = 'anniversaire';
    case Mariage = 'mariage';
    case Bapteme = 'bapteme';
    case Entreprise = 'entreprise';
    case Autre = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::Anniversaire => 'Anniversaire',
            self::Mariage => 'Mariage',
            self::Bapteme => 'Baptême',
            self::Entreprise => 'Entreprise',
            self::Autre => 'Autre',
        };
    }

    /**
     * @return array<string, string> value => label, in display order
     */
    public static function options(): array
    {
        return array_combine(
            array_map(fn (self $case) => $case->value, self::cases()),
            array_map(fn (self $case) => $case->label(), self::cases()),
        );
    }
}

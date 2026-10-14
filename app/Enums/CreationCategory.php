<?php

namespace App\Enums;

/**
 * Catégories de la galerie « Mes créations » (filtres de la vitrine).
 */
enum CreationCategory: string
{
    case Entremets = 'entremets';
    case Tartes = 'tartes';
    case GateauxDeFete = 'gateaux-de-fete';
    case PetitsDesserts = 'petits-desserts';
    case Patisseries = 'patisseries';

    public function label(): string
    {
        return match ($this) {
            self::Entremets => 'Entremets',
            self::Tartes => 'Tartes',
            self::GateauxDeFete => 'Gâteaux de fête',
            self::PetitsDesserts => 'Petits desserts',
            self::Patisseries => 'Pâtisseries',
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

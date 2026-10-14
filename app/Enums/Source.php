<?php

namespace App\Enums;

/**
 * Answer to "Comment avez-vous connu l'artisan ?" (R8).
 */
enum Source: string
{
    case Google = 'google';
    case Instagram = 'instagram';
    case BoucheAOreille = 'bouche_a_oreille';
    case DejaClient = 'deja_client';
    case Autre = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::Google => 'Recherche Google / fiche Google',
            self::Instagram => 'Instagram',
            self::BoucheAOreille => 'Bouche-à-oreille',
            self::DejaClient => 'Déjà client',
            self::Autre => 'Autre',
        };
    }
}

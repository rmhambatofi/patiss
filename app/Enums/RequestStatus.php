<?php

namespace App\Enums;

/**
 * Life cycle of a cake request, from submission to deposit or refusal.
 */
enum RequestStatus: string
{
    case Nouvelle = 'nouvelle';
    case Proposee = 'proposee';
    case Reservee = 'reservee';
    case Expiree = 'expiree';
    case Refusee = 'refusee';
    case Remboursee = 'remboursee';

    public function label(): string
    {
        return match ($this) {
            self::Nouvelle => 'Nouvelle',
            self::Proposee => 'Proposée',
            self::Reservee => 'Réservée',
            self::Expiree => 'Expirée',
            self::Refusee => 'Refusée',
            self::Remboursee => 'Remboursée',
        };
    }
}

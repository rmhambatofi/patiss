<?php

namespace App\Enums;

enum Fulfilment: string
{
    case Pickup = 'pickup';
    case Delivery = 'delivery';

    public function label(): string
    {
        return match ($this) {
            self::Pickup => 'Retrait sur place (gratuit)',
            self::Delivery => 'Livraison',
        };
    }
}

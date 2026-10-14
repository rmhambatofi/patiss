<?php

namespace App\Enums;

/**
 * The 14 allergens of the EU INCO regulation (1169/2011, annex II).
 */
enum Allergen: string
{
    case Gluten = 'gluten';
    case Crustaceans = 'crustaces';
    case Eggs = 'oeufs';
    case Fish = 'poissons';
    case Peanuts = 'arachides';
    case Soy = 'soja';
    case Milk = 'lait';
    case Nuts = 'fruits_a_coque';
    case Celery = 'celeri';
    case Mustard = 'moutarde';
    case Sesame = 'sesame';
    case Sulphites = 'sulfites';
    case Lupin = 'lupin';
    case Molluscs = 'mollusques';

    public function label(): string
    {
        return match ($this) {
            self::Gluten => 'gluten',
            self::Crustaceans => 'crustacés',
            self::Eggs => 'œufs',
            self::Fish => 'poissons',
            self::Peanuts => 'arachides',
            self::Soy => 'soja',
            self::Milk => 'lait',
            self::Nuts => 'fruits à coque',
            self::Celery => 'céleri',
            self::Mustard => 'moutarde',
            self::Sesame => 'sésame',
            self::Sulphites => 'sulfites',
            self::Lupin => 'lupin',
            self::Molluscs => 'mollusques',
        };
    }
}

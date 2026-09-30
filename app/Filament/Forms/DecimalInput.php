<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\TextInput;

/**
 * Decimal fields typed on a French phone keyboard: "12,5" and "12.5" are both accepted.
 */
class DecimalInput
{
    private const PATTERN = '/^\d+([.,]\d{1,2})?$/';

    /**
     * An amount typed in euros and stored in cents.
     */
    public static function euros(string $name): TextInput
    {
        return TextInput::make($name)
            ->required()
            ->inputMode('decimal')
            ->suffix('€')
            ->regex(self::PATTERN)
            ->validationMessages([
                'required' => 'Indiquez un prix en euros.',
                'regex' => 'Indiquez un prix positif en euros, par exemple 45 ou 12,50.',
            ])
            ->formatStateUsing(fn ($state) => $state === null ? null : ((int) $state % 100 === 0
                ? (string) intdiv((int) $state, 100)
                : number_format($state / 100, 2, ',', '')))
            ->dehydrateStateUsing(fn (string $state): int => (int) round(self::toFloat($state) * 100));
    }

    /**
     * A distance in kilometres, strictly greater than zero.
     */
    public static function kilometres(string $name): TextInput
    {
        return TextInput::make($name)
            ->required()
            ->inputMode('decimal')
            ->suffix('km')
            ->regex(self::PATTERN)
            ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail) {
                if (is_string($value) && preg_match(self::PATTERN, $value) && self::toFloat($value) <= 0) {
                    $fail('La distance doit être supérieure à 0 km.');
                }
            })
            ->validationMessages([
                'required' => 'Indiquez une distance en kilomètres.',
                'regex' => 'Indiquez une distance positive en kilomètres, par exemple 10 ou 12,5.',
            ])
            ->formatStateUsing(fn ($state) => $state === null ? null : rtrim(rtrim(number_format((float) $state, 2, ',', ''), '0'), ','))
            ->dehydrateStateUsing(fn (string $state): string => number_format(self::toFloat($state), 2, '.', ''));
    }

    private static function toFloat(string $value): float
    {
        return (float) str_replace(',', '.', $value);
    }
}

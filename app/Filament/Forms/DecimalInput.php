<?php

namespace App\Filament\Forms;

use App\Support\Money;
use Filament\Forms\Components\TextInput;

/**
 * Decimal fields typed on a French phone keyboard: "12,5" and "12.5" are both accepted.
 */
class DecimalInput
{
    /**
     * An amount typed in euros and stored in cents.
     */
    public static function euros(string $name): TextInput
    {
        return TextInput::make($name)
            ->required()
            ->inputMode('decimal')
            ->suffix('€')
            ->regex(Money::INPUT_PATTERN)
            ->validationMessages([
                'required' => 'Indiquez un prix en euros.',
                'regex' => 'Indiquez un prix positif en euros, par exemple 45 ou 12,50.',
            ])
            ->formatStateUsing(fn ($state) => $state === null ? null : Money::toInput((int) $state))
            ->dehydrateStateUsing(fn (string $state): int => Money::fromInput($state));
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
            ->regex(Money::INPUT_PATTERN)
            ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail) {
                if (is_string($value) && preg_match(Money::INPUT_PATTERN, $value) && Money::parseDecimal($value) <= 0) {
                    $fail('La distance doit être supérieure à 0 km.');
                }
            })
            ->validationMessages([
                'required' => 'Indiquez une distance en kilomètres.',
                'regex' => 'Indiquez une distance positive en kilomètres, par exemple 10 ou 12,5.',
            ])
            ->formatStateUsing(fn ($state) => $state === null ? null : rtrim(rtrim(number_format((float) $state, 2, ',', ''), '0'), ','))
            ->dehydrateStateUsing(fn (string $state): string => number_format(Money::parseDecimal($state), 2, '.', ''));
    }
}

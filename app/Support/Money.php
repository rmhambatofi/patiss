<?php

namespace App\Support;

/**
 * Euro amounts stored in cents, shown and typed the French way ("12,50 €").
 */
class Money
{
    /** A positive decimal typed with a comma or a dot, at most two decimals: "45", "12,5", "12.50". */
    public const INPUT_PATTERN = '/^\d+([.,]\d{1,2})?$/';

    /** "45 €", "12,50 €" or "1 250 €". */
    public static function format(int $cents): string
    {
        $decimals = $cents % 100 === 0 ? 0 : 2;

        return number_format($cents / 100, $decimals, ',', ' ').' €';
    }

    /** Form value for an amount: "45" for whole euros, otherwise "12,50". */
    public static function toInput(int $cents): string
    {
        return $cents % 100 === 0
            ? (string) intdiv($cents, 100)
            : number_format($cents / 100, 2, ',', '');
    }

    /** Form value for an amount, always with two decimals: "45,00". */
    public static function toFixedInput(int $cents): string
    {
        return number_format($cents / 100, 2, ',', '');
    }

    /** "12,5" or "12.5" as a float, without validation. */
    public static function parseDecimal(string $value): float
    {
        return (float) str_replace(',', '.', $value);
    }

    /** Cents for an amount typed in euros, without validation. */
    public static function fromInput(string $value): int
    {
        return (int) round(self::parseDecimal($value) * 100);
    }

    /** Cents for an amount typed in euros, or null when it does not match INPUT_PATTERN. */
    public static function tryFromInput(mixed $value): ?int
    {
        if (! is_string($value) || ! preg_match(self::INPUT_PATTERN, $value)) {
            return null;
        }

        return self::fromInput($value);
    }
}

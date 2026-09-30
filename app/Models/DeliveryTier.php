<?php

namespace App\Models;

use Database\Factories\DeliveryTierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A delivery price for every commune up to max_km from Dammartin-en-Goële (KTD6).
 *
 * @property string $max_km
 * @property int $price_cents
 */
class DeliveryTier extends Model
{
    /** @use HasFactory<DeliveryTierFactory> */
    use HasFactory;

    protected $fillable = ['max_km', 'price_cents'];

    protected function casts(): array
    {
        return [
            'max_km' => 'decimal:2',
            'price_cents' => 'integer',
        ];
    }

    /** "10" or "12,5" (km, French decimal comma, no trailing zeros). */
    public function maxKmLabel(): string
    {
        return static::trimDecimals(number_format((float) $this->max_km, 2, ',', ''));
    }

    /** "5 €" or "12,50 €". */
    public function priceLabel(): string
    {
        $price = $this->price_cents % 100 === 0
            ? number_format($this->price_cents / 100, 0, ',', ' ')
            : number_format($this->price_cents / 100, 2, ',', ' ');

        return $price.' €';
    }

    private static function trimDecimals(string $number): string
    {
        return rtrim(rtrim($number, '0'), ',');
    }
}

<?php

namespace App\Models;

use Database\Factories\CommuneFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A commune of Île-de-France or Oise, imported from geo.api.gouv.fr (KTD6).
 *
 * @property string $insee_code
 * @property string $name
 * @property list<string> $postal_codes
 * @property string $department_code
 * @property string $distance_km Distance as the crow flies from Dammartin-en-Goële.
 */
class Commune extends Model
{
    /** @use HasFactory<CommuneFactory> */
    use HasFactory;

    protected $fillable = [
        'insee_code',
        'name',
        'postal_codes',
        'department_code',
        'latitude',
        'longitude',
        'distance_km',
    ];

    protected function casts(): array
    {
        return [
            'postal_codes' => 'array',
            'latitude' => 'decimal:5',
            'longitude' => 'decimal:5',
            'distance_km' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Commune $commune) {
            $commune->search_name = static::normalizeForSearch($commune->name);
        });
    }

    /**
     * Lowercase ASCII with every separator collapsed to one space, so that
     * "dammartin en goele" and "Dammartin-en-Goële" compare equal.
     */
    public static function normalizeForSearch(string $value): string
    {
        $ascii = Str::lower(Str::ascii($value));

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $ascii));
    }

    /**
     * Communes whose name or one of whose postal codes starts with the term.
     *
     * @param  Builder<Commune>  $query
     */
    public function scopeMatching(Builder $query, string $term): void
    {
        $name = static::normalizeForSearch($term);
        $digits = preg_replace('/\D/', '', $term);

        $query->where(function (Builder $query) use ($name, $digits, $term) {
            if ($name !== '') {
                $query->where('search_name', 'like', $name.'%');
            }

            if ($digits !== '' && $digits === trim($term)) {
                $query->orWhere('postal_codes', 'like', '%"'.$digits.'%');
            }
        });
    }
}

<?php

namespace App\Models;

use Database\Factories\CapacityOverrideFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * The maximum number of cakes for one specific date, replacing the default daily capacity (R20).
 *
 * @property Carbon $date
 * @property int $capacity
 */
class CapacityOverride extends Model
{
    /** @use HasFactory<CapacityOverrideFactory> */
    use HasFactory;

    protected $fillable = ['date', 'capacity'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'capacity' => 'integer',
        ];
    }

    /**
     * Stored as "Y-m-d" so that the unique index and lookups by day compare equal.
     */
    protected function date(): Attribute
    {
        return Attribute::set(fn ($value) => Carbon::parse($value)->toDateString());
    }
}

<?php

namespace App\Models;

use App\Enums\Fulfilment;
use App\Enums\Source;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * What remains of a request once purged (KTD9): no name, contact, allergy,
 * address, reference or date finer than the month (R19).
 *
 * @property string $kind
 * @property ?int $cake_type_id
 * @property Source $source
 * @property ?string $department_code
 * @property Fulfilment $fulfilment
 * @property ?int $total_cents
 * @property bool $paid
 * @property string $event_month Y-m
 */
class RequestStat extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'kind',
        'cake_type_id',
        'source',
        'department_code',
        'fulfilment',
        'total_cents',
        'paid',
        'event_month',
    ];

    protected function casts(): array
    {
        return [
            'cake_type_id' => 'integer',
            'source' => Source::class,
            'fulfilment' => Fulfilment::class,
            'total_cents' => 'integer',
            'paid' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<CakeType, $this>
     */
    public function cakeType(): BelongsTo
    {
        return $this->belongsTo(CakeType::class);
    }
}

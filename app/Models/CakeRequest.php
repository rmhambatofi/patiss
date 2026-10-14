<?php

namespace App\Models;

use App\Enums\Fulfilment;
use App\Enums\RequestStatus;
use App\Enums\Source;
use Database\Factories\CakeRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A client's request for one cake (R6, R9). Customization, allergies and
 * phone are encrypted at rest (R19), like the delivery address copied from
 * the Stripe page once the deposit is paid (U8).
 *
 * @property string $reference
 * @property string $kind 'type' or 'libre'
 * @property Carbon $event_date
 * @property Fulfilment $fulfilment
 * @property RequestStatus $status
 */
class CakeRequest extends Model
{
    /** @use HasFactory<CakeRequestFactory> */
    use HasFactory;

    public const KIND_TYPE = 'type';

    public const KIND_LIBRE = 'libre';

    /** No 0/O, 1/I/L: readable over the phone and in an e-mail subject. */
    public const REFERENCE_ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    protected $fillable = [
        'reference',
        'kind',
        'cake_type_id',
        'portions',
        'customization',
        'event_date',
        'fulfilment',
        'commune_id',
        'delivery_fee_cents',
        'allergies',
        'allergy_consent_at',
        'name',
        'email',
        'phone',
        'source',
        'out_of_lead_time',
        'status',
        'last_activity_at',
        'delivery_name',
        'delivery_line1',
        'delivery_line2',
        'delivery_postal_code',
        'delivery_city',
        'delivery_commune_mismatch',
    ];

    protected function casts(): array
    {
        return [
            'portions' => 'integer',
            'customization' => 'encrypted',
            'event_date' => 'date',
            'fulfilment' => Fulfilment::class,
            'delivery_fee_cents' => 'integer',
            'allergies' => 'encrypted',
            'allergy_consent_at' => 'datetime',
            'phone' => 'encrypted',
            'source' => Source::class,
            'out_of_lead_time' => 'boolean',
            'status' => RequestStatus::class,
            'last_activity_at' => 'datetime',
            'delivery_name' => 'encrypted',
            'delivery_line1' => 'encrypted',
            'delivery_line2' => 'encrypted',
            'delivery_postal_code' => 'encrypted',
            'delivery_city' => 'encrypted',
            'delivery_commune_mismatch' => 'boolean',
        ];
    }

    public static function generateReference(): string
    {
        do {
            $reference = '';
            for ($i = 0; $i < 6; $i++) {
                $reference .= self::REFERENCE_ALPHABET[random_int(0, strlen(self::REFERENCE_ALPHABET) - 1)];
            }
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    public function isFree(): bool
    {
        return $this->kind === self::KIND_LIBRE;
    }

    /** "Layer cake" or "Création libre". */
    public function typeLabel(): string
    {
        return $this->isFree() ? 'Création libre' : ($this->cakeType?->name ?? 'Type supprimé');
    }

    /**
     * @return BelongsTo<CakeType, $this>
     */
    public function cakeType(): BelongsTo
    {
        return $this->belongsTo(CakeType::class);
    }

    /**
     * @return BelongsTo<Commune, $this>
     */
    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * @return HasMany<RequestPhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(RequestPhoto::class)->orderBy('id');
    }

    /**
     * @return HasMany<DepositLink, $this>
     */
    public function depositLinks(): HasMany
    {
        return $this->hasMany(DepositLink::class);
    }
}

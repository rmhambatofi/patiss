<?php

namespace App\Models;

use App\Enums\Allergen;
use Database\Factories\DepositLinkFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The artisan's priced answer to a request, paid through `/acompte/{token}` (R12, R13, KTD4).
 * Only the sha256 of the token is stored. One active link per request.
 *
 * @property int $total_cents Price including delivery.
 * @property int $deposit_cents
 * @property Collection<int, Allergen> $allergens Empty only after the "none of the 14" confirmation.
 * @property string $slot
 * @property ?string $message
 * @property Carbon $expires_at
 * @property string $status
 */
class DepositLink extends Model
{
    /** @use HasFactory<DepositLinkFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_REPLACED = 'replaced';

    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'cake_request_id',
        'token_hash',
        'total_cents',
        'deposit_cents',
        'allergens',
        'slot',
        'message',
        'expires_at',
        'status',
        'stripe_session_id',
        'stripe_session_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'total_cents' => 'integer',
            'deposit_cents' => 'integer',
            'allergens' => AsEnumCollection::of(Allergen::class),
            'expires_at' => 'datetime',
            'stripe_session_expires_at' => 'datetime',
        ];
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public function balanceCents(): int
    {
        return $this->total_cents - $this->deposit_cents;
    }

    /**
     * Active and not yet expired: the client can still open it.
     *
     * @param  Builder<DepositLink>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE)->where('expires_at', '>', now());
    }

    /**
     * @return BelongsTo<CakeRequest, $this>
     */
    public function cakeRequest(): BelongsTo
    {
        return $this->belongsTo(CakeRequest::class);
    }
}

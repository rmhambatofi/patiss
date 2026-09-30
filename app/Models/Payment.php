<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A deposit paid through Stripe Checkout, recorded once per session by the
 * webhook (KTD4, KTD5): kept, or refunded in full when it could not reserve.
 *
 * @property string $stripe_session_id
 * @property ?string $payment_intent_id
 * @property int $amount_cents
 * @property string $status
 * @property ?string $refund_id
 * @property ?string $payer_email
 */
class Payment extends Model
{
    public const STATUS_PAID = 'paid';

    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'deposit_link_id',
        'cake_request_id',
        'stripe_session_id',
        'payment_intent_id',
        'amount_cents',
        'status',
        'refund_id',
        'payer_email',
    ];

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'payer_email' => 'encrypted',
        ];
    }

    /**
     * @return BelongsTo<DepositLink, $this>
     */
    public function depositLink(): BelongsTo
    {
        return $this->belongsTo(DepositLink::class);
    }

    /**
     * @return BelongsTo<CakeRequest, $this>
     */
    public function cakeRequest(): BelongsTo
    {
        return $this->belongsTo(CakeRequest::class);
    }
}

<?php

namespace App\Enums;

/**
 * Why a paid Stripe session is refunded, as told to the client (KTD4, KTD5).
 */
enum RefundReason: string
{
    /** The date filled up before the payment arrived. */
    case Full = 'full';

    /** The offer had been replaced, declined or had expired. */
    case Invalid = 'invalid';
}

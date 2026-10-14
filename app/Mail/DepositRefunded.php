<?php

namespace App\Mail;

use App\Enums\RefundReason;
use App\Models\CakeRequest;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Tells the client their deposit was refunded in full (KTD4, KTD5): the date
 * was full, or the offer had been replaced or declined. No pickup address.
 */
class DepositRefunded extends QueuedMail
{
    public function __construct(public CakeRequest $cakeRequest, public int $amountCents, public RefundReason $reason) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Demande {$this->cakeRequest->reference} : votre acompte est remboursé");
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.deposit-refunded', with: [
            'request' => $this->cakeRequest,
            'amountCents' => $this->amountCents,
            'dateFull' => $this->reason === RefundReason::Full,
        ]);
    }
}

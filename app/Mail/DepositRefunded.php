<?php

namespace App\Mail;

use App\Http\Controllers\StripeWebhookController;
use App\Models\CakeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the client their deposit was refunded in full (KTD4, KTD5): the date
 * was full, or the offer had been replaced or declined. No pickup address.
 */
class DepositRefunded extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 5;

    /** @var list<int> */
    public array $backoff = [60, 300, 1800];

    public function __construct(public CakeRequest $cakeRequest, public int $amountCents, public string $reason) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Demande {$this->cakeRequest->reference} : votre acompte est remboursé");
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.deposit-refunded', with: [
            'request' => $this->cakeRequest,
            'amountCents' => $this->amountCents,
            'dateFull' => $this->reason === StripeWebhookController::REASON_FULL,
        ]);
    }
}

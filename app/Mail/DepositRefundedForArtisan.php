<?php

namespace App\Mail;

use App\Http\Controllers\StripeWebhookController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Short notice to the artisan that a deposit was refunded automatically:
 * reference, amount and a link to the atelier only.
 */
class DepositRefundedForArtisan extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 5;

    /** @var list<int> */
    public array $backoff = [60, 300, 1800];

    public function __construct(public ?string $reference, public int $amountCents, public string $reason) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Acompte remboursé automatiquement'.($this->reference ? " : demande {$this->reference}" : ''));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.deposit-refunded-artisan', with: [
            'reference' => $this->reference,
            'amountCents' => $this->amountCents,
            'dateFull' => $this->reason === StripeWebhookController::REASON_FULL,
            'atelierUrl' => url('/atelier'),
        ]);
    }
}

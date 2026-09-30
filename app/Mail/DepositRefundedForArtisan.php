<?php

namespace App\Mail;

use App\Enums\RefundReason;
use Filament\Facades\Filament;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Short notice to the artisan that a deposit was refunded automatically:
 * reference, amount and a link to the atelier only.
 */
class DepositRefundedForArtisan extends QueuedMail
{
    public function __construct(public ?string $reference, public int $amountCents, public RefundReason $reason) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Acompte remboursé automatiquement'.($this->reference ? " : demande {$this->reference}" : ''));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.deposit-refunded-artisan', with: [
            'reference' => $this->reference,
            'amountCents' => $this->amountCents,
            'dateFull' => $this->reason === RefundReason::Full,
            'atelierUrl' => Filament::getPanel('atelier')->getUrl(),
        ]);
    }
}

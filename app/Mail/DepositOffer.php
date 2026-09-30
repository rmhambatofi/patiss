<?php

namespace App\Mail;

use App\Models\DepositLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The artisan's priced answer with the deposit link (R12, R13, R17, R21).
 * The raw token only exists in $url; the database keeps its hash.
 */
class DepositOffer extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 5;

    /** @var list<int> */
    public array $backoff = [60, 300, 1800];

    public function __construct(public DepositLink $link, public string $url) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Votre gâteau {$this->link->cakeRequest->reference} : prix et lien d’acompte");
    }

    public function content(): Content
    {
        $this->link->loadMissing('cakeRequest.cakeType');

        return new Content(markdown: 'mail.deposit-offer', with: [
            'link' => $this->link,
            'request' => $this->link->cakeRequest,
            'url' => $this->url,
        ]);
    }
}

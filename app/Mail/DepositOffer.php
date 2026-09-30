<?php

namespace App\Mail;

use App\Models\DepositLink;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * The artisan's priced answer with the deposit link (R12, R13, R17, R21).
 * The raw token only exists in $url; the database keeps its hash.
 */
class DepositOffer extends QueuedMail
{
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

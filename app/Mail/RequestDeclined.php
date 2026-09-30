<?php

namespace App\Mail;

use App\Models\CakeRequest;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * The artisan's refusal, with the message written in the atelier (F3).
 */
class RequestDeclined extends QueuedMail
{
    public function __construct(public CakeRequest $cakeRequest, public string $refusal) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Votre demande {$this->cakeRequest->reference}");
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.request-declined', with: [
            'request' => $this->cakeRequest,
            'refusal' => $this->refusal,
        ]);
    }
}

<?php

namespace App\Mail;

use App\Models\CakeRequest;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Acknowledgement to the client, recapping the request (R11). Queued so the
 * request is kept even when SMTP is down, and retried (KTD8).
 */
class RequestReceived extends QueuedMail
{
    public function __construct(public CakeRequest $cakeRequest) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Votre demande {$this->cakeRequest->reference} a bien été reçue");
    }

    public function content(): Content
    {
        $this->cakeRequest->loadMissing('cakeType', 'commune');

        return new Content(markdown: 'mail.request-received', with: ['request' => $this->cakeRequest]);
    }
}

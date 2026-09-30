<?php

namespace App\Mail;

use App\Models\CakeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The artisan's refusal, with the message written in the atelier (F3).
 */
class RequestDeclined extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 5;

    /** @var list<int> */
    public array $backoff = [60, 300, 1800];

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

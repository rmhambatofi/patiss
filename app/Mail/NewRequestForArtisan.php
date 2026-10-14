<?php

namespace App\Mail;

use App\Models\CakeRequest;
use Filament\Facades\Filament;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Notification to the artisan: type, date, commune and a link to the atelier
 * only. No allergies, phone, customization or photo, so the mailbox never
 * becomes a copy of health data (KTD8, R19).
 */
class NewRequestForArtisan extends QueuedMail
{
    public function __construct(public CakeRequest $cakeRequest) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Nouvelle demande {$this->cakeRequest->reference}");
    }

    public function content(): Content
    {
        $this->cakeRequest->loadMissing('cakeType', 'commune');

        return new Content(markdown: 'mail.new-request', with: [
            'reference' => $this->cakeRequest->reference,
            'typeLabel' => $this->cakeRequest->typeLabel(),
            'eventDate' => $this->cakeRequest->event_date,
            'outOfLeadTime' => $this->cakeRequest->out_of_lead_time,
            'communeName' => $this->cakeRequest->commune?->name,
            'atelierUrl' => Filament::getPanel('atelier')->getUrl(),
        ]);
    }
}

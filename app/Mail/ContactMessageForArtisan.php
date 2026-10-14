<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Filament\Facades\Filament;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Transmission d'un message du formulaire de contact à l'artisan. Répondre à l'e-mail
 * répond directement au client quand il a laissé une adresse.
 */
class ContactMessageForArtisan extends QueuedMail
{
    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        $email = $this->contactMessage->contactEmail();

        return new Envelope(
            subject: "Message de {$this->contactMessage->first_name} ({$this->contactMessage->occasion->label()})",
            replyTo: $email ? [new Address($email, $this->contactMessage->first_name)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.contact-message', with: [
            'firstName' => $this->contactMessage->first_name,
            'contact' => $this->contactMessage->contact,
            'occasion' => $this->contactMessage->occasion->label(),
            'body' => $this->contactMessage->message,
            'atelierUrl' => Filament::getPanel('atelier')->getUrl(),
        ]);
    }
}

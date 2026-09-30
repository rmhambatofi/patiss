<?php

namespace App\Mail;

use App\Enums\Fulfilment;
use App\Models\CakeRequest;
use App\Models\DepositLink;
use App\Settings\ShopSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Order confirmation after the paid deposit (R5, R15, R17, R21). The only
 * message that ever carries the pickup address, and only for a pickup.
 */
class OrderConfirmed extends QueuedMail
{
    public function __construct(public CakeRequest $cakeRequest, public DepositLink $link) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Commande {$this->cakeRequest->reference} confirmée : votre date est réservée");
    }

    public function content(): Content
    {
        $settings = app(ShopSettings::class);
        $this->cakeRequest->loadMissing('cakeType');

        return new Content(markdown: 'mail.order-confirmed', with: [
            'request' => $this->cakeRequest,
            'link' => $this->link,
            'pickupAddress' => $this->cakeRequest->fulfilment === Fulfilment::Pickup ? $settings->pickup_address : null,
            'reviewUrl' => $settings->google_review_url,
            'businessName' => $settings->business_name,
        ]);
    }
}

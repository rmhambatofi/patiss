<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ShopSettings extends Settings
{
    public string $business_name;

    public ?string $legal_address;

    public ?string $siret;

    public ?int $deposit_rate_percent;

    public ?string $cancellation_terms;

    public ?int $default_link_validity_days;

    public int $default_daily_capacity;

    public int $free_request_lead_days;

    public ?string $pickup_address;

    public ?string $google_review_url;

    public ?string $notification_email;

    /* Vitrine : artisan, canaux de contact et atelier (affichés sur le site). */
    public ?string $artisan_first_name;

    /** Numéro international sans espace, ex. +33600000000. */
    public ?string $whatsapp_number;

    public ?string $instagram_handle;

    public ?string $facebook_url;

    public ?string $contact_email;

    /** Adresse de l'atelier montrée sur la vitrine (différente de l'adresse de retrait, jamais publiée). */
    public ?string $workshop_address;

    public ?string $workshop_hours;

    public static function group(): string
    {
        return 'shop';
    }

    public static function encrypted(): array
    {
        return ['pickup_address'];
    }

    /**
     * Settings the public request forms need before they can open (R14, R18, R22).
     *
     * @return list<string>
     */
    public function missingRequiredSettings(): array
    {
        $required = [
            'legal_address',
            'siret',
            'deposit_rate_percent',
            'cancellation_terms',
            'default_link_validity_days',
        ];

        return array_values(array_filter(
            $required,
            fn (string $name) => blank($this->{$name}),
        ));
    }

    public function isOpenForRequests(): bool
    {
        return $this->missingRequiredSettings() === [];
    }

    /** Lien wa.me, avec un message d'accroche prérempli. */
    public function whatsappUrl(?string $text = null): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->whatsapp_number);

        if ($digits === '') {
            return null;
        }

        $text ??= 'Bonjour'.($this->artisan_first_name ? ' '.$this->artisan_first_name : '').',';

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($text);
    }

    /** "06 00 00 00 00" pour un numéro français, sinon le numéro tel quel. */
    public function whatsappDisplay(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->whatsapp_number);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '33') && strlen($digits) === 11) {
            $digits = '0'.substr($digits, 2);
        }

        return strlen($digits) === 10 ? trim(chunk_split($digits, 2, ' ')) : $this->whatsapp_number;
    }

    public function phoneHref(): ?string
    {
        $digits = preg_replace('/[^\d+]+/', '', (string) $this->whatsapp_number);

        return $digits === '' ? null : 'tel:'.$digits;
    }

    /** Ville de l'atelier, lue après le code postal de l'adresse publique (« 69001 Lyon » → « Lyon »). */
    public function workshopCity(): ?string
    {
        return preg_match('/\b\d{5}\s+([^,\n]+)/u', (string) $this->workshop_address, $m) ? trim($m[1]) : null;
    }

    public function instagramUrl(): ?string
    {
        $handle = ltrim(trim((string) $this->instagram_handle), '@');

        return $handle === '' ? null : 'https://instagram.com/'.$handle;
    }
}

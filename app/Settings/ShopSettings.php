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
}

<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('shop.business_name', 'Pâtisserie');
        $this->migrator->add('shop.legal_address', null);
        $this->migrator->add('shop.siret', null);
        $this->migrator->add('shop.deposit_rate_percent', null);
        $this->migrator->add('shop.cancellation_terms', null);
        $this->migrator->add('shop.default_link_validity_days', 3);
        $this->migrator->add('shop.default_daily_capacity', 1);
        $this->migrator->add('shop.free_request_lead_days', 14);
        $this->migrator->addEncrypted('shop.pickup_address', null);
        $this->migrator->add('shop.google_review_url', null);
        $this->migrator->add('shop.notification_email', null);
    }
};

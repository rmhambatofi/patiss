<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Réglages de la vitrine v1 : prénom de l'artisan, canaux de contact, atelier.
     * Valeurs initiales reprises de la maquette (à remplacer dans l'atelier par les vraies).
     */
    public function up(): void
    {
        $this->migrator->add('shop.artisan_first_name', 'Tahina');
        $this->migrator->add('shop.whatsapp_number', '+33600000000');
        $this->migrator->add('shop.instagram_handle', 'tahpatisserie');
        $this->migrator->add('shop.facebook_url', 'https://facebook.com/tahpatisserie');
        $this->migrator->add('shop.contact_email', 'bonjour@tahpatisserie.fr');
        $this->migrator->add('shop.workshop_address', '12 rue des Lilas, 69001 Lyon');
        $this->migrator->add('shop.workshop_hours', 'Mar – Sam : 9h – 18h');
    }

    public function down(): void
    {
        foreach (['artisan_first_name', 'whatsapp_number', 'instagram_handle', 'facebook_url', 'contact_email', 'workshop_address', 'workshop_hours'] as $name) {
            $this->migrator->delete('shop.'.$name);
        }
    }
};

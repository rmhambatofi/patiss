<x-mail::message>
# Votre demande est bien reçue

Bonjour {{ $request->name }},

Merci pour votre demande. Voici son récapitulatif.

**Référence :** {{ $request->reference }}<br>
**Gâteau :** {{ $request->typeLabel() }}<br>
**Nombre de parts :** {{ $request->portions }} parts<br>
**Date de l’événement :** {{ $request->event_date->locale('fr')->translatedFormat('l j F Y') }}<br>
@if ($request->fulfilment === \App\Enums\Fulfilment::Delivery)
**Livraison :** {{ $request->commune?->name }}@if ($request->delivery_fee_cents !== null), tarif de zone {{ \App\Support\Money::format($request->delivery_fee_cents) }}@endif
@else
**Retrait :** gratuit, à Dammartin-en-Goële (l’adresse vous sera donnée à la confirmation)
@endif

@if (filled($request->customization))
**{{ $request->isFree() ? 'Votre projet' : 'Votre personnalisation' }} :**

{{ $request->customization }}
@endif

@if ($request->out_of_lead_time)
Votre date est plus proche que le délai habituel : l’artisan vous dira s’il peut prendre votre commande.
@endif

L’artisan vous confirmera le prix exact sous 48 heures{{ $request->fulfilment === \App\Enums\Fulfilment::Delivery ? ', livraison comprise' : '' }}, avec un lien pour payer l’acompte qui réserve votre date.

Pour toute question, répondez simplement à cet e-mail en rappelant votre référence {{ $request->reference }}.

À bientôt,<br>
{{ app(\App\Settings\ShopSettings::class)->business_name }}
</x-mail::message>

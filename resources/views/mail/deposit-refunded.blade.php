<x-mail::message>
# Votre acompte est remboursé

Bonjour {{ $request->name }},

@if ($dateFull)
Votre paiement est arrivé juste après celui d’un autre client, et la date du {{ $request->event_date->locale('fr')->translatedFormat('l j F Y') }} est désormais complète. Votre date n’est donc pas réservée.
@else
Le lien que vous avez utilisé n’est plus valable : l’offre a été remplacée ou retirée entre-temps. Votre date n’est donc pas réservée.
@endif

Votre acompte de {{ \App\Support\Money::format($amountCents) }} vous est remboursé intégralement, sur la carte utilisée. Le remboursement apparaît sous quelques jours ouvrés.

**Référence :** {{ $request->reference }}

Pour trouver une autre solution, répondez simplement à cet e-mail.

{{ app(\App\Settings\ShopSettings::class)->business_name }}
</x-mail::message>

@php($euros = fn (int $cents) => \App\Models\CakeType::formatEuros($cents))
<x-mail::message>
# Votre commande est confirmée

Bonjour {{ $request->name }},

Merci, votre acompte est bien reçu : votre date est réservée.

**Référence :** {{ $request->reference }}<br>
**Gâteau :** {{ $request->typeLabel() }}, {{ $request->portions }} parts<br>
**Date :** {{ $request->event_date->locale('fr')->translatedFormat('l j F Y') }}<br>
**Créneau :** {{ $link->slot }}

@if ($request->fulfilment === \App\Enums\Fulfilment::Delivery)
**Livraison à :** {{ collect([$request->delivery_name, $request->delivery_line1, $request->delivery_line2, trim($request->delivery_postal_code.' '.$request->delivery_city)])->filter()->join(', ') }}
@elseif (filled($pickupAddress))
**Adresse de retrait :** {!! nl2br(e($pickupAddress)) !!}

Merci de garder cette adresse pour vous.
@endif

**Prix total :** {{ $euros($link->total_cents) }}<br>
**Acompte payé :** {{ $euros($link->deposit_cents) }}<br>
**Solde :** {{ $euros($link->balanceCents()) }}, à régler à la remise du gâteau

@if ($link->allergens->isEmpty())
**Allergènes :** ce gâteau ne contient aucun des 14 allergènes réglementaires.
@else
**Allergènes de votre gâteau :** {{ $link->allergens->map->label()->join(', ') }}
@endif

Pour toute question ou modification, répondez simplement à cet e-mail.

@if (filled($reviewUrl))
Après la fête, votre avis compte beaucoup :

<x-mail::button :url="$reviewUrl">
Laisser un avis Google
</x-mail::button>
@endif

À bientôt,<br>
{{ $businessName }}
</x-mail::message>

@php($euros = fn (int $cents) => \App\Support\Money::format($cents))
<x-mail::message>
# Votre gâteau : prix et acompte

Bonjour {{ $request->name }},

Voici ma proposition pour votre demande {{ $request->reference }}.

**Gâteau :** {{ $request->typeLabel() }}, {{ $request->portions }} parts<br>
**Date :** {{ $request->event_date->locale('fr')->translatedFormat('l j F Y') }}<br>
**Créneau :** {{ $link->slot }}

**Prix total{{ $request->fulfilment === \App\Enums\Fulfilment::Delivery ? ', livraison comprise' : '' }} :** {{ $euros($link->total_cents) }}<br>
**Acompte à payer en ligne :** {{ $euros($link->deposit_cents) }}<br>
**Solde :** {{ $euros($link->balanceCents()) }}, à régler à la remise du gâteau

@if ($link->allergens->isEmpty())
**Allergènes :** ce gâteau ne contient aucun des 14 allergènes réglementaires.
@else
**Allergènes de votre gâteau :** {{ $link->allergens->map->label()->join(', ') }}
@endif

@if (filled($link->message))
{!! nl2br(e($link->message)) !!}
@endif

Votre date n’est réservée qu’une fois l’acompte payé.

<x-mail::button :url="$url">
Payer l’acompte de {{ $euros($link->deposit_cents) }}
</x-mail::button>

Ce lien est valable jusqu’au {{ $link->expires_at->timezone('Europe/Paris')->locale('fr')->translatedFormat('l j F Y \à H\hi') }}. Après cette date, il ne permettra plus de payer.

Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur : {{ $url }}

À bientôt,<br>
{{ app(\App\Settings\ShopSettings::class)->business_name }}
</x-mail::message>

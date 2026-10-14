<x-mail::message>
# Nouveau message de {{ $firstName }}

**Occasion :** {{ $occasion }}<br>
**Pour répondre :** {{ $contact }}

{{ $body }}

<x-mail::button :url="$atelierUrl">
Ouvrir l’atelier
</x-mail::button>
</x-mail::message>

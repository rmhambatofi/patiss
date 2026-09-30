<x-mail::message>
# Nouvelle demande {{ $reference }}

**Gâteau :** {{ $typeLabel }}<br>
**Date :** {{ $eventDate->locale('fr')->translatedFormat('l j F Y') }}@if ($outOfLeadTime) (hors délai)@endif<br>
**Commune :** {{ $communeName ?? 'retrait' }}

Le détail de la demande est dans l’atelier.

<x-mail::button :url="$atelierUrl">
Ouvrir l’atelier
</x-mail::button>
</x-mail::message>

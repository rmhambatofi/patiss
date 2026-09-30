<x-mail::message>
# Acompte remboursé automatiquement

**Demande :** {{ $reference ?? 'inconnue (demande supprimée)' }}<br>
**Montant remboursé :** {{ \App\Support\Money::format($amountCents) }}<br>
**Motif :** {{ $dateFull ? 'la date était déjà complète' : 'le lien n’était plus valable (remplacé, refusé ou expiré)' }}

Le client a été prévenu par e-mail. Aucune confirmation ne lui a été envoyée.

<x-mail::button :url="$atelierUrl">
Ouvrir l’atelier
</x-mail::button>
</x-mail::message>

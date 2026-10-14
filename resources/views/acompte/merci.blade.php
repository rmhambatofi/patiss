@php
    [$heading, $explanation] = match ($state) {
        'reserved' => [
            'Date réservée, confirmation envoyée par e-mail',
            'Merci ! Votre acompte est bien reçu. Vous allez recevoir la confirmation de commande par e-mail, avec toutes les informations de retrait ou de livraison.',
        ],
        'full' => [
            'Date complète, paiement remboursé',
            'Un autre paiement a été confirmé juste avant le vôtre et la date est désormais complète. Votre acompte vous est remboursé intégralement, et un e-mail vous l’explique.',
        ],
        'refunded' => [
            'Offre plus valable, paiement remboursé',
            'Ce lien n’était plus valable au moment du paiement. Votre acompte vous est remboursé intégralement, et un e-mail vous l’explique.',
        ],
        default => [
            'Paiement en cours de confirmation',
            'Merci ! Stripe nous transmet votre paiement. Dès qu’il est confirmé, vous recevez un e-mail : votre date n’est réservée qu’à ce moment-là. Vous pouvez recharger cette page dans un instant.',
        ],
    };
@endphp
<x-layouts.public :title="$heading">
    <x-slot:head>
        <meta name="robots" content="noindex">
    </x-slot:head>

    <h1 class="heading-2">{{ $heading }}</h1>
    @if ($reference)
        <p class="mt-4 text-lg">Référence de votre demande : <strong class="font-mono tracking-widest">{{ $reference }}</strong></p>
    @endif
    <p class="mt-4">{{ $explanation }}</p>
    <p class="mt-2 text-sm text-muted">Pensez à vérifier vos courriers indésirables si vous ne voyez pas l’e-mail.</p>
</x-layouts.public>

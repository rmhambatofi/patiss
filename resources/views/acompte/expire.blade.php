@php
    [$heading, $explanation] = match ($reason) {
        \App\Services\DepositCheckout::PAID => [
            'Acompte déjà réglé',
            'L’acompte de cette commande est déjà payé et votre date est réservée. La confirmation vous a été envoyée par e-mail.',
        ],
        \App\Services\DepositCheckout::REPLACED => [
            'Ce lien a été remplacé',
            'Une nouvelle proposition a été faite pour cette demande, ou la demande a été close : ce lien ne permet plus de payer.',
        ],
        default => [
            'Ce lien a expiré',
            'La durée de validité de ce lien est dépassée : il ne permet plus de payer, et la date n’est pas réservée.',
        ],
    };
@endphp
<x-layouts.public :title="$heading">
    <x-slot:head>
        <meta name="robots" content="noindex">
    </x-slot:head>

    <h1 class="heading-2">{{ $heading }}</h1>
    <p class="mt-4 text-lg">Référence de votre demande : <strong class="font-mono tracking-widest">{{ $reference }}</strong></p>
    <p class="mt-4">{{ $explanation }}</p>

    @if ($reason === \App\Services\DepositCheckout::PAID)
        <p class="mt-2">Pour toute question, répondez à l’e-mail de confirmation.</p>
    @else
        <p class="mt-2">Pour obtenir un nouveau lien, répondez à l’e-mail de proposition en indiquant cette référence.</p>
    @endif
</x-layouts.public>

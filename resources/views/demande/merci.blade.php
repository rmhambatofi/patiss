<x-layouts.public title="Demande envoyée">
    <x-slot:head>
        <meta name="robots" content="noindex">
    </x-slot:head>

    <h1 class="text-2xl font-bold sm:text-3xl">Merci, votre demande est envoyée</h1>

    @if ($reference)
        <p class="mt-4 text-lg">Référence de votre demande : <strong class="font-mono tracking-widest">{{ $reference }}</strong></p>
    @endif

    <p class="mt-4">Vous allez recevoir un e-mail récapitulatif. L’artisan vous confirmera le prix exact sous 48 heures, avec un lien pour payer l’acompte qui réserve votre date.</p>
    <p class="mt-2 text-sm text-stone-600">Pensez à vérifier vos courriers indésirables si vous ne voyez pas l’e-mail.</p>

    <p class="mt-8"><a href="{{ route('collection.index') }}" class="underline">Retour à la collection</a></p>
</x-layouts.public>

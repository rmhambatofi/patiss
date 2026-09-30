<x-layouts.public
    title="Gâteaux d'anniversaire personnalisés à Dammartin-en-Goële"
    description="Gâteaux d'anniversaire personnalisés faits maison à Dammartin-en-Goële, livrés en Île-de-France et dans l'Oise.">
    <x-slot:head>
        @include('partials.structured-data')
    </x-slot:head>

    <section>
        <h1 class="text-2xl font-bold sm:text-3xl">Gâteaux d'anniversaire personnalisés</h1>
        <p class="mt-2 text-stone-700">
            Faits maison à Dammartin-en-Goële, pour toute l'Île-de-France et l'Oise.
            Choisissez un type de gâteau, puis racontez-nous votre projet.
        </p>
    </section>

    <section class="mt-8" aria-labelledby="collection-title">
        <h2 id="collection-title" class="sr-only">La collection</h2>
        @if ($types->isEmpty())
            <p class="text-stone-600">La collection arrive bientôt.</p>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($types as $type)
                    @include('partials.cake-type-card', ['type' => $type])
                @endforeach
            </div>
        @endif
    </section>

    <section class="mt-10 text-stone-700">
        <p>Zone desservie : Île-de-France et Oise, au départ de Dammartin-en-Goële.</p>
        <p class="mt-4 text-sm text-stone-500">
            Votre projet n'entre dans aucune catégorie ?
            <a href="{{ url('/demande-libre') }}" class="underline hover:text-stone-700">Demande libre</a>
        </p>
    </section>
</x-layouts.public>

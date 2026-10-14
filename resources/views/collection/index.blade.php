<x-layouts.public
    title="La collection de gâteaux"
    description="Number cakes, layer cakes, gâteaux à thème : les gâteaux d'anniversaire personnalisés d'un pâtissier de Dammartin-en-Goële."
    order-bar>
    <x-slot:head>
        @include('partials.structured-data')
    </x-slot:head>

    <div class="flex flex-col gap-3">
        <span class="eyebrow">Nos gâteaux</span>
        <h1 class="heading-2">La collection</h1>
        <p class="lead max-w-2xl">
            Chaque type de gâteau est un point de départ que l'on personnalise ensemble.
            Fabrication à Dammartin-en-Goële, retrait ou livraison en Île-de-France et dans l'Oise.
        </p>
    </div>

    @if ($types->isEmpty())
        <p class="mt-10 text-ink-2">La collection arrive bientôt.</p>
    @else
        <div class="mt-10 grid grid-cols-[repeat(auto-fill,minmax(min(100%,260px),1fr))] gap-6">
            @foreach ($types as $type)
                @include('partials.cake-type-card', ['type' => $type])
            @endforeach
        </div>
    @endif

    <p class="mt-12 text-ink-2">
        Votre projet n'entre dans aucune catégorie ?
        <a href="{{ url('/demande-libre') }}" class="link">Faites une demande libre</a>
    </p>
</x-layouts.public>

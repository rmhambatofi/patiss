<x-layouts.public
    title="La collection de gâteaux"
    description="Number cakes, layer cakes, gâteaux à thème : les gâteaux d'anniversaire personnalisés d'un pâtissier de Dammartin-en-Goële.">
    <x-slot:head>
        @include('partials.structured-data')
    </x-slot:head>

    <h1 class="text-2xl font-bold sm:text-3xl">La collection</h1>
    <p class="mt-2 text-stone-700">
        Chaque type de gâteau est un point de départ que l'on personnalise ensemble.
        Fabrication à Dammartin-en-Goële, retrait ou livraison en Île-de-France et dans l'Oise.
    </p>

    @if ($types->isEmpty())
        <p class="mt-8 text-stone-600">La collection arrive bientôt.</p>
    @else
        <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($types as $type)
                @include('partials.cake-type-card', ['type' => $type])
            @endforeach
        </div>
    @endif
</x-layouts.public>

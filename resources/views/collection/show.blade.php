<x-layouts.public
    :title="$type->name.' personnalisé à Dammartin-en-Goële'"
    :description="\Illuminate\Support\Str::limit($type->description, 155)">
    <x-slot:head>
        @include('partials.structured-data')
    </x-slot:head>

    <nav class="text-sm text-stone-500">
        <a href="{{ route('collection.index') }}" class="hover:underline">Collection</a> / {{ $type->name }}
    </nav>

    <h1 class="mt-2 text-2xl font-bold sm:text-3xl">{{ $type->name }}</h1>
    <p class="mt-1 text-lg text-stone-700">{{ $type->fromPriceLabel() }}</p>
    <p class="mt-1 text-sm text-stone-500">Le prix final dépend du nombre de parts, des ingrédients et de la personnalisation.</p>

    <section class="mt-6" aria-labelledby="realisations-title">
        <h2 id="realisations-title" class="sr-only">Réalisations</h2>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach ($type->photos as $photo)
                <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}" loading="lazy" class="aspect-square w-full rounded-lg object-cover">
            @endforeach
        </div>
    </section>

    <div class="mt-6 whitespace-pre-line text-stone-800">{{ $type->description }}</div>

    <dl class="mt-6 grid gap-4 sm:grid-cols-2">
        <div>
            <dt class="font-semibold">Formats</dt>
            <dd>{{ $type->portionFormatsLabel() }}</dd>
        </div>
        <div>
            <dt class="font-semibold">Délai minimum</dt>
            <dd>Commande au moins {{ $type->min_lead_days }} {{ $type->min_lead_days > 1 ? 'jours' : 'jour' }} à l'avance</dd>
        </div>
        @if (! empty($type->customizable_options))
            <div class="sm:col-span-2">
                <dt class="font-semibold">Ce qui se personnalise</dt>
                <dd>
                    <ul class="mt-1 flex flex-wrap gap-2">
                        @foreach ($type->customizable_options as $option)
                            <li class="rounded-full bg-stone-200 px-3 py-1 text-sm">{{ $option }}</li>
                        @endforeach
                    </ul>
                </dd>
            </div>
        @endif
    </dl>

    <section class="mt-6" aria-labelledby="allergenes-title">
        <h2 id="allergenes-title" class="font-semibold">Allergènes de la recette de base</h2>
        @if ($type->base_allergens->isEmpty())
            <p>Aucun des 14 allergènes réglementaires n’est présent dans la recette de base.</p>
        @else
            <p>{{ $type->base_allergens->map->label()->implode(', ') }}</p>
        @endif
        <p class="mt-1 text-sm text-stone-600">Attention : la personnalisation peut ajouter des allergènes. Ceux de votre gâteau vous sont indiqués avec la proposition.</p>
    </section>

    <section class="mt-6 rounded-lg border border-stone-200 bg-white p-4 text-sm" aria-labelledby="conditions-title">
        <h2 id="conditions-title" class="font-semibold">Acompte et annulation</h2>
        @if ($depositRate)
            <p class="mt-1">Acompte de {{ $depositRate }} % du prix, payé en ligne pour confirmer la commande.</p>
        @endif
        @if ($cancellationTerms)
            <p class="mt-1 whitespace-pre-line">{{ $cancellationTerms }}</p>
        @endif
        <p class="mt-1"><a href="{{ url('/cgv') }}" class="underline">Conditions générales de vente</a></p>
    </section>

    <p class="mt-8">
        <a href="{{ url('/demande/'.$type->slug) }}" class="inline-block rounded-lg bg-stone-900 px-6 py-3 font-semibold text-white hover:bg-stone-700">
            Demander ce gâteau
        </a>
    </p>
</x-layouts.public>

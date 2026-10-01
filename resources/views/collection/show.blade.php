<x-layouts.public
    :title="$type->name.' personnalisé à Dammartin-en-Goële'"
    :description="\Illuminate\Support\Str::limit($type->description, 155)">
    <x-slot:head>
        @include('partials.structured-data')
    </x-slot:head>

    <nav class="text-sm text-muted" aria-label="Fil d'Ariane">
        <a href="{{ route('collection.index') }}" class="hover:text-amethyst">Collection</a> / {{ $type->name }}
    </nav>

    <div class="mt-6 grid items-start gap-10 lg:grid-cols-[1.1fr_1fr] lg:gap-14">
        <section aria-labelledby="realisations-title">
            <h2 id="realisations-title" class="sr-only">Réalisations</h2>
            <div class="grid grid-cols-2 gap-3.5">
                @foreach ($type->photos as $photo)
                    <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}" loading="lazy"
                        @class(['w-full rounded-card object-cover shadow-card', 'col-span-2 aspect-[4/3]' => $loop->first, 'aspect-square' => ! $loop->first])>
                @endforeach
            </div>
        </section>

        <div class="flex flex-col gap-6">
            <div class="flex flex-col gap-2">
                <h1 class="heading-2">{{ $type->name }}</h1>
                <p class="text-xl font-bold text-plum">{{ $type->fromPriceLabel() }}</p>
                <p class="text-sm text-muted">Le prix final dépend du nombre de parts, des ingrédients et de la personnalisation.</p>
            </div>

            <div class="leading-relaxed whitespace-pre-line text-ink-2">{{ $type->description }}</div>

            <dl class="grid gap-5 rounded-card bg-lilac-3 p-6 sm:grid-cols-2">
                <div>
                    <dt class="text-micro font-bold tracking-badge text-ink uppercase">Formats</dt>
                    <dd class="mt-1 text-ink-2">{{ $type->portionFormatsLabel() }}</dd>
                </div>
                <div>
                    <dt class="text-micro font-bold tracking-badge text-ink uppercase">Délai minimum</dt>
                    <dd class="mt-1 text-ink-2">Commande au moins {{ $type->min_lead_days }} {{ $type->min_lead_days > 1 ? 'jours' : 'jour' }} à l'avance</dd>
                </div>
                @if (! empty($type->customizable_options))
                    <div class="sm:col-span-2">
                        <dt class="text-micro font-bold tracking-badge text-ink uppercase">Ce qui se personnalise</dt>
                        <dd>
                            <ul class="mt-2 flex flex-wrap gap-2">
                                @foreach ($type->customizable_options as $option)
                                    <li class="chip">{{ $option }}</li>
                                @endforeach
                            </ul>
                        </dd>
                    </div>
                @endif
            </dl>

            <section aria-labelledby="allergenes-title">
                <h2 id="allergenes-title" class="heading-4">Allergènes de la recette de base</h2>
                @if ($type->base_allergens->isEmpty())
                    <p class="mt-1 text-ink-2">Aucun des 14 allergènes réglementaires n’est présent dans la recette de base.</p>
                @else
                    <p class="mt-1 text-ink-2">{{ $type->base_allergens->map->label()->implode(', ') }}</p>
                @endif
                <p class="mt-1 text-sm text-muted">Attention : la personnalisation peut ajouter des allergènes. Ceux de votre gâteau vous sont indiqués avec la proposition.</p>
            </section>

            <section class="panel text-sm" aria-labelledby="conditions-title">
                <h2 id="conditions-title" class="heading-4">Acompte et annulation</h2>
                @if ($depositRate)
                    <p class="mt-2 text-ink-2">Acompte de {{ $depositRate }} % du prix, payé en ligne pour confirmer la commande.</p>
                @endif
                @if ($cancellationTerms)
                    <p class="mt-1 whitespace-pre-line text-ink-2">{{ $cancellationTerms }}</p>
                @endif
                <p class="mt-2"><a href="{{ url('/cgv') }}" class="link">Conditions générales de vente</a></p>
            </section>

            <p>
                <x-ds.button :href="url('/demande/'.$type->slug)" class="w-full sm:w-auto">Demander ce gâteau</x-ds.button>
            </p>
        </div>
    </div>
</x-layouts.public>

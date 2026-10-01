@php
    $photos = $types->flatMap->photos;
    $heroType = $types->first(fn ($type) => $type->photos->isNotEmpty());
    $heroPhoto = $heroType?->photos->first();
    $customPhotos = $photos->reject(fn ($photo) => $photo->is($heroPhoto))->take(2)->values();
    $trust = [
        ['icon' => 'handmade', 'title' => 'Fait maison', 'subtitle' => 'Chaque gâteau préparé pour vous'],
        ['icon' => 'edit', 'title' => 'Personnalisé', 'subtitle' => 'Thème, couleurs, inscription'],
        ['icon' => 'delivery', 'title' => 'Retrait ou livraison', 'subtitle' => 'Île-de-France et Oise'],
        ['icon' => 'secure', 'title' => 'Acompte sécurisé', 'subtitle' => 'Paiement en ligne par carte'],
    ];
    $steps = [
        ['title' => 'Choisissez', 'text' => 'Un gâteau de la collection comme point de départ, ou une création libre.'],
        ['title' => 'Racontez votre projet', 'text' => 'Nombre de parts, date, thème, inscription, photos d’inspiration.'],
        ['title' => 'Recevez le prix exact', 'text' => 'L’artisan vous répond par e-mail avec une proposition détaillée.'],
        ['title' => 'Réservez votre date', 'text' => 'Le paiement en ligne de l’acompte confirme la commande.'],
    ];
@endphp
<x-layouts.public
    title="Gâteaux d'anniversaire personnalisés à Dammartin-en-Goële"
    description="Gâteaux d'anniversaire personnalisés faits maison à Dammartin-en-Goële, livrés en Île-de-France et dans l'Oise."
    bare
    order-bar>
    <x-slot:head>
        @include('partials.structured-data')
    </x-slot:head>

    {{-- Hero --}}
    <section class="bg-(image:--gradient-hero) pt-16 pb-22 sm:pt-18">
        <div class="container-site grid items-center gap-12 md:grid-cols-2">
            <div class="flex max-w-xl flex-col gap-5.5">
                <x-ds.chip size="lg" class="self-start">Pâtissier · Fait maison</x-ds.chip>
                <h1 class="heading-1">
                    Gâteaux d'anniversaire personnalisés,
                    <em class="text-amethyst italic">pour vos plus beaux moments.</em>
                </h1>
                <p class="lead max-w-md">
                    Faits maison à Dammartin-en-Goële, pour toute l'Île-de-France et l'Oise.
                    Choisissez un type de gâteau, puis racontez-nous votre projet.
                </p>
                <div class="mt-1.5 flex flex-wrap gap-3">
                    <x-ds.button href="#collection">Découvrir la collection</x-ds.button>
                    <x-ds.button href="#sur-mesure" variant="secondary">Un projet sur mesure</x-ds.button>
                </div>
            </div>

            <div class="relative mx-auto aspect-square w-full max-w-[520px]">
                <div class="absolute inset-[8%] rounded-full bg-radial from-white/90 to-white/0 to-70%" aria-hidden="true"></div>
                <x-ds.image-frame :src="$heroPhoto?->url()" :alt="$heroPhoto?->alt ?? ''" :lazy="false" ratio="size-full" class="animate-floaty rounded-hero shadow-hero motion-reduce:animate-none" />
                @if ($heroType)
                    <a href="{{ route('collection.show', $heroType) }}" class="absolute bottom-5 left-3 flex flex-col gap-0.5 rounded-lg bg-white px-4 py-3 shadow-float transition hover:-translate-y-0.5">
                        <span class="text-tag font-bold tracking-tag text-amethyst uppercase">À découvrir</span>
                        <span class="font-serif text-xl font-semibold text-ink">{{ $heroType->name }}</span>
                        <span class="text-caption text-ink-2">{{ $heroType->fromPriceLabel() }}</span>
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- Réassurance --}}
    <section class="container-site relative z-10 -mt-7" aria-label="Nos engagements">
        <x-ds.trust-bar :items="$trust" />
    </section>

    {{-- La collection --}}
    <section id="collection" class="scroll-mt-20 pt-24 pb-10" aria-labelledby="collection-title">
        <div class="container-site flex flex-col gap-10">
            <x-ds.section-heading eyebrow="La collection" title="Choisissez votre point de départ" title-id="collection-title"
                link="Voir toute la collection" :link-href="url('/gateaux')" />
            @if ($types->isEmpty())
                <p class="text-ink-2">La collection arrive bientôt.</p>
            @else
                <div class="grid grid-cols-[repeat(auto-fill,minmax(min(100%,260px),1fr))] gap-6">
                    @foreach ($types as $type)
                        @include('partials.cake-type-card', ['type' => $type])
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Comment ça marche --}}
    <section class="py-14" aria-labelledby="etapes-title">
        <div class="container-site flex flex-col gap-8">
            <x-ds.heading :level="3" as="h2" id="etapes-title">Comment ça marche</x-ds.heading>
            <ol class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($steps as $step)
                    <li class="flex flex-col gap-3 rounded-card bg-white p-6 shadow-card">
                        <span class="grid size-10 place-items-center rounded-full bg-lilac text-sm font-bold text-amethyst">{{ $loop->iteration }}</span>
                        <span class="heading-4">{{ $step['title'] }}</span>
                        <p class="text-sm leading-relaxed text-muted">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Sur mesure --}}
    <section id="sur-mesure" class="container-site scroll-mt-20 py-14">
        <div class="block-gradient grid items-center gap-10 md:grid-cols-2">
            <div class="flex flex-col gap-5">
                <span class="eyebrow">Demande libre</span>
                <x-ds.heading>Un gâteau unique, pensé pour votre occasion</x-ds.heading>
                <p class="max-w-md leading-relaxed text-pretty text-ink-2">
                    Votre projet n'entre dans aucune catégorie ? Décrivez-le : l'artisan vous répond avec le prix exact et les allergènes de votre gâteau.
                </p>
                <div class="flex flex-wrap gap-2.5">
                    <x-ds.chip>Votre thème</x-ds.chip>
                    <x-ds.chip>Vos couleurs</x-ds.chip>
                    <x-ds.chip>Votre inscription</x-ds.chip>
                </div>
                <x-ds.button :href="url('/demande-libre')" class="self-start">Faire une demande libre</x-ds.button>
            </div>
            <div class="grid grid-cols-2 gap-3.5" aria-hidden="true">
                @foreach ([0, 1] as $i)
                    <x-ds.image-frame :src="$customPhotos->get($i)?->url()" ratio="aspect-[3/4]" :tone="$i === 0 ? 'rose' : 'lilac'"
                        :class="$i === 0 ? 'rounded-xl translate-y-5' : 'rounded-xl'" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Zone desservie --}}
    <section class="mt-6 bg-lilac-3 px-4 py-16 sm:px-6" aria-labelledby="zone-title">
        <div class="mx-auto flex max-w-3xl flex-col items-center gap-4 text-center">
            <x-ds.heading :level="3" as="h2" id="zone-title">Retrait gratuit ou livraison</x-ds.heading>
            <p class="leading-relaxed text-ink-2">
                Zone desservie : Île-de-France et Oise, au départ de Dammartin-en-Goële.
                Le retrait est gratuit ; la livraison est calculée selon la distance.
            </p>
            <x-ds.button :href="url('/livraison')" variant="secondary" class="mt-2">Voir les zones et tarifs</x-ds.button>
        </div>
    </section>
</x-layouts.public>

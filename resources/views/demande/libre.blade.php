<x-layouts.public
    title="Demande de création libre"
    description="Un projet de gâteau qui n’entre dans aucun type de la collection ? Décrivez-le à l’artisan.">
    <x-slot:head>
        <meta name="robots" content="noindex">
        @vite('resources/js/demande.js')
    </x-slot:head>

    <h1 class="heading-2">Demande de création libre</h1>
    <p class="mt-1 text-ink-2">
        Votre projet n’entre dans aucun <a href="{{ route('collection.index') }}" class="link">type de la collection</a> ?
        Décrivez-le : l’artisan vous répond avec le prix exact et les allergènes de votre gâteau.
    </p>

    @if ($errors->any())
        <p class="alert-error mt-4" role="alert">Certains champs sont à corriger ci-dessous.</p>
    @endif

    <form method="POST" action="{{ route('request.free.store') }}" enctype="multipart/form-data" class="mt-6 flex max-w-2xl flex-col gap-6" data-demande-form novalidate>
        @csrf

        <div>
            <label for="description" class="field-label">Description de votre projet</label>
            <textarea id="description" name="description" rows="5" required class="field-input"
                placeholder="Forme, thème, couleurs, parfums, inscription…">{{ old('description') }}</textarea>
            @include('demande.partials.error', ['field' => 'description'])
        </div>

        <div>
            <label for="portions" class="field-label">Nombre de parts</label>
            <input type="number" id="portions" name="portions" min="1" max="1000" step="1" required inputmode="numeric"
                value="{{ old('portions') }}"
                class="field-input sm:w-40">
            @include('demande.partials.error', ['field' => 'portions'])
        </div>

        @include('demande.partials.fields')
    </form>
</x-layouts.public>

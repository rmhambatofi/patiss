<x-layouts.public
    :title="'Demande : '.$type->name"
    description="Demandez votre gâteau personnalisé : l’artisan vous répond avec le prix exact.">
    <x-slot:head>
        <meta name="robots" content="noindex">
        @vite('resources/js/demande.js')
    </x-slot:head>

    <nav class="text-sm text-muted">
        <a href="{{ route('collection.index') }}" class="hover:text-amethyst">Collection</a>
        / <a href="{{ route('collection.show', $type) }}" class="hover:text-amethyst">{{ $type->name }}</a>
        / Demande
    </nav>

    <h1 class="heading-2 mt-2">Votre demande : {{ $type->name }}</h1>
    <p class="mt-1 text-ink-2">{{ \Illuminate\Support\Str::ucfirst($type->fromPriceLabel()) }}. L’artisan vous répond avec le prix exact de votre gâteau.</p>

    @if ($errors->any())
        <p class="alert-error mt-4" role="alert">Certains champs sont à corriger ci-dessous.</p>
    @endif

    <form method="POST" action="{{ route('request.type.store', $type) }}" enctype="multipart/form-data" class="mt-6 flex max-w-2xl flex-col gap-6" data-demande-form novalidate>
        @csrf

        <div>
            <label for="portions" class="field-label">Nombre de parts</label>
            <select id="portions" name="portions" required class="field-input">
                @foreach ($type->portion_formats as $format)
                    <option value="{{ (int) $format }}" @selected((int) old('portions') === (int) $format)>{{ (int) $format }} parts</option>
                @endforeach
            </select>
            @include('demande.partials.error', ['field' => 'portions'])
        </div>

        <div>
            <label for="customization" class="field-label">Votre personnalisation</label>
            @if (! empty($type->customizable_options))
                <p class="text-sm text-muted">Ce qui se personnalise : {{ implode(', ', $type->customizable_options) }}.</p>
            @endif
            <textarea id="customization" name="customization" rows="4" class="field-input"
                placeholder="Thème, couleurs, inscription, parfum…">{{ old('customization') }}</textarea>
            @include('demande.partials.error', ['field' => 'customization'])
        </div>

        @include('demande.partials.fields')
    </form>
</x-layouts.public>

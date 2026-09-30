<x-layouts.public
    :title="'Demande : '.$type->name"
    description="Demandez votre gâteau personnalisé : l’artisan vous répond avec le prix exact.">
    <x-slot:head>
        <meta name="robots" content="noindex">
        @vite('resources/js/demande.js')
    </x-slot:head>

    <nav class="text-sm text-stone-500">
        <a href="{{ route('collection.index') }}" class="hover:underline">Collection</a>
        / <a href="{{ route('collection.show', $type) }}" class="hover:underline">{{ $type->name }}</a>
        / Demande
    </nav>

    <h1 class="mt-2 text-2xl font-bold sm:text-3xl">Votre demande : {{ $type->name }}</h1>
    <p class="mt-1 text-stone-700">{{ \Illuminate\Support\Str::ucfirst($type->fromPriceLabel()) }}. L’artisan vous répond avec le prix exact de votre gâteau.</p>

    @if ($errors->any())
        <p class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">Certains champs sont à corriger ci-dessous.</p>
    @endif

    <form method="POST" action="{{ route('request.type.store', $type) }}" enctype="multipart/form-data" class="mt-6 flex max-w-2xl flex-col gap-6" data-demande-form novalidate>
        @csrf

        <div>
            <label for="portions" class="font-semibold">Nombre de parts</label>
            <select id="portions" name="portions" required class="mt-1 block w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-base">
                @foreach ($type->portion_formats as $format)
                    <option value="{{ (int) $format }}" @selected((int) old('portions') === (int) $format)>{{ (int) $format }} parts</option>
                @endforeach
            </select>
            @include('demande.partials.error', ['field' => 'portions'])
        </div>

        <div>
            <label for="customization" class="font-semibold">Votre personnalisation</label>
            @if (! empty($type->customizable_options))
                <p class="text-sm text-stone-600">Ce qui se personnalise : {{ implode(', ', $type->customizable_options) }}.</p>
            @endif
            <textarea id="customization" name="customization" rows="4" class="mt-1 block w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-base"
                placeholder="Thème, couleurs, inscription, parfum…">{{ old('customization') }}</textarea>
            @include('demande.partials.error', ['field' => 'customization'])
        </div>

        @include('demande.partials.fields')
    </form>
</x-layouts.public>

@php($photo = $type->photos->first())
<a href="{{ route('collection.show', $type) }}" class="group block overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm hover:shadow-md">
    @if ($photo)
        <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}" loading="lazy" class="aspect-square w-full object-cover">
    @endif
    <div class="p-4">
        <h3 class="text-lg font-semibold group-hover:underline">{{ $type->name }}</h3>
        <p class="text-sm text-stone-600">{{ $type->fromPriceLabel() }}</p>
    </div>
</a>

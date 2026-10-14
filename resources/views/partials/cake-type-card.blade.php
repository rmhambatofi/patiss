@php($photo = $type->photos->first())
<x-ds.product-card
    :href="route('collection.show', $type)"
    :name="$type->name"
    :description="filled($type->description) ? \Illuminate\Support\Str::limit($type->description, 110) : null"
    :price="$type->fromPriceLabel()"
    :image="$photo?->url()"
    :alt="$photo?->alt ?? ''" />

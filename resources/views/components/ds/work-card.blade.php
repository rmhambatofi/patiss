{{-- DS WorkCard : pièce de la galerie. Bouton (zoom) qui ouvre la visionneuse x-ds.lightbox ; photo carrée, catégorie en étiquette, nom 24px et description. --}}
@props(['creation'])
<button type="button" data-lightbox data-category="{{ $creation->category->value }}" data-category-label="{{ $creation->category->label() }}"
    data-image="{{ $creation->imageUrl() }}" data-name="{{ $creation->name }}" data-description="{{ $creation->description }}"
    {{ $attributes->class('card flex cursor-zoom-in flex-col p-0 text-left transition-colors duration-200 hover:bg-surface focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ink') }}>
    <x-ds.photo :src="$creation->imageUrl()" :alt="$creation->name" ratio="aspect-square" :label="$creation->category->label()" label-position="top-left" rounded="rounded-none" class="w-full" />
    <span class="flex flex-col gap-1.5 px-6 pt-5 pb-6">
        <span class="heading-4">{{ $creation->name }}</span>
        @if ($creation->description)
            <span class="text-body-sm leading-body-sm text-muted">{{ $creation->description }}</span>
        @endif
    </span>
</button>

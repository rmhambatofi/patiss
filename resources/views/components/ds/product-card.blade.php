{{-- DS ProductCard : carte blanche rayon 24, photo carrée, titre serif, prix prune et bouton. Toute la carte est un lien. --}}
@props(['href', 'name', 'description' => null, 'price' => null, 'image' => null, 'alt' => '', 'tag' => null, 'cta' => 'Découvrir'])
<a href="{{ $href }}" {{ $attributes->class('group flex flex-col overflow-hidden rounded-card bg-white shadow-card transition-[transform,box-shadow] duration-250 hover:-translate-y-1.5 hover:shadow-card-hover') }}>
    <x-ds.image-frame :src="$image" :alt="$alt" tone="rose">
        @if ($tag)
            <x-ds.badge class="absolute top-3.5 left-3.5">{{ $tag }}</x-ds.badge>
        @endif
    </x-ds.image-frame>
    <div class="flex flex-1 flex-col gap-2 px-5 pt-5 pb-5.5">
        <h3 class="heading-4">{{ $name }}</h3>
        @if (filled($description))
            <p class="flex-1 text-sm leading-normal text-muted">{{ $description }}</p>
        @endif
        <div class="mt-2 flex items-center justify-between gap-3">
            <span class="text-[17px] font-bold text-plum">{{ $price }}</span>
            <span class="btn btn-sm btn-primary px-5 shadow-none" aria-hidden="true">{{ $cta }}</span>
        </div>
    </div>
</a>

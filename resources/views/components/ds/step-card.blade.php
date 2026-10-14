{{-- DS StepCard : étape de l'atelier. Carte blanche à marge 8px, photo 4/5 rayon 16, numéro, titre et texte. --}}
@props(['num', 'title', 'text' => null, 'image' => null])
<div {{ $attributes->class('flex h-full flex-col gap-4 rounded-md bg-white p-2') }}>
    <div class="overflow-hidden rounded-sm bg-hairline-soft aspect-[4/5]">
        @if ($image)
            <img src="{{ $image }}" alt="" loading="lazy" class="size-full object-cover">
        @endif
    </div>
    <div class="flex flex-col gap-1 px-4 pt-1 pb-4">
        <span class="text-label leading-label font-semibold text-muted">{{ $num }}</span>
        <span class="title">{{ $title }}</span>
        @if ($text)
            <span class="text-body-sm leading-body-sm text-muted">{{ $text }}</span>
        @endif
    </div>
</div>

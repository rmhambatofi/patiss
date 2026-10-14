{{-- DS ImageFrame : cadre arrondi, photo en cover, hachures lilas ou rose sans photo. Le slot se superpose (badges, étiquettes). Rayon via la classe (rounded-card par défaut côté appelant). --}}
@props(['src' => null, 'alt' => '', 'ratio' => 'aspect-square', 'tone' => 'lilac', 'shadow' => false, 'lazy' => true])
<div {{ $attributes->class(['relative overflow-hidden', $ratio, $tone === 'rose' ? 'placeholder-rose' : 'placeholder-lilac', 'shadow-image' => $shadow]) }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" @if ($lazy) loading="lazy" @endif class="absolute inset-0 size-full object-cover">
    @endif
    {{ $slot }}
</div>

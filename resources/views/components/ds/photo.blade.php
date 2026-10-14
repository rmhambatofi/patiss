{{-- DS Photo : tuile 4/5 rayon 24 fond gris, photo en cover, étiquette translucide optionnelle. label-position : bottom-left|bottom-center|top-left ; grayscale pour les portraits. --}}
@props(['src' => null, 'alt' => '', 'ratio' => 'aspect-[4/5]', 'rounded' => 'rounded-md', 'label' => null, 'labelPosition' => 'bottom-left', 'grayscale' => false, 'lazy' => true])
<div {{ $attributes->class(['relative overflow-hidden bg-surface', $ratio, $rounded]) }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" @if ($lazy) loading="lazy" @endif @class(['absolute inset-0 size-full object-cover', 'grayscale' => $grayscale])>
    @endif
    @if ($label)
        <span @class([
            'badge-overlay absolute',
            'bottom-3 left-3' => $labelPosition === 'bottom-left',
            'bottom-4 left-1/2 -translate-x-1/2' => $labelPosition === 'bottom-center',
            'top-3 left-3' => $labelPosition === 'top-left',
        ])>{{ $label }}</span>
    @endif
    {{ $slot }}
</div>

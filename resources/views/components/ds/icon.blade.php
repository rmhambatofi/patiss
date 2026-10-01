{{-- DS Icon : icônes filaires maison (trait 1.8, 24×24, currentColor). Nouveau besoin : reprendre le tracé Lucide équivalent et l'ajouter ici. --}}
@props(['name', 'size' => 22])
@php
    $paths = [
        // Jeu du design system
        'cart' => ['M6 7h12l-1 12H7L6 7z', 'M9 7a3 3 0 0 1 6 0'],
        'menu' => ['M4 7h16M4 12h16M4 17h16'],
        'close' => ['M6 6l12 12M18 6L6 18'],
        'handmade' => ['M12 3c-3 4-7 6-7 10a7 7 0 0 0 14 0c0-4-4-6-7-10z'],
        'quality' => ['M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z', 'M9 12l2 2 4-4'],
        'delivery' => ['M3 7h11v9H3z', 'M14 10h4l3 3v3h-7z', 'M7 19a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z', 'M18 19a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z'],
        'secure' => ['M5 10h14v10H5z', 'M8 10V7a4 4 0 0 1 8 0v3'],
        'arrow' => ['M5 12h14', 'M13 6l6 6-6 6'],
        // Ajouts du site (style Lucide)
        'edit' => ['M12 20h9', 'M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z'],
    ];
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes }}>
    @foreach ($paths[$name] ?? [] as $d)
        <path d="{{ $d }}"/>
    @endforeach
</svg>

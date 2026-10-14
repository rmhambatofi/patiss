{{-- DS Icon : icônes filaires (trait 1.8, 24×24, currentColor). Nouveau besoin : reprendre le tracé Lucide équivalent et l'ajouter ici, jamais d'icône pleine ni d'emoji. --}}
@props(['name', 'size' => 22])
@php
    $paths = [
        'menu' => ['M4 7h16M4 12h16M4 17h16'],
        'close' => ['M6 6l12 12M18 6L6 18'],
        'check' => ['M5 12l5 5L20 7'],
        'arrow' => ['M5 12h14', 'M13 6l6 6-6 6'],
        'handmade' => ['M12 3c-3 4-7 6-7 10a7 7 0 0 0 14 0c0-4-4-6-7-10z'],
        'quality' => ['M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z', 'M9 12l2 2 4-4'],
        'whatsapp' => ['M4 20l1.3-3.9A8 8 0 1 1 8.2 19L4 20z', 'M9.5 9.5c0 3 2 5 5 5l1-1.5-2-1-1 1a4 4 0 0 1-1.5-1.5l1-1-1-2z'],
        'instagram' => ['M8 3h8a5 5 0 0 1 5 5v8a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5V8a5 5 0 0 1 5-5z', 'M12 8a4 4 0 1 0 0 8 4 4 0 1 0 0-8z', 'M17.5 6.5h.01'],
        'facebook' => ['M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v5h4v-5h3l1-4h-4V8z'],
        'mail' => ['M6 5h12a3 3 0 0 1 3 3v8a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V8a3 3 0 0 1 3-3z', 'M3 8l9 6 9-6'],
        'globe' => ['M12 3a9 9 0 1 0 0 18 9 9 0 1 0 0-18z', 'M3 12h18', 'M12 3c3 3 3 15 0 18', 'M12 3c-3 3-3 15 0 18'],
        // Pages de commande (hors vitrine v1)
        'cart' => ['M6 7h12l-1 12H7L6 7z', 'M9 7a3 3 0 0 1 6 0'],
        'delivery' => ['M3 7h11v9H3z', 'M14 10h4l3 3v3h-7z', 'M7 19a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z', 'M18 19a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z'],
        'secure' => ['M5 10h14v10H5z', 'M8 10V7a4 4 0 0 1 8 0v3'],
        'edit' => ['M12 20h9', 'M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z'],
    ];
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes }}>
    @foreach ($paths[$name] ?? [] as $d)
        <path d="{{ $d }}"/>
    @endforeach
</svg>

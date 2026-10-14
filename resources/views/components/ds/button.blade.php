{{-- DS Button : pilule 48px, verbe court (Voir mes créations, Me contacter, Envoyer). variant : primary|secondary|soft|dark|white ; size : sm (44px)|md|lg (48px). --}}
@props(['variant' => 'primary', 'size' => 'lg', 'href' => null, 'type' => 'button', 'full' => false])
@php
    $classes = 'btn btn-'.$size.' btn-'.$variant.($full ? ' w-full' : '');
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif

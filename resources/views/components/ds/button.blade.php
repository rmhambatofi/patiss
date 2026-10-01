{{-- DS Button : pilule, verbe court (Commander, Découvrir la carte…). variant : primary|dark|secondary|ghost|white ; size : sm|md|lg. --}}
@props(['variant' => 'primary', 'size' => 'lg', 'href' => null, 'type' => 'button', 'full' => false])
@php
    $classes = 'btn btn-'.$size.' btn-'.$variant.($full ? ' w-full' : '');
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif

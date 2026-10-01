{{-- DS Heading : serif Cormorant. level 1 = h1 hero, 2 = titre de page ou de section, 3 = h2 secondaire, 4 = titre de carte (24px). `as` force la balise. --}}
@props(['level' => 2, 'as' => null])
@php
    $tag = $as ?? 'h'.min($level, 3);
@endphp
<{{ $tag }} {{ $attributes->class('heading-'.$level) }}>{{ $slot }}</{{ $tag }}>

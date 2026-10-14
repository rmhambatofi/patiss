{{-- DS Badge : petite étiquette en capitales posée sur une image (Nouveau, Best-seller…). tone : accent|dark. --}}
@props(['tone' => 'accent'])
<span {{ $attributes->class(['badge', 'text-plum' => $tone === 'dark']) }}>{{ $slot }}</span>

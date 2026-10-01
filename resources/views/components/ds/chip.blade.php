{{-- DS Chip : pastille blanche. size md = option (« Vos couleurs ») ; lg = étiquette en capitales (« Pâtissier · Fait maison »). --}}
@props(['size' => 'md'])
<span {{ $attributes->class($size === 'lg' ? 'chip-lg' : 'chip') }}>{{ $slot }}</span>

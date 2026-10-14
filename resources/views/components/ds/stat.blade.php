{{-- DS Stat : chiffre clé sur fond gris (« 12 / ans de métier »). --}}
@props(['value', 'label'])
<div {{ $attributes->class('stat') }}>
    <span class="text-h3 leading-heading font-bold text-ink">{{ $value }}</span>
    <span class="text-body-sm text-muted">{{ $label }}</span>
</div>

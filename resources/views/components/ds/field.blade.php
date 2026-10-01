{{--
    DS TextField : libellé 13px gras, contrôle (classe .field-input : pilule 48px, textarea rayon 20), aide et erreur.
    Le contrôle passe par le slot pour garder ses attributs (data-*, autocomplete…) :
    <x-ds.field label="E-mail" for="email" name="email"><input type="email" id="email" name="email" class="field-input"></x-ds.field>
--}}
@props(['label' => null, 'for' => null, 'name' => null, 'hint' => null])
<div {{ $attributes }}>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif class="field-label">{{ $label }}</label>
    @endif
    {{ $slot }}
    @if ($hint)
        <p class="field-hint mt-1.5">{{ $hint }}</p>
    @endif
    @if ($name)
        @include('demande.partials.error', ['field' => $name])
    @endif
</div>

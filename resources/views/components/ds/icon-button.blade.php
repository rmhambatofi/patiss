{{-- DS IconButton : bouton rond 44px avec une icône de x-ds.icon. `label` est obligatoire (accessibilité). --}}
@props(['icon', 'label', 'href' => null, 'badge' => null])
@if ($href)
    <a href="{{ $href }}" aria-label="{{ $label }}" {{ $attributes->class('icon-btn') }}>
@else
    <button type="button" aria-label="{{ $label }}" {{ $attributes->class('icon-btn') }}>
@endif
    <x-ds.icon :name="$icon" :size="20" />
    @if ($badge)
        <span class="absolute -top-1 -right-1 grid h-5 min-w-5 place-items-center rounded-full bg-amethyst px-1.5 text-tag font-bold text-white">{{ $badge }}</span>
    @endif
@if ($href)
    </a>
@else
    </button>
@endif

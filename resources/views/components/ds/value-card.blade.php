{{-- DS ValueCard : carte blanche à filet gris, squircle d'icône, titre 20px et texte discret. --}}
@props(['icon', 'title', 'text' => null])
<div {{ $attributes->class('flex flex-col gap-4 rounded-md border border-hairline-soft bg-white p-6 max-sm:flex-row max-sm:items-center') }}>
    <span class="squircle size-12 bg-surface text-ink"><x-ds.icon :name="$icon" /></span>
    <div class="flex flex-col gap-1">
        <span class="title">{{ $title }}</span>
        @if ($text)
            <span class="text-body-sm leading-body-sm text-muted">{{ $text }}</span>
        @endif
    </div>
</div>

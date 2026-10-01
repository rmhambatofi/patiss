{{-- DS TrustItem : icône dans un carré lilas 46px rayon 14, titre et sous-titre. --}}
@props(['icon', 'title', 'subtitle' => null])
<div {{ $attributes->class('flex items-center gap-3.5') }}>
    <div class="grid size-11.5 shrink-0 place-items-center rounded-md bg-icon text-amethyst"><x-ds.icon :name="$icon" /></div>
    <div class="flex flex-col gap-0.5">
        <span class="text-body-sm font-bold text-ink">{{ $title }}</span>
        @if ($subtitle)
            <span class="text-caption text-muted">{{ $subtitle }}</span>
        @endif
    </div>
</div>

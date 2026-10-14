{{-- DS ReviewCard : citation, initiale dans une pastille grise, nom et occasion. --}}
@props(['text', 'name', 'occasion' => null])
<figure {{ $attributes->class('flex flex-col gap-4 rounded-sm border border-hairline-soft bg-white p-6') }}>
    <blockquote class="text-body leading-body text-pretty text-ink">{{ $text }}</blockquote>
    <figcaption class="mt-auto flex items-center gap-3">
        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-surface text-body-sm font-semibold text-ink" aria-hidden="true">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</span>
        <span class="flex flex-col">
            <span class="text-body leading-body font-semibold text-ink">{{ $name }}</span>
            @if ($occasion)
                <span class="text-body-sm text-muted">{{ $occasion }}</span>
            @endif
        </span>
    </figcaption>
</figure>

{{-- DS SectionHeading : eyebrow + titre, lien « … → » optionnel à droite. `title-id` sert à aria-labelledby. --}}
@props(['eyebrow' => null, 'title', 'link' => null, 'linkHref' => '#', 'align' => 'left', 'level' => 2, 'as' => null, 'titleId' => null])
<div {{ $attributes->class(['flex flex-wrap items-end gap-5', 'justify-center text-center' => $align === 'center', 'justify-between' => $align !== 'center']) }}>
    <div @class(['flex flex-col gap-2.5', 'items-center' => $align === 'center', 'items-start' => $align !== 'center'])>
        @if ($eyebrow)
            <span class="eyebrow">{{ $eyebrow }}</span>
        @endif
        <x-ds.heading :level="$level" :as="$as" :id="$titleId">{{ $title }}</x-ds.heading>
    </div>
    @if ($link)
        <a href="{{ $linkHref }}" class="link-arrow">{{ $link }} →</a>
    @endif
</div>

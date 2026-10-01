{{-- DS CategoryTile : tuile photo 4/5, étiquette blanche « Nom → » en bas, zoom 1.03 au survol. --}}
@props(['href', 'name', 'image' => null])
<a href="{{ $href }}" {{ $attributes->class('placeholder-lilac relative flex aspect-[4/5] items-end overflow-hidden rounded-xl p-3 shadow-card transition-transform duration-250 hover:scale-[1.03]') }}>
    @if ($image)
        <img src="{{ $image }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover">
    @endif
    <span class="relative inline-flex max-w-full items-center gap-1.5 rounded-2xl bg-white/92 px-3.5 py-2.25 text-sm leading-[1.2] font-bold text-plum">{{ $name }}<span class="text-amethyst">→</span></span>
</a>

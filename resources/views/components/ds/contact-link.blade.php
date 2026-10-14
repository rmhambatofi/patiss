{{-- DS ContactLink : ligne de contact (WhatsApp, Instagram, Facebook, E-mail) : squircle rouge, libellé, détail et flèche ↗. --}}
@props(['href', 'icon', 'label', 'detail' => null, 'external' => false])
<a href="{{ $href }}" @if ($external) target="_blank" rel="noreferrer" @endif {{ $attributes->class('contact-link') }}>
    <span class="squircle size-10 bg-primary text-white"><x-ds.icon :name="$icon" :size="20" /></span>
    <span class="flex min-w-0 flex-col">
        <span class="text-body font-semibold">{{ $label }}</span>
        @if ($detail)
            <span class="truncate text-body-sm text-muted">{{ $detail }}</span>
        @endif
    </span>
    <span class="ml-auto text-muted" aria-hidden="true">↗</span>
</a>

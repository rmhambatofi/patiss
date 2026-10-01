{{-- DS TrustBar : bandeau blanc de réassurance. items : [['icon' => 'handmade', 'title' => '…', 'subtitle' => '…'], …] --}}
@props(['items'])
<ul {{ $attributes->class('grid gap-5 rounded-card bg-white p-6 shadow-bar sm:grid-cols-2 lg:grid-cols-4') }}>
    @foreach ($items as $item)
        <li><x-ds.trust-item :icon="$item['icon']" :title="$item['title']" :subtitle="$item['subtitle'] ?? null" /></li>
    @endforeach
</ul>

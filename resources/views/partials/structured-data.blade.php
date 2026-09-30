{{-- KTD12: Bakery with areaServed only. Never an address: the artisan works from home (R4). --}}
@inject('shop', 'App\Settings\ShopSettings')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Bakery',
    'name' => $shop->business_name,
    'url' => url('/'),
    'areaServed' => [
        ['@type' => 'City', 'name' => 'Dammartin-en-Goële'],
        ['@type' => 'AdministrativeArea', 'name' => 'Île-de-France'],
        ['@type' => 'AdministrativeArea', 'name' => 'Oise'],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>

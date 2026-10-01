@props(['title' => null, 'description' => null, 'bare' => false, 'orderBar' => false])
@inject('shop', 'App\Settings\ShopSettings')
@php
    $nav = [
        ['label' => 'La collection', 'url' => url('/gateaux')],
        ['label' => 'Création libre', 'url' => url('/demande-libre')],
        ['label' => 'Retrait et livraison', 'url' => url('/livraison')],
    ];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · '.$shop->business_name : $shop->business_name }}</title>
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif
    {{ $head ?? '' }}
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-clip bg-cream font-sans text-ink antialiased">
    <header class="sticky top-0 z-50 border-b border-line-soft bg-(--glass-bg) backdrop-blur-[14px]">
        <div class="container-site flex items-center gap-5 py-3.5">
            <a href="{{ url('/') }}" class="font-serif text-logo leading-none font-bold tracking-tight text-plum">{{ $shop->business_name }}</a>

            <nav aria-label="Navigation principale" class="ml-auto hidden md:block">
                <ul class="flex gap-6 text-sm font-semibold">
                    @foreach ($nav as $item)
                        <li><a href="{{ $item['url'] }}" class="text-nav hover:text-amethyst">{{ $item['label'] }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <x-ds.button :href="url('/gateaux')" size="sm" class="max-md:hidden">Commander</x-ds.button>

            <details class="group ml-auto md:hidden">
                <summary class="icon-btn list-none [&::-webkit-details-marker]:hidden" aria-label="Menu">
                    <x-ds.icon name="menu" :size="20" class="group-open:hidden" />
                    <x-ds.icon name="close" :size="20" class="hidden group-open:block" />
                </summary>
                <nav aria-label="Navigation mobile" class="absolute inset-x-0 top-full border-t border-b border-line-soft bg-cream px-6 pt-2 pb-4">
                    <ul class="flex flex-col">
                        @foreach ($nav as $item)
                            <li><a href="{{ $item['url'] }}" class="block border-b border-lilac-2 py-3.5 text-[17px] font-semibold text-ink last:border-0">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            </details>
        </div>
    </header>

    @if ($bare)
        <main>
            {{ $slot }}
        </main>
    @else
        <main class="container-site py-10 sm:py-14">
            {{ $slot }}
        </main>
    @endif

    <footer class="bg-lilac-2 px-4 pt-16 pb-8 sm:px-6 {{ $orderBar ? 'max-md:pb-28' : '' }}">
        <div class="mx-auto flex max-w-site flex-col gap-12">
            <div class="grid gap-9 sm:grid-cols-2 lg:grid-cols-3">
                <div class="flex flex-col gap-3.5">
                    <span class="font-serif text-[30px] font-bold text-plum">{{ $shop->business_name }}</span>
                    <p class="max-w-xs text-sm leading-relaxed text-ink-2">Gâteaux d'anniversaire personnalisés, faits maison. Retrait ou livraison sur commande.</p>
                </div>
                <div class="flex flex-col gap-2.5 text-sm text-ink-2">
                    <span class="text-caption font-bold tracking-badge text-ink uppercase">L'atelier</span>
                    <span>Pâtissier à Dammartin-en-Goële</span>
                    <span>Livraison en Île-de-France et dans l'Oise</span>
                    <a href="{{ url('/livraison') }}" class="font-semibold text-amethyst hover:text-plum">Zones et tarifs de livraison</a>
                </div>
                <div class="flex flex-col gap-2.5 text-sm">
                    <span class="text-caption font-bold tracking-badge text-ink uppercase">Commander</span>
                    <a href="{{ url('/gateaux') }}" class="font-semibold text-amethyst hover:text-plum">La collection</a>
                    <a href="{{ url('/demande-libre') }}" class="font-semibold text-amethyst hover:text-plum">Demande de création libre</a>
                </div>
            </div>
            <div class="flex flex-wrap justify-between gap-4 border-t border-line pt-5 text-xs text-muted">
                <span>© {{ now()->year }} {{ $shop->business_name }}</span>
                <div class="flex flex-wrap gap-4.5">
                    <a href="{{ url('/mentions-legales') }}" class="text-muted hover:text-amethyst">Mentions légales</a>
                    <a href="{{ url('/cgv') }}" class="text-muted hover:text-amethyst">CGV</a>
                    <a href="{{ url('/confidentialite') }}" class="text-muted hover:text-amethyst">Confidentialité</a>
                </div>
            </div>
        </div>
    </footer>

    @if ($orderBar)
        <div class="fixed inset-x-0 bottom-0 z-40 flex items-center gap-2.5 border-t border-line-soft bg-cream/92 px-4 pt-2.5 pb-[calc(10px+env(safe-area-inset-bottom))] backdrop-blur-[14px] md:hidden">
            <x-ds.button :href="url('/gateaux')" class="flex-1 text-base shadow-button-lg">Commander</x-ds.button>
            <x-ds.button :href="url('/demande-libre')" variant="secondary" class="px-4.5 text-sm">Sur mesure</x-ds.button>
        </div>
    @endif
</body>
</html>

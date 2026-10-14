{{--
    Layout public (vitrine) : pilule de navigation flottante, pied de page blanc, barre de contact mobile.
    `bare` : sections pleine largeur (la page gère ses conteneurs) ; `contact-bar` : barre WhatsApp / Écrire fixe sur mobile ;
    `photo-credit` : mention des photos d'illustration dans le pied de page.
--}}
@props(['title' => null, 'description' => null, 'bare' => false, 'contactBar' => false, 'orderBar' => false, 'photoCredit' => null])
@inject('shop', 'App\Settings\ShopSettings')
@php
    $home = route('home');
    $nav = [
        ['label' => 'Créations', 'url' => $home.'#creations'],
        ['label' => 'L’artisan', 'url' => $home.'#artisan'],
        ['label' => 'L’atelier', 'url' => $home.'#atelier'],
        ['label' => 'Contact', 'url' => $home.'#contact'],
    ];
    $whatsapp = $shop->whatsappUrl();
    $showBar = $contactBar || $orderBar;
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
<body class="min-h-screen overflow-x-clip bg-canvas pt-4 font-sans text-ink antialiased">
    <header class="sticky top-4 z-50 flex justify-center px-4">
        <div class="relative flex w-full max-w-nav flex-col">
            <div class="nav-pill">
                <a href="{{ $home }}" class="text-logo leading-none font-bold text-ink hover:text-ink">{{ $shop->business_name }}</a>

                <nav aria-label="Navigation principale" class="hidden md:ml-auto md:block">
                    <ul class="flex gap-6 text-body font-semibold">
                        @foreach ($nav as $item)
                            <li><a href="{{ $item['url'] }}" class="text-ink hover:text-muted">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </nav>

                <div class="ml-auto flex items-center gap-2 md:ml-0">
                    <details data-mobile-menu class="group md:hidden">
                        <summary class="icon-btn list-none [&::-webkit-details-marker]:hidden" aria-label="Menu">
                            <x-ds.icon name="menu" :size="20" class="group-open:hidden" />
                            <x-ds.icon name="close" :size="20" class="hidden group-open:block" />
                        </summary>
                        <nav aria-label="Navigation mobile" class="absolute inset-x-0 top-full mt-2 flex animate-fadein flex-col gap-2 rounded-md bg-surface p-2 shadow-pill">
                            @foreach ($nav as $item)
                                <a href="{{ $item['url'] }}" class="flex items-center justify-between rounded-sm bg-white px-4 py-3.5 text-body font-semibold text-ink">
                                    <span>{{ $item['label'] }}</span><span class="text-muted" aria-hidden="true">↗</span>
                                </a>
                            @endforeach
                        </nav>
                    </details>
                    <x-ds.button :href="$home.'#contact'" size="sm"><span class="md:hidden">Contact</span><span class="max-md:hidden">Me contacter</span></x-ds.button>
                </div>
            </div>
        </div>
    </header>

    @if ($bare)
        <main>
            {{ $slot }}
        </main>
    @else
        <main class="container-site py-12 sm:py-16">
            {{ $slot }}
        </main>
    @endif

    <footer class="border-t border-hairline bg-canvas px-6 pt-12 pb-6 {{ $showBar ? 'max-md:pb-28' : '' }}">
        <div class="mx-auto flex max-w-site flex-col gap-8">
            <div class="grid grid-cols-[repeat(auto-fit,minmax(min(100%,200px),1fr))] gap-6 text-body-sm leading-body-sm">
                <div class="flex flex-col gap-3">
                    <span class="text-body leading-tight font-semibold text-ink">{{ $shop->business_name }}</span>
                    <a href="{{ $home }}#creations" class="text-ink hover:underline">Mes créations</a>
                    <a href="{{ $home }}#artisan" class="text-ink hover:underline">L’artisan</a>
                    <a href="{{ $home }}#atelier" class="text-ink hover:underline">L’atelier</a>
                </div>
                <div class="flex flex-col gap-3">
                    <span class="text-body leading-tight font-semibold text-ink">Contact</span>
                    @if ($whatsapp)
                        <a href="{{ $whatsapp }}" target="_blank" rel="noreferrer" class="text-ink hover:underline">WhatsApp</a>
                    @endif
                    @if ($shop->contact_email)
                        <a href="mailto:{{ $shop->contact_email }}" class="text-ink hover:underline">{{ $shop->contact_email }}</a>
                    @endif
                    @if ($shop->phoneHref())
                        <a href="{{ $shop->phoneHref() }}" class="text-ink hover:underline">{{ $shop->whatsappDisplay() }}</a>
                    @endif
                </div>
                <div class="flex flex-col gap-3">
                    <span class="text-body leading-tight font-semibold text-ink">Suivre</span>
                    @if ($shop->instagramUrl())
                        <a href="{{ $shop->instagramUrl() }}" target="_blank" rel="noreferrer" class="text-ink hover:underline">Instagram</a>
                    @endif
                    @if ($shop->facebook_url)
                        <a href="{{ $shop->facebook_url }}" target="_blank" rel="noreferrer" class="text-ink hover:underline">Facebook</a>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap justify-between gap-4 border-t border-hairline pt-6 text-caption leading-caption text-muted">
                <div class="flex flex-wrap gap-2">
                    <span>© {{ now()->year }} {{ $shop->business_name }}</span>
                    <span aria-hidden="true">·</span>
                    <a href="{{ url('/mentions-legales') }}" class="text-muted hover:underline">Mentions légales</a>
                    <span aria-hidden="true">·</span>
                    <a href="{{ url('/confidentialite') }}" class="text-muted hover:underline">Confidentialité</a>
                    @if ($photoCredit)
                        <span aria-hidden="true">·</span>
                        <span>{{ $photoCredit }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-4 font-semibold text-ink">
                    <span class="inline-flex items-center gap-1.5"><x-ds.icon name="globe" :size="14" />Français (FR)</span>
                    <span>€ EUR</span>
                </div>
            </div>
        </div>
    </footer>

    @if ($showBar)
        <div class="fixed inset-x-4 bottom-[calc(12px+env(safe-area-inset-bottom))] z-40 flex items-center gap-1.5 rounded-full bg-surface p-1.5 shadow-pill-strong md:hidden">
            @if ($whatsapp)
                <x-ds.button :href="$whatsapp" target="_blank" rel="noreferrer" class="flex-1"><x-ds.icon name="whatsapp" :size="18" />WhatsApp</x-ds.button>
                <x-ds.button :href="$home.'#contact'" variant="white" class="px-4.5">Écrire</x-ds.button>
            @else
                <x-ds.button :href="$home.'#contact'" class="flex-1">Écrire</x-ds.button>
            @endif
        </div>
    @endif
</body>
</html>

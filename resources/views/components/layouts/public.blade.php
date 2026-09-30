@props(['title' => null, 'description' => null])
@inject('shop', 'App\Settings\ShopSettings')
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <nav class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-4 px-4 py-4">
            <a href="{{ url('/') }}" class="text-lg font-semibold">{{ $shop->business_name }}</a>
            <ul class="flex flex-wrap gap-4 text-sm">
                <li><a href="{{ url('/gateaux') }}" class="hover:underline">Collection</a></li>
                <li><a href="{{ url('/livraison') }}" class="hover:underline">Livraison</a></li>
            </ul>
        </nav>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        {{ $slot }}
    </main>

    <footer class="border-t border-stone-200 bg-white">
        <div class="mx-auto flex max-w-5xl flex-wrap gap-4 px-4 py-6 text-sm text-stone-600">
            <span>Pâtissier à Dammartin-en-Goële, livraison en Île-de-France et dans l'Oise</span>
            <a href="{{ url('/mentions-legales') }}" class="hover:underline">Mentions légales</a>
            <a href="{{ url('/cgv') }}" class="hover:underline">CGV</a>
            <a href="{{ url('/confidentialite') }}" class="hover:underline">Confidentialité</a>
        </div>
    </footer>
</body>
</html>

@inject('shop', 'App\Settings\ShopSettings')
@php($missing = 'à compléter')
<x-layouts.public
    title="Mentions légales"
    :description="'Éditeur, hébergeur et contact du site '.$shop->business_name.'.'"
>
    <div class="max-w-2xl space-y-8">
        <h1 class="heading-2">Mentions légales</h1>

        <section class="space-y-2">
            <h2 class="heading-4">Éditeur du site</h2>
            <ul class="space-y-1">
                <li><span class="font-medium">Nom commercial :</span> {{ $shop->business_name }}</li>
                <li><span class="font-medium">SIRET :</span> {{ $shop->siret ?: $missing }}</li>
                <li><span class="font-medium">Adresse professionnelle :</span> {{ $shop->legal_address ?: $missing }}</li>
                <li>
                    <span class="font-medium">Contact :</span>
                    @if ($shop->notification_email)
                        <a href="mailto:{{ $shop->notification_email }}" class="link">{{ $shop->notification_email }}</a>
                    @else
                        {{ $missing }}
                    @endif
                </li>
            </ul>
            <p>Directeur de la publication : l'exploitant de {{ $shop->business_name }}.</p>
        </section>

        <section class="space-y-2">
            <h2 class="heading-4">Hébergeur</h2>
            <p>
                o2switch SAS<br>
                Chemin des Pardiaux, 63000 Clermont-Ferrand, France<br>
                Téléphone : 04 44 44 60 40<br>
                <a href="https://www.o2switch.fr" class="link">www.o2switch.fr</a>
            </p>
        </section>

        <section class="space-y-2">
            <h2 class="heading-4">Propriété intellectuelle</h2>
            <p>Les textes et les photos de réalisations publiés sur ce site appartiennent à {{ $shop->business_name }}. Toute reproduction sans autorisation écrite est interdite.</p>
        </section>

        <section class="space-y-2">
            <h2 class="heading-4">Données personnelles et conditions de vente</h2>
            <p>Le traitement des données transmises par le site est décrit dans la <a href="{{ route('legal.privacy') }}" class="link">politique de confidentialité</a>. Les conditions de commande figurent dans les <a href="{{ route('legal.terms') }}" class="link">conditions générales de vente</a>.</p>
        </section>
    </div>
</x-layouts.public>

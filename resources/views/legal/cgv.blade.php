@inject('shop', 'App\Settings\ShopSettings')
@php($missing = 'à compléter')
<x-layouts.public
    title="Conditions générales de vente"
    :description="'Demande, prix, acompte, annulation, livraison et allergènes : les conditions de commande de '.$shop->business_name.'.'"
>
    <div class="max-w-2xl space-y-8">
        <h1 class="text-3xl font-semibold">Conditions générales de vente</h1>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">1. Le vendeur</h2>
            <p>
                {{ $shop->business_name }}<br>
                SIRET : {{ $shop->siret ?: $missing }}<br>
                Adresse professionnelle : {{ $shop->legal_address ?: $missing }}<br>
                Contact : {{ $shop->notification_email ?: $missing }}
            </p>
            <p>Ces conditions s'appliquent à toute commande de gâteau passée par ce site. Vous les acceptez en envoyant une demande.</p>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">2. De la demande à la commande</h2>
            <ol class="list-decimal space-y-1 pl-6">
                <li>Vous envoyez une demande depuis le site : type de gâteau ou création libre, nombre de parts, personnalisation, date de l'événement, retrait ou livraison.</li>
                <li>Une demande n'engage ni vous ni nous. Le prix « à partir de » affiché sur les pages de gâteaux est indicatif.</li>
                <li>Si nous pouvons réaliser votre gâteau, vous recevez par e-mail une proposition avec le prix exact, les frais de livraison éventuels, le montant de l'acompte et un lien de paiement. Ce lien reste valable pendant la durée indiquée dans la proposition ; passé ce délai, il ne fonctionne plus.</li>
                <li>La commande est conclue, et la date réservée, uniquement lorsque l'acompte est payé. Tant que l'acompte n'est pas payé, la date n'est pas bloquée et peut être prise par une autre commande.</li>
                <li>Vous recevez alors une confirmation par e-mail, avec le récapitulatif de la commande et les informations de retrait ou de livraison.</li>
            </ol>
            <p>Si la date n'est plus disponible au moment où votre paiement arrive (par exemple si deux paiements arrivent en même temps pour la dernière place), l'acompte vous est remboursé intégralement et vous en êtes prévenu par e-mail.</p>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">3. Prix et paiement</h2>
            <p>Les prix sont indiqués en euros, toutes taxes comprises.</p>
            <p>Acompte : <strong>{{ filled($shop->deposit_rate_percent) ? $shop->deposit_rate_percent.' %' : $missing }}</strong> du prix total de la commande, payé en ligne par carte bancaire (Apple Pay et Google Pay acceptés) par l'intermédiaire de notre prestataire de paiement Stripe.</p>
            <p>Le solde se paie à la remise du gâteau, au retrait ou à la livraison.</p>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">4. Annulation</h2>
            @if (filled($shop->cancellation_terms))
                <div class="whitespace-pre-line">{{ $shop->cancellation_terms }}</div>
            @else
                <p>Conditions d'annulation : {{ $missing }}</p>
            @endif
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">5. Droit de rétractation</h2>
            <p>Chaque gâteau est fabriqué pour vous, selon vos choix de format, de parfums et de décor. Selon l'article L221-28 3° du Code de la consommation, le droit de rétractation ne s'applique pas à la fourniture de biens confectionnés selon les spécifications du consommateur ou nettement personnalisés. Il ne s'applique pas non plus aux biens susceptibles de se détériorer ou de se périmer rapidement (article L221-28 4°).</p>
            <p>Une fois l'acompte payé, ce sont donc les conditions d'annulation ci-dessus qui s'appliquent.</p>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">6. Retrait et livraison</h2>
            <p>Le retrait à Dammartin-en-Goële est gratuit. L'adresse de retrait est communiquée dans l'e-mail de confirmation, après le paiement de l'acompte.</p>
            <p>La livraison est possible en Île-de-France et dans l'Oise. Ses frais dépendent de la distance ; les zones et les tarifs sont détaillés sur la page <a href="{{ route('delivery.index') }}" class="underline">Retrait et livraison</a>. Les frais de livraison de votre commande figurent dans la proposition.</p>
            <p>Dès sa remise, le gâteau doit être conservé au réfrigérateur.</p>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">7. Allergènes</h2>
            <p>Chaque page de gâteau indique les allergènes de sa recette de base parmi les 14 allergènes majeurs du règlement européen INCO (règlement UE n° 1169/2011). La personnalisation peut en ajouter.</p>
            <p>La proposition et la confirmation de commande indiquent les allergènes du gâteau réellement commandé. Signalez dans votre demande toute allergie ou intolérance de vos invités.</p>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">8. Réclamations</h2>
            <p>Pour toute réclamation, écrivez à {{ $shop->notification_email ?: $missing }}. Vos données sont traitées selon la <a href="{{ route('legal.privacy') }}" class="underline">politique de confidentialité</a>.</p>
        </section>
    </div>
</x-layouts.public>

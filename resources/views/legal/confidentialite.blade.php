@inject('shop', 'App\Settings\ShopSettings')
@php($missing = 'à compléter')
<x-layouts.public
    title="Politique de confidentialité"
    :description="'Quelles données '.$shop->business_name.' collecte, pourquoi, pendant combien de temps, et comment exercer vos droits.'"
>
    <div class="max-w-2xl space-y-8">
        <h1 class="text-3xl font-semibold">Politique de confidentialité</h1>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">Responsable du traitement</h2>
            <p>
                {{ $shop->business_name }}<br>
                SIRET : {{ $shop->siret ?: $missing }}<br>
                Adresse professionnelle : {{ $shop->legal_address ?: $missing }}<br>
                Contact : {{ $shop->notification_email ?: $missing }}
            </p>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">Données collectées</h2>
            <p>Lorsque vous envoyez une demande de gâteau, nous recevons :</p>
            <ul class="list-disc space-y-1 pl-6">
                <li>vos coordonnées : nom, adresse e-mail, numéro de téléphone ;</li>
                <li>les détails de l'événement : date, nombre de parts, personnalisation souhaitée, commune de livraison ou choix du retrait ;</li>
                <li>les allergies ou intolérances des invités, si vous en déclarez. Ce sont des données de santé : nous ne les traitons qu'avec votre consentement explicite, donné par une case à cocher dans le formulaire ;</li>
                <li>les photos que vous joignez (gâteau à reproduire ou inspiration). Leurs métadonnées (appareil, position GPS) ne sont pas conservées.</li>
            </ul>
            <p>Le site n'utilise pas de cookie publicitaire ni d'outil de suivi tiers.</p>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">Pourquoi et sur quelle base</h2>
            <ul class="list-disc space-y-1 pl-6">
                <li>Étudier votre demande, vous envoyer une proposition, préparer et remettre le gâteau : exécution de mesures précontractuelles et du contrat.</li>
                <li>Tenir compte des allergies déclarées : votre consentement explicite. Vous pouvez le retirer à tout moment en nous écrivant ; nous ne pourrons alors plus garantir l'adaptation du gâteau.</li>
                <li>Conserver les pièces comptables (factures, paiements) : obligation légale.</li>
            </ul>
            <p>Vos données servent uniquement à traiter votre commande. Elles ne sont ni vendues, ni utilisées pour de la publicité.</p>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">Qui y a accès</h2>
            <ul class="list-disc space-y-1 pl-6">
                <li>L'artisan et, le cas échéant, le proche qui assure la maintenance du site.</li>
                <li>o2switch (Clermont-Ferrand, France), hébergeur du site : les données sont stockées sur ses serveurs, et les e-mails du site partent par son serveur d'envoi.</li>
                <li>Stripe, prestataire de paiement de l'acompte : il reçoit votre adresse e-mail et, pour une livraison, l'adresse de livraison, ainsi que les données de votre carte, que nous ne voyons jamais. Stripe conserve ces données selon sa propre politique de confidentialité (<a href="https://stripe.com/fr/privacy" class="underline">stripe.com/fr/privacy</a>).</li>
            </ul>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">Durée de conservation</h2>
            <ul class="list-disc space-y-1 pl-6">
                <li>Les demandes, les allergies et les photos sont supprimées du site 3 mois après la date de l'événement.</li>
                <li>Pour une demande refusée ou jamais payée, elles sont supprimées 3 mois après la dernière activité sur la demande.</li>
                <li>Seules des statistiques anonymes (nombre de demandes, provenance) sont conservées au-delà.</li>
                <li>Les pièces comptables sont conservées pendant la durée imposée par la loi.</li>
            </ul>
            <p>Ces durées portent sur les données du site. Les données transmises à Stripe suivent sa propre politique.</p>
        </section>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">Vos droits</h2>
            <p>Vous pouvez demander l'accès à vos données, leur rectification, leur effacement, leur portabilité, la limitation de leur traitement, vous opposer à leur traitement ou retirer votre consentement pour les allergies. Écrivez à {{ $shop->notification_email ?: $missing }} ; nous répondons dans un délai d'un mois.</p>
            <p>Si vous estimez que vos droits ne sont pas respectés, vous pouvez adresser une réclamation à la CNIL (<a href="https://www.cnil.fr/fr/plaintes" class="underline">www.cnil.fr/fr/plaintes</a>).</p>
        </section>
    </div>
</x-layouts.public>

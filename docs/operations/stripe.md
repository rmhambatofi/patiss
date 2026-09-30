# Stripe (paiement de l'acompte)

Ce guide s'adresse au proche qui maintient le site, avec l'artisan pour la création du compte. Le site crée lui-même les sessions de paiement Stripe à partir de ses liens d'acompte ; seul le webhook de Stripe marque un acompte comme payé et réserve la date.

## 1. Compte au nom de l'artisan

- Le compte Stripe est ouvert **par l'artisan, à son nom et avec son SIRET** : c'est lui qui encaisse, et Stripe vérifie l'identité du titulaire et le compte bancaire de versement. Le proche peut être invité ensuite comme utilisateur (**Paramètres → Équipe**, rôle « Développeur »).
- **Profil public** (**Paramètres → Informations publiques**, ce qui apparaît sur le relevé et la page de paiement) : nom commercial, adresse e-mail de contact du site, et comme adresse **l'adresse professionnelle choisie pour les textes légaux**, la même que dans **Réglages → Adresse professionnelle**. Jamais l'adresse du domicile si l'artisan a choisi une autre adresse pour les textes légaux.
- **Libellé sur le relevé bancaire** : le nom commercial, pour que le client reconnaisse le débit.

## 2. Moyens de paiement

Le site ne propose que la carte bancaire (Apple Pay et Google Pay passent par la carte). Dans **Paramètres → Moyens de paiement**, laisser la carte active ; les autres moyens (virement, prélèvement, paiement différé) peuvent rester désactivés, le site ne les demande pas. Pour Apple Pay, enregistrer le domaine du site dans **Moyens de paiement → Domaines**.

## 3. Clés dans `.env`

Stripe fournit deux jeux de clés : **test** (`pk_test_…`, `sk_test_…`) et **production** (`pk_live_…`, `sk_live_…`). Le bouton « Mode test » en haut du tableau de bord bascule de l'un à l'autre.

```dotenv
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

- Commencer en **mode test** sur le serveur, dérouler la répétition complète, puis seulement passer aux clés de production.
- La clé secrète et le secret du webhook ne se partagent jamais (ni par e-mail, ni dans le dépôt Git).
- Après chaque modification : `php artisan optimize`.

## 4. Webhook

Dans **Développeurs → Webhooks → Ajouter une destination** (à faire une fois en mode test, une fois en mode production) :

| Réglage | Valeur |
|---|---|
| URL du point de terminaison | `https://<domaine>/stripe/webhook` |
| Événements | `checkout.session.completed` et `checkout.session.expired` |

Copier ensuite le **secret de signature** (`whsec_…`) de cette destination dans `STRIPE_WEBHOOK_SECRET`. Les secrets de test et de production sont différents : changer les deux clés et le secret du webhook en même temps.

### Vérifier les livraisons

Dans la destination du webhook, l'onglet des événements liste chaque envoi avec son code de réponse :

- **200** : le site a bien traité l'événement.
- **400** : signature refusée, en général un `STRIPE_WEBHOOK_SECRET` faux ou d'un autre mode.
- **Échec / délai dépassé** : le site n'a pas répondu. Stripe réessaie pendant plusieurs jours ; corriger puis cliquer sur **Renvoyer** pour les événements en échec.

Contrôler cette page après chaque premier paiement réel, puis de temps en temps. Un événement `checkout.session.completed` en échec, c'est un acompte payé sans date réservée.

## 5. Tester en local avec la CLI Stripe

1. Installer la CLI Stripe (<https://docs.stripe.com/stripe-cli>) et lancer `stripe login`.
2. Démarrer le site en local, puis relayer les webhooks :

   ```bash
   stripe listen --forward-to http://localhost:8000/stripe/webhook
   ```

3. La commande affiche un secret `whsec_…` : le mettre dans `STRIPE_WEBHOOK_SECRET` du `.env` local (avec les clés de test), puis relancer le serveur.
4. Payer un lien d'acompte avec la carte de test `4242 4242 4242 4242`, une date d'expiration future et n'importe quel code. La commande `stripe listen` affiche l'événement et la réponse du site.
5. Pour tester un paiement refusé : carte `4000 0000 0000 0002`.

## 6. Passage en production

1. Vérifier que le compte est activé (identité et compte bancaire validés par Stripe).
2. Créer la destination du webhook en mode production (section 4).
3. Remplacer dans `.env` les trois valeurs par celles de production, puis `php artisan optimize`.
4. Faire un vrai paiement d'un petit montant sur une demande de test, vérifier la réservation et l'e-mail de confirmation, puis le rembourser depuis le tableau de bord Stripe.

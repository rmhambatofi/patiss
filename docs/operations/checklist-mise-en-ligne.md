# Check-list de mise en ligne

À dérouler par le proche, dans l'ordre, une première fois entièrement en **mode test Stripe** sur o2switch. Chaque case se coche quand elle est vérifiée, pas quand elle est « normalement faite ».

## 1. Serveur

- [ ] Installation terminée selon [deploiement-o2switch.md](deploiement-o2switch.md) : PHP et extensions, base de données, racine du domaine sur `public/`, HTTPS actif, `/up` répond 200.
- [ ] Limites PHP vérifiées sur le serveur (`upload_max_filesize` 10M, `post_max_size` 60M, `max_file_uploads` 5, `memory_limit` 512M, `max_execution_time` 60).
- [ ] Cron « chaque minute » en place ; `php artisan schedule:list` affiche la tâche de file d'attente.
- [ ] `.env` : `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` = adresse exacte de cet environnement en `https://`, `QUEUE_CONNECTION=database`, `SESSION_SECURE_COOKIE=true`, `SESSION_DOMAIN` vide (et `SESSION_COOKIE` propre à la recette).
- [ ] `php artisan storage:link` lancé ; `http://` et `www.` redirigent vers `https://tahpatisserie.fr`.
- [ ] E-mails configurés selon [emails-dns.md](emails-dns.md) : boîte du domaine, SPF, DKIM et DMARC valides, `MAIL_*` renseignés.
- [ ] Stripe configuré en **mode test** selon [stripe.md](stripe.md) : clés `pk_test`/`sk_test`, webhook `https://<domaine>/stripe/webhook` avec `checkout.session.completed` et `checkout.session.expired`, `STRIPE_WEBHOOK_SECRET` de test.

## 2. Comptes de l'atelier

- [ ] Un compte par personne (artisan, proche) : `php artisan make:filament-user`, avec une adresse e-mail que la personne lit.
- [ ] Chaque personne se connecte sur `https://<domaine>/atelier`, active la double authentification par code e-mail et reçoit bien le code.

## 3. Réglages de l'atelier

Dans **Atelier → Réglages** :

- [ ] Nom commercial.
- [ ] **Adresse professionnelle** : celle choisie par l'artisan pour les textes légaux (jamais le domicile s'il a choisi une autre adresse).
- [ ] **SIRET**.
- [ ] E-mail de notification des demandes (adresse du domaine : elle est publiée comme contact dans les pages légales).
- [ ] Taux d'acompte, conditions d'annulation, durée de validité des liens.
- [ ] Nombre maximal de gâteaux par jour, délai de la demande libre.
- [ ] Adresse de retrait (elle n'apparaît que dans l'e-mail de confirmation après paiement).
- [ ] Le bandeau « Les formulaires de demande sont fermés » a disparu.
- [ ] Relire `/mentions-legales`, `/cgv` et `/confidentialite` : plus aucun « à compléter », aucune adresse de retrait. Les textes ont été relus par l'artisan (et idéalement par un professionnel du droit).

## 4. Contenu

- [ ] `php artisan communes:import` lancé ; la recherche de commune du formulaire trouve Dammartin-en-Goële et une commune de l'Oise.
- [ ] Paliers de livraison saisis (**Atelier → Livraison**) ; `/livraison` les affiche.
- [ ] Capacité (**Atelier → Capacité par date**) : exceptions saisies pour les jours déjà pris ou fermés.
- [ ] Types de gâteaux saisis (**Atelier → Collection**) : description, photos, prix « à partir de », formats, **allergènes de la recette de base**. Chaque type visible a au moins une photo.
- [ ] Une photo publiée, téléchargée depuis le site, ne contient ni EXIF ni GPS (par exemple avec `exiftool photo.jpg`).
- [ ] Dans l'atelier, l'aperçu d'une photo existante se charge, et la console du navigateur (F12) n'affiche aucune erreur « CORS » ni « Mixed Content ».

## 5. Répétition complète F1 → F3 (mode test Stripe)

- [ ] **Demande** : depuis un navigateur privé, envoyer une demande sur un type avec livraison, allergies (case de consentement cochée) et une photo, avec une adresse Gmail. L'accusé arrive dans la boîte de réception, pas en spam.
- [ ] L'artisan reçoit la notification ; elle ne contient ni allergies ni photos, seulement le lien vers l'atelier.
- [ ] **Proposition** : dans l'atelier, envoyer une proposition (prix, allergènes du gâteau commandé, durée du lien). L'e-mail de proposition arrive.
- [ ] **Acompte** : payer avec la carte `4242 4242 4242 4242`. Dans Stripe (**Développeurs → Webhooks**), l'événement `checkout.session.completed` a reçu une réponse 200.
- [ ] **Confirmation** : la demande passe à « Réservée », la date est comptée dans la capacité, l'e-mail de confirmation arrive avec l'adresse de retrait, les allergènes, le solde à payer à la remise et le lien d'avis Google.
- [ ] Refaire une demande avec une adresse Outlook/Hotmail : accusé et confirmation hors spam.
- [ ] **Lien expiré** : envoyer une proposition avec la durée la plus courte, attendre l'expiration, ouvrir le lien : le paiement est refusé.
- [ ] **Demande libre** : une demande hors catégorie passe aussi jusqu'à la proposition.
- [ ] Supprimer les demandes de test, ou les laisser partir avec la purge automatique.

## 6. Passage en production

- [ ] Compte Stripe activé ; destination du webhook créée en mode production.
- [ ] `.env` : `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` remplacés par les valeurs de production, puis `php artisan optimize`.
- [ ] Un vrai paiement d'un petit montant aboutit à une réservation et à la confirmation ; il est ensuite remboursé depuis Stripe.

## 7. Lancement

- [ ] Fiche Google vérifiée, sans adresse, avec le lien du site (voir [fiche-google.md](fiche-google.md)).
- [ ] Lien d'avis Google collé dans **Réglages → Lien d'avis Google**.
- [ ] Lien d'avis envoyé aux clients existants.
- [ ] Dans les jours qui suivent : contrôler les livraisons du webhook dans Stripe et `php artisan queue:failed` après les premières demandes réelles.

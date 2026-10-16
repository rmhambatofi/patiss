# Déploiement sur o2switch

Ce guide s'adresse au proche qui maintient le site. L'application est une application Laravel standard ; o2switch l'héberge sur un compte cPanel mutualisé.

Deux environnements vivent sur le même compte, chacun dans son propre dossier, avec sa propre base et son propre `.env` :

| Environnement | Adresse | Dossier du code | Rôle |
|---|---|---|---|
| Recette (staging) | `https://stg.tahpatisserie.fr` | `~/patiss-stg` | vérifier une mise à jour avant la production, montrer le site à l'artisan |
| Production | `https://tahpatisserie.fr` | `~/patiss` | le site public |

Le périmètre de la première version est la **vitrine** : créations, artisan, atelier, contact. La commande en ligne (demandes, acompte Stripe) n'est pas liée depuis le site ; ses réglages peuvent rester vides.

## Première installation

Les étapes valent pour les deux environnements. Commencer par la recette, et remplacer `~/patiss-stg` par `~/patiss` pour la production.

1. **SSH.** Dans cPanel, ouvrir « Autorisation SSH » et autoriser l'adresse IP depuis laquelle on se connecte. o2switch refuse les connexions SSH des IP non autorisées.
2. **Version de PHP.** Dans « Sélectionner une version de PHP », choisir PHP 8.4 ou 8.5 et activer les extensions `gd` (ou `imagick`), `intl`, `sodium`, `pdo_mysql`, `mbstring`, `fileinfo`, `exif`, `zip`. La version de PHP est commune à tout le compte.
3. **Limites PHP** (onglet Options du même écran), nécessaires aux photos envoyées depuis l'atelier (10 Mo par photo) :

   | Réglage | Valeur minimale |
   |---|---|
   | `upload_max_filesize` | 10M |
   | `post_max_size` | 60M |
   | `max_file_uploads` | 5 |
   | `memory_limit` | 512M |
   | `max_execution_time` | 60 |

   Vérifier ensuite sur le serveur : `php -i | grep -E 'upload_max_filesize|post_max_size|max_file_uploads|memory_limit|max_execution_time'`.
4. **Base de données.** Dans « Bases de données MySQL », créer une base et un utilisateur **par environnement** (par exemple `xxx_patiss_stg` et `xxx_patiss`), et donner tous les droits à l'utilisateur sur sa base. La recette ne doit jamais pointer sur la base de production.
5. **Code.** Dans « Git Version Control », cloner le dépôt dans un dossier hors de tout document root : `~/patiss-stg` pour la recette, `~/patiss` pour la production. À défaut, cloner en SSH :

   ```bash
   git clone <adresse-du-depot> ~/patiss-stg
   ```

6. **Domaine et document root.**
   - **Recette.** Dans « Domaines », créer le sous-domaine `stg.tahpatisserie.fr`. cPanel lui attribue le document root `~/stg.tahpatisserie.fr`. Le site doit être servi depuis le dossier `public` du code, et uniquement lui : remplacer ce dossier par un lien vers `~/patiss-stg/public` :

     ```bash
     rmdir ~/stg.tahpatisserie.fr
     ln -s ~/patiss-stg/public ~/stg.tahpatisserie.fr
     ```

     Si cPanel permet de modifier le document root du sous-domaine, on peut aussi le faire pointer directement sur `~/patiss-stg/public` et se passer du lien.
   - **Production.** Dans « Domaines », faire pointer la racine du domaine `tahpatisserie.fr` vers `~/patiss/public`. Si `www.tahpatisserie.fr` existe, le rediriger vers `https://tahpatisserie.fr` (« Redirections » de cPanel, redirection permanente 301).
   - Dans les deux cas, activer **« Forcer la redirection HTTPS »** et vérifier que le certificat AutoSSL couvre bien `stg.tahpatisserie.fr` (cPanel → « SSL/TLS Status »). Aucun autre dossier du projet ne doit être accessible depuis le web.
   - **Protéger la recette.** Dans « Confidentialité du répertoire », protéger `~/patiss-stg/public` par un identifiant et un mot de passe : la recette n'est pas destinée au public et ne doit pas être indexée par Google.
7. **Environnement.** Dans le dossier du code, copier `.env.example` en `.env`, puis renseigner :
   - `APP_ENV=staging` sur la recette, `APP_ENV=production` en production ; `APP_DEBUG=false` dans les deux cas.
   - `APP_URL` : l'adresse exacte de **cet** environnement, en `https://` et sans `/` final : `https://stg.tahpatisserie.fr` sur la recette, `https://tahpatisserie.fr` en production. Les liens des e-mails en dépendent : une recette avec l'`APP_URL` de la production envoie ses lecteurs vers la production.
   - `SESSION_SECURE_COOKIE=true` ; laisser `SESSION_DOMAIN` vide (cookie limité à l'hôte exact) et, sur la recette, `SESSION_COOKIE=tahpatisserie_stg_session` pour que ses cookies ne se mélangent jamais avec ceux de la production (sinon erreurs « 419 Page expirée »).
   - `DB_CONNECTION=mysql` et les identifiants de la base **de cet environnement**.
   - `QUEUE_CONNECTION=database`.
   - `MAIL_MAILER=smtp` avec l'adresse du domaine créée dans cPanel (voir `emails-dns.md`). En production le SMTP est toujours utilisé, quel que soit `MAIL_MAILER`, et le contenu des e-mails n'est jamais écrit dans le log ; si le SMTP n'est pas configuré, chaque envoi journalise une erreur. Sur la recette (`APP_ENV=staging`) et sur tout environnement qui n'est ni `local` ni `production`, les e-mails (codes de connexion compris) restent dans `storage/logs/laravel.log` même avec `MAIL_MAILER=smtp` : un avertissement est alors journalisé à chaque envoi (voir `App\Support\MailPolicy`).
   - `MAIL_DEV_WEBHOOK_URL` : laisser vide en production (il y est de toute façon ignoré). Sur la recette, on peut y mettre l'adresse d'une boîte de réception web : chaque e-mail y est alors aussi transmis en POST (HTML).
   - `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` : hors périmètre de la vitrine, laisser vides (voir `stripe.md` le jour où la commande en ligne sera activée).

   Puis lancer :

   ```bash
   cd ~/patiss-stg
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   php artisan key:generate
   php artisan migrate --force
   php artisan storage:link
   php artisan optimize
   ```

   Sans `storage:link`, les photos téléversées ne s'affichent pas (`/storage/…` renvoie 403). Avec le lien symbolique du document root, vérifier que `~/stg.tahpatisserie.fr/storage` existe bien après cette commande.
8. **Compte de l'atelier.** `php artisan make:filament-user`, avec une adresse e-mail que la personne lit : la connexion sur `/atelier` demande à chaque fois un code reçu par e-mail.
9. **Contenu de départ.** Sur la recette seulement, on peut charger les douze pièces d'exemple de la maquette (photos d'illustration Pexels) :

   ```bash
   php artisan db:seed --class=CreationSeeder --force
   ```

   En production, les pièces se saisissent dans **Atelier → Créations** avec les vraies photos. Dans **Atelier → Réglages → Vitrine**, remplacer les valeurs d'exemple (prénom, WhatsApp, Instagram, Facebook, e-mail, adresse et horaires de l'atelier) par les vraies.
10. **Cron.** Dans « Tâches Cron », ajouter une tâche toutes les minutes **par environnement** :

    ```bash
    cd ~/patiss-stg && php artisan schedule:run >> /dev/null 2>&1
    cd ~/patiss && php artisan schedule:run >> /dev/null 2>&1
    ```

    Le planificateur vide la file des e-mails (messages de contact, codes de connexion) chaque minute.

## Mise à jour

Toujours sur la recette d'abord, puis en production une fois la recette vérifiée. Depuis le dossier de l'environnement, en SSH :

```bash
cd ~/patiss-stg   # ou ~/patiss
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize
```

`php artisan migrate --force` applique aussi les migrations de réglages (`database/settings`). En cas de problème après une mise à jour, `php artisan optimize:clear` vide les caches.

## Vérifications après déploiement

- Le site répond en HTTPS sur son adresse (`https://stg.tahpatisserie.fr` ou `https://tahpatisserie.fr`), et `/up` renvoie 200. `http://` (et `www.` en production) redirige vers l'adresse en `https://`.
- Le `.env` contient bien l'`APP_URL` de l'environnement : `php artisan about` l'affiche. Sur la recette, le mot de passe du répertoire est demandé.
- La page d'accueil affiche les créations, les filtres fonctionnent et une photo s'ouvre en grand. Le formulaire de contact envoie un message qui apparaît dans **Atelier → Messages** et arrive par e-mail à l'adresse de notification (après le passage du cron).
- Dans **Atelier → Créations**, l'aperçu d'une photo existante se charge et un nouvel envoi aboutit. En cas d'échec, la console du navigateur (F12) indique la cause : « CORS » ou « Mixed Content » signale un `APP_URL` faux ou un site encore servi en `http://` ; « 401 » sur `livewire/upload-file` signale une adresse signée refusée (voir « Envoi de photos et adresses » ci-dessous).

## Envoi de photos et adresses

- Les photos publiques sont servies en adresse relative (`/storage/…`, voir `config/filesystems.php`) : elles suivent l'hôte de la page, quel que soit `APP_URL`. C'est ce qui évite les blocages CORS de l'aperçu des photos dans l'atelier.
- Quand `APP_URL` est en `https://`, toutes les adresses générées sont en `https://` (`AppServiceProvider`). Si un proxy gère le HTTPS devant PHP, seul son en-tête `X-Forwarded-Proto` est pris en compte (`bootstrap/app.php`) ; `X-Forwarded-For` ne l'est pas, pour que la limite d'envois par IP du formulaire de contact ne puisse pas être contournée.
- En local : `php artisan storage:link` une fois, puis ouvrir le site sur l'hôte de son choix (`localhost`, `127.0.0.1`, `.test`) ; `APP_URL` ne sert qu'aux e-mails. Avec `MAIL_DEV_WEBHOOK_URL` renseigné, chaque e-mail envoyé est aussi transmis en POST (HTML) à cette adresse, dans tout environnement sauf `production`. `MAIL_MAILER=smtp` n'est respecté qu'en `local` et en `production`.
- `php artisan schedule:list` affiche la tâche de file d'attente.
- Les pages de commande (`/gateaux`, `/demande-libre`, `/acompte/…`) existent encore mais ne sont plus liées depuis le site. Tant que les réglages obligatoires (adresse professionnelle, SIRET, taux d'acompte, conditions d'annulation, durée des liens) sont vides, elles affichent « Commandes bientôt ouvertes ».

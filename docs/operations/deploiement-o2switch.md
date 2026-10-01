# Déploiement sur o2switch

Ce guide s'adresse au proche qui maintient le site. L'application est une application Laravel standard ; o2switch l'héberge sur un compte cPanel mutualisé.

## Première installation

1. **SSH.** Dans cPanel, ouvrir « Autorisation SSH » et autoriser l'adresse IP depuis laquelle on se connecte. o2switch refuse les connexions SSH des IP non autorisées.
2. **Version de PHP.** Dans « Sélectionner une version de PHP », choisir PHP 8.4 ou 8.5 et activer les extensions `gd` (ou `imagick`), `intl`, `sodium`, `pdo_mysql`, `mbstring`, `fileinfo`, `exif`, `zip`.
3. **Limites PHP** (onglet Options du même écran), nécessaires aux photos des clients (5 fichiers de 10 Mo) :

   | Réglage | Valeur minimale |
   |---|---|
   | `upload_max_filesize` | 10M |
   | `post_max_size` | 60M |
   | `max_file_uploads` | 5 |
   | `memory_limit` | 512M |
   | `max_execution_time` | 60 |

   Vérifier ensuite sur le serveur : `php -i | grep -E 'upload_max_filesize|post_max_size|max_file_uploads|memory_limit|max_execution_time'`.
4. **Base de données.** Dans « Bases de données MySQL », créer une base, un utilisateur, et donner tous les droits à l'utilisateur sur la base.
5. **Code.** Dans « Git Version Control », cloner le dépôt dans un dossier hors de `public_html` (par exemple `~/patiss`). À défaut, cloner en SSH.
6. **Racine du domaine.** Dans « Domaines », faire pointer la racine du domaine vers `~/patiss/public`. Aucun autre dossier du projet ne doit être accessible depuis le web. Toujours dans « Domaines », activer **« Forcer la redirection HTTPS »**. Le site répond sur une seule adresse, `https://tahpatisserie.fr` : si `www.tahpatisserie.fr` existe, le rediriger vers elle (« Redirections » de cPanel, redirection permanente 301).
7. **Environnement.** Copier `.env.example` en `.env`, puis renseigner :
   - `APP_ENV=production`, `APP_DEBUG=false`
   - `APP_URL` : l'adresse exacte de **cet** environnement, en `https://` et sans `/` final (`https://tahpatisserie.fr` en production, l'adresse de la recette sur la recette). Les liens des e-mails et de Stripe en dépendent : une recette avec l'`APP_URL` de la production envoie ses clients vers la production.
   - `SESSION_SECURE_COOKIE=true` ; laisser `SESSION_DOMAIN` vide (cookie limité à l'hôte exact) et, sur la recette, `SESSION_COOKIE=tahpatisserie_recette_session` pour que ses cookies ne se mélangent jamais avec ceux de la production (sinon erreurs « 419 Page expirée »).
   - `DB_CONNECTION=mysql` et les identifiants de la base
   - `QUEUE_CONNECTION=database`
   - `MAIL_MAILER=smtp` avec l'adresse du domaine créée dans cPanel (voir `emails-dns.md`)
   - `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` (voir `stripe.md`)

   Puis lancer `php artisan key:generate` et `php artisan storage:link` (sans ce lien, les photos des gâteaux ne s'affichent pas : `/storage/…` renvoie 403).
8. **Cron.** Dans « Tâches Cron », ajouter une tâche toutes les minutes :

   ```bash
   cd ~/patiss && php artisan schedule:run >> /dev/null 2>&1
   ```

   Le planificateur vide la file des e-mails chaque minute et lance la purge quotidienne des demandes échues.

## Mise à jour

Depuis `~/patiss`, en SSH :

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize
```

`php artisan migrate --force` applique aussi les migrations de réglages (`database/settings`). En cas de problème après une mise à jour, `php artisan optimize:clear` vide les caches.

## Vérifications après déploiement

- Le site répond en HTTPS sur le domaine, et `/up` renvoie 200. `http://` et `www.` redirigent vers `https://tahpatisserie.fr`.
- Une photo de gâteau s'affiche sur la collection, et dans **Atelier → Collection** l'aperçu d'une photo existante se charge et un nouvel envoi aboutit. En cas d'échec, la console du navigateur (F12) indique la cause : « CORS » ou « Mixed Content » signale un `APP_URL` faux ou un site encore servi en `http://` ; « 401 » sur `livewire/upload-file` signale une adresse signée refusée (voir « Envoi de photos et adresses » ci-dessous).

## Envoi de photos et adresses

- Les photos publiques sont servies en adresse relative (`/storage/…`, voir `config/filesystems.php`) : elles suivent l'hôte de la page, quel que soit `APP_URL`. C'est ce qui évite les blocages CORS de l'aperçu des photos dans l'atelier.
- Quand `APP_URL` est en `https://`, toutes les adresses générées sont en `https://` (`AppServiceProvider`). Si un proxy gère le HTTPS devant PHP, seul son en-tête `X-Forwarded-Proto` est pris en compte (`bootstrap/app.php`) ; `X-Forwarded-For` ne l'est pas, pour que la limite d'envois par IP des formulaires ne puisse pas être contournée.
- En local : `php artisan storage:link` une fois, puis ouvrir le site sur l'hôte de son choix (`localhost`, `127.0.0.1`, `.test`) ; `APP_URL` ne sert qu'aux e-mails.
- `php artisan schedule:list` affiche la tâche de file d'attente.
- Tant que les réglages obligatoires (adresse professionnelle, SIRET, taux d'acompte, conditions d'annulation, durée des liens) sont vides, les formulaires de demande affichent « Commandes bientôt ouvertes ».

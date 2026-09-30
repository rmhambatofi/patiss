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
6. **Racine du domaine.** Dans « Domaines », faire pointer la racine du domaine vers `~/patiss/public`. Aucun autre dossier du projet ne doit être accessible depuis le web.
7. **Environnement.** Copier `.env.example` en `.env`, puis renseigner :
   - `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`
   - `DB_CONNECTION=mysql` et les identifiants de la base
   - `QUEUE_CONNECTION=database`
   - `MAIL_MAILER=smtp` avec l'adresse du domaine créée dans cPanel (voir `emails-dns.md`)
   - `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` (voir `stripe.md`)

   Puis lancer `php artisan key:generate`.
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

- Le site répond en HTTPS sur le domaine, et `/up` renvoie 200.
- `php artisan schedule:list` affiche la tâche de file d'attente.
- Tant que les réglages obligatoires (adresse professionnelle, SIRET, taux d'acompte, conditions d'annulation, durée des liens) sont vides, les formulaires de demande affichent « Commandes bientôt ouvertes ».

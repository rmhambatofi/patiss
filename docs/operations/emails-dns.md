# E-mails du site et DNS

Ce guide s'adresse au proche qui maintient le site. Le site envoie ses e-mails (accusé de réception, proposition, confirmation, notification à l'artisan, codes de connexion à l'atelier) par le serveur SMTP d'o2switch, depuis une adresse du domaine. Ils partent par la file d'attente, vidée chaque minute par le cron (voir [deploiement-o2switch.md](deploiement-o2switch.md)).

Sans SPF, DKIM et DMARC, ces e-mails finissent en spam, et le client ne voit jamais sa proposition.

## 1. Créer la boîte du domaine

1. Dans cPanel, ouvrir **Comptes de messagerie → Créer**.
2. Créer par exemple `contact@<domaine>`, avec un mot de passe long, gardé dans un gestionnaire de mots de passe.
3. Cette adresse sert d'expéditeur des e-mails du site. Elle peut aussi être l'adresse de contact publique : c'est l'adresse saisie dans **Réglages → E-mail de notification des demandes**, affichée dans les mentions légales, les CGV et la politique de confidentialité. N'y mettez pas une adresse personnelle.
4. Si l'artisan préfère lire ses e-mails dans Gmail, ajouter une redirection (**Redirecteurs**) de `contact@<domaine>` vers son adresse habituelle.

## 2. SPF, DKIM et DMARC

Le domaine doit utiliser les serveurs DNS d'o2switch (c'est le cas s'il a été acheté chez o2switch). Sinon, recopier les enregistrements ci-dessous chez le registraire.

1. Dans cPanel, ouvrir **Délivrabilité des e-mails** (Email Deliverability).
2. Pour le domaine, vérifier que **DKIM** et **SPF** sont au statut « Valide ». Sinon, cliquer sur **Gérer** et **Installer l'enregistrement suggéré** pour chacun.
3. SPF attendu, en un seul enregistrement TXT sur le domaine : il commence par `v=spf1` et contient les serveurs d'o2switch (`+a +mx` et l'inclusion proposée par cPanel), terminé par `~all`. Il ne doit exister **qu'un seul** enregistrement SPF : fusionner s'il y en a deux.
4. DMARC : dans **Zone Editor**, ajouter un enregistrement TXT :

   | Nom | Valeur |
   |---|---|
   | `_dmarc.<domaine>` | `v=DMARC1; p=none; rua=mailto:contact@<domaine>` |

   Laisser `p=none` le premier mois, lire les rapports reçus, puis passer à `p=quarantine` quand tout est aligné.
5. Attendre la propagation (de quelques minutes à quelques heures).

## 3. Réglages `.env`

Les valeurs exactes (nom du serveur, port) figurent dans cPanel, **Comptes de messagerie → Connect Devices** pour la boîte créée.

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=<serveur SMTP indiqué par cPanel>
MAIL_PORT=465
MAIL_USERNAME=contact@<domaine>
MAIL_PASSWORD=<mot de passe de la boîte>
MAIL_FROM_ADDRESS=contact@<domaine>
MAIL_FROM_NAME="<Nom commercial>"
```

L'adresse d'expédition (`MAIL_FROM_ADDRESS`) doit être celle de la boîte authentifiée, sinon le serveur refuse l'envoi ou DMARC échoue. Après modification : `php artisan optimize`.

## 4. Tester la délivrabilité

1. Envoyer une demande de test depuis le site avec une adresse **Gmail**, puis avec une adresse **Outlook/Hotmail**.
2. Attendre une minute (le cron vide la file), puis vérifier que l'accusé arrive **dans la boîte de réception**, pas dans les spams, dans les deux cas.
3. Dans Gmail, ouvrir l'e-mail → menu ⋮ → **Afficher l'original** : `SPF`, `DKIM` et `DMARC` doivent être à `PASS`.
4. Pour un score détaillé, envoyer un e-mail depuis la boîte à l'adresse donnée par <https://www.mail-tester.com> et viser 9/10 ou plus.
5. Refaire le test pour la confirmation de commande pendant la répétition complète (voir [checklist-mise-en-ligne.md](checklist-mise-en-ligne.md)).

## En cas de problème

- Rien n'arrive : vérifier que le cron tourne (`php artisan schedule:list`) et que la file se vide (`php artisan queue:failed` liste les envois en échec, avec le message d'erreur).
- Erreur d'authentification : revoir `MAIL_USERNAME`/`MAIL_PASSWORD`, puis `php artisan optimize`.
- En spam malgré SPF/DKIM valides : vérifier DMARC, et que `MAIL_FROM_ADDRESS` est bien sur le domaine.

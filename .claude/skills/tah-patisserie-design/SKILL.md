---
name: tah-patisserie-design
description: Design system vitrine Tah'Pâtisserie (Claude Design, structure Mobbin et couleurs Airbnb). À utiliser pour toute création ou modification d'une page publique, d'une vue Blade, d'un composant ou de styles CSS/Tailwind du site : couleurs, typographie, espacements, composants <x-ds.*>, ton des textes.
---

# Design system vitrine Tah'Pâtisserie

Source de vérité : la maquette `Tah Patisserie Vitrine Mobbin Couleurs Airbnb.dc.html` (et sa déclinaison mobile) du projet
Claude Design https://claude.ai/design/p/5ab6b005-c8df-4b0d-9a66-9e12bbac649d. Les fondations (ambiance, couleurs, typo,
géométrie, mouvement, ton éditorial) sont dans `readme.md` : **le lire avant de concevoir une page**.

## Où vit le design system dans le code

| Quoi | Où |
|---|---|
| Tokens CSS (copie conforme, ne pas éditer) | `resources/css/design-system/*.css` |
| Branchement Tailwind + classes de composants | `resources/css/app.css` (`@theme inline reference`, `@layer components`) |
| Composants Blade | `resources/views/components/ds/*.blade.php` → `<x-ds.*>` |
| Layout public (pilule de navigation, pied de page, barre de contact mobile) | `resources/views/components/layouts/public.blade.php` |
| Comportements (filtres, visionneuse, menu) | `resources/js/app.js` (vanilla, tout fonctionne sans JS) |
| Police (Hanken Grotesk 300–700) | `vite.config.js` (auto-hébergée via Bunny Fonts) |

## Règles pour toute nouvelle page

1. Partir de `<x-layouts.public>` (`bare` pour des sections pleine largeur, `contact-bar` pour la barre WhatsApp / Écrire mobile, `photo-credit` pour la mention des photos d'illustration).
2. Utiliser les composants `<x-ds.*>` avant d'écrire du balisage :
   - `x-ds.button` (`variant` primary|secondary|soft|dark|white, `size` sm (44px)|md|lg (48px), `href` → `<a>`, sinon `<button>`, `full`)
   - `x-ds.icon` (menu, close, check, arrow, handmade, quality, whatsapp, instagram, facebook, mail, globe) ; bouton rond 44px = classe `icon-btn`
   - `x-ds.photo` (tuile 4/5 rayon 24, étiquette translucide, `grayscale` pour un portrait), `x-ds.work-card` (pièce de galerie, ouvre `x-ds.lightbox`)
   - `x-ds.value-card`, `x-ds.step-card`, `x-ds.review-card`, `x-ds.stat`, `x-ds.contact-link`
   - `x-ds.heading` (`level` 1 à 4), `x-ds.field` (libellé + contrôle `.field-input` + aide + erreur)
3. Sinon, les classes de composants de `app.css` : `container-site`, `nav-pill`, `seg` / `seg-btn` (segmented control, actif via `aria-pressed="true"` ou `peer-checked`), `badge-overlay`, `squircle`, `card`, `card-soft`, `panel`, `heading-1..4`, `title`, `lead`, `eyebrow`, `link`, `stat`, `contact-link`, `field-label`, `field-input`, `field-hint`, `field-error`, `alert-error`.
4. Puis les utilitaires Tailwind branchés sur les tokens (jamais de hex, de `rgba()` ni de taille en dur) :
   - Couleurs : `canvas` (blanc, fond de page), `surface` (gris 6 %, pilules, blocs, cartes mises en avant), `field` (gris 8 %, champs), `hairline` / `hairline-soft` (filets), `ink` (texte, titres, boutons sombres), `ink-soft`, `muted` (texte secondaire), `faint`, `primary` / `primary-active` (rouge Rausch : CTA et squircles de contact uniquement), `error`, `overlay` (étiquette sur photo), `scrim` (voile de la visionneuse).
   - Texte : `text-display` (h1), `text-h2|h3|h4`, `text-title`, `text-lead`, `text-body`, `text-body-sm`, `text-caption`, `text-label`, `text-tag` ; `leading-display|heading|card|title|body|body-sm|label|caption` ; graisses `font-light` (chapô), `font-normal`, `font-semibold` (titres courts, boutons, libellés), `font-bold` (display et titres).
   - Rayons : `rounded-sm` (16 : champs, tuiles, lignes de contact, chiffres clés), `rounded-md` (24 : cartes, blocs, photos), `rounded-full` (toute action), `rounded-squircle` (30 % : carrés d'icône).
   - Ombres (presque jamais) : `shadow-segment` (option active d'un segmented control), `shadow-pill` / `shadow-pill-strong` (pilules flottantes mobile), `shadow-hover` (survol de carte si besoin).
   - Mise en page : `max-w-site` (1200px), `max-w-nav` (1040px), `p-(--block-pad)` (bloc 24–48px), sections `pb-30` (120px) / `pb-20` (80px), `animate-fadein`.
5. Un besoin non couvert : ajouter d'abord le composant `x-ds.*` ou la classe dans `app.css` en suivant le readme, plutôt que du style ponctuel dans la vue. Une nouvelle valeur (couleur, ombre…) se crée dans Claude Design puis se recopie dans `resources/css/design-system/`.

## Rappels de marque (détail dans `readme.md`)

- Français, vouvoiement ; l'artisane parle à la première personne (« je », « mon atelier »). Phrases courtes et concrètes, titres en phrase déclarative terminée par un point (« Tout est fait main. »), pas de superlatifs, **pas d'emoji**. Typographie française (espace avant « : ! ? % », « 48 h », « 9h – 18h »).
- Boutons : verbe, 1 à 4 mots (Voir mes créations, Me contacter, Écrire sur WhatsApp, Envoyer).
- Une seule couleur : le rouge `primary` sur les CTA et les squircles de contact ; tout le reste est blanc, gris et encre. Pas de dégradé, pas de bande colorée, pas de capitales espacées, pas d'ombre portée (hors pilules flottantes mobile et option active).
- Tout est arrondi : pilule pour les actions et les filtres, 16 pour les champs et tuiles, 24 pour les cartes, blocs et photos, squircle 30 % pour les icônes.
- Une seule famille, Hanken Grotesk : display et titres en 700 avec interlignes serrés (1 à 1,13), chapôs en 300, texte en 400.
- Photos en vedette (4/5 ou carré, cover, coins 24, étiquette translucide) ; portrait de l'artisane en noir et blanc. Toute photo d'illustration est créditée dans le pied de page.
- Mobile-first : pilule de navigation flottante, menu en liste de lignes blanches, barre fixe WhatsApp / Écrire, cibles ≥ 44px ; `details` pour le menu et `<dialog>` pour la visionneuse (fonctionnent sans JS).
- Mouvements : changement de fond 0,2 s, fondu 0,2 s. Pas de lift, pas de scale, pas de rebond.

## Différences assumées avec la maquette

- Le nom affiché vient de `ShopSettings::business_name` ; le prénom de l'artisan, les canaux de contact (WhatsApp, Instagram, Facebook, e-mail), l'adresse et les horaires de l'atelier viennent des réglages de l'atelier (section « Vitrine »). La ville est déduite de l'adresse.
- Les pièces de la galerie sont des enregistrements `Creation` (atelier → Créations) ; les quatre photos du hero sont les pièces « mises en avant ».
- Le formulaire de contact est réel (`POST /contact`, modèle `ContactMessage`, e-mail à l'artisan) ; la maquette simule l'envoi.
- Commande, panier, paiement : hors périmètre de la vitrine v1. Les pages de demande et d'acompte existent encore mais ne sont plus liées ; elles s'appuient sur des alias transitoires dans `app.css`.
- Témoignages et chiffres clés sont des exemples de contenu, signalés comme tels dans la vue.
- Messages d'erreur de formulaire (`alert-error`, `field-error`) : en `error` (rouge Airbnb), absents de la maquette.

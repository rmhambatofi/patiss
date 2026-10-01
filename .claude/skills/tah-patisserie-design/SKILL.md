---
name: tah-patisserie-design
description: Design system Tah'Pâtisserie (Claude Design). À utiliser pour toute création ou modification d'une page publique, d'une vue Blade, d'un composant ou de styles CSS/Tailwind du site : couleurs, typographie, espacements, composants <x-ds.*>, ton des textes.
---

# Design system Tah'Pâtisserie

Source de vérité : le projet Claude Design https://claude.ai/design/p/4b946b13-312a-4c84-8495-e04b779d1b7c.
Les fondations (ambiance, couleurs, typo, rayons, ombres, mouvement, ton éditorial) sont dans
`readme.md` (copie du readme du design system) : **le lire avant de concevoir une page**.

## Où vit le design system dans le code

| Quoi | Où |
|---|---|
| Tokens CSS (copie conforme, ne pas éditer) | `resources/css/design-system/*.css` |
| Branchement Tailwind + classes de composants | `resources/css/app.css` (`@theme inline reference`, `@layer components`) |
| Composants Blade | `resources/views/components/ds/*.blade.php` → `<x-ds.*>` |
| Layout public (header, footer, barre mobile « Commander ») | `resources/views/components/layouts/public.blade.php` |
| Polices (Manrope, Cormorant Garamond) | `vite.config.js` (auto-hébergées, pas Google Fonts) |

## Règles pour toute nouvelle page

1. Partir de `<x-layouts.public>` (`bare` pour des sections pleine largeur, `order-bar` pour afficher la barre « Commander » mobile).
2. Utiliser les composants `<x-ds.*>` avant d'écrire du balisage :
   - `x-ds.button` (`variant` primary|dark|secondary|ghost|white, `size` sm|md|lg, `href` → `<a>`, sinon `<button>`, `full`)
   - `x-ds.icon-button`, `x-ds.icon` (cart, menu, close, handmade, quality, delivery, secure, arrow, edit)
   - `x-ds.heading` (`level` 1 à 4, `as` pour la balise), `x-ds.section-heading` (eyebrow + titre + lien « → »)
   - `x-ds.chip` (`size` md|lg), `x-ds.badge`
   - `x-ds.image-frame` (photo cover ou hachures lilas/rose), `x-ds.product-card`, `x-ds.category-tile`
   - `x-ds.trust-bar` / `x-ds.trust-item`
   - `x-ds.field` (libellé + contrôle `.field-input` + aide + erreur)
3. Sinon, les classes de composants de `app.css` : `container-site`, `heading-1..4`, `eyebrow`, `lead`, `link`, `link-arrow`, `panel`, `panel-soft`, `block-gradient`, `field-label`, `field-input`, `field-hint`, `field-error`, `alert-error`, `alert-warning`, `placeholder-lilac|rose`.
4. Puis les utilitaires Tailwind branchés sur les tokens (jamais de hex, de `rgba()` ni de taille en dur) :
   - Couleurs : `cream` (fond page), `lilac-3` / `lilac-2` / `lilac` (surfaces lilas 50/100/200), `rose-light` / `rose` / `rose-strong`, `icon` (carré d'icône), `line-soft` / `line` / `line-strong` (bordures), `amethyst` (accent, CTA, liens), `plum` (logo, prix, fond sombre), `ink` (titres), `ink-2` (corps de texte), `muted` (discret), `placeholder`, `nav`, `lavender-pale`, `gold` (étoiles).
   - Texte : `text-display-1|2|3`, `text-title`, `text-logo`, `text-lead`, `text-body-sm`, `text-small`, `text-caption`, `text-micro`, `text-tag` ; `tracking-eyebrow|tag|badge` ; `leading-display`.
   - Rayons : `rounded-sm|md|lg|xl` (8/14/18/22 px, valeurs du design system), `rounded-card` (24), `rounded-block` (32), `rounded-hero` (40), `rounded-full` (pilules).
   - Ombres (toujours violettes) : `shadow-card`, `shadow-card-hover`, `shadow-float`, `shadow-bar`, `shadow-image`, `shadow-hero`, `shadow-button`, `shadow-button-lg`, `shadow-button-hover`.
   - Dégradés : `bg-(image:--gradient-hero)`, `bg-(image:--gradient-hero-vivid)`, `bg-(image:--gradient-block)`.
   - Mise en page : `max-w-site` (1200px), `animate-floaty`.
5. Un besoin non couvert : ajouter d'abord le composant `x-ds.*` ou la classe dans `app.css` en suivant le readme, plutôt que du style ponctuel dans la vue. Une nouvelle valeur (couleur, ombre…) se crée dans Claude Design puis se recopie dans `resources/css/design-system/`.

## Rappels de marque (détail dans `readme.md`)

- Français, vouvoiement ; la marque dit « on » / « notre ». Phrases courtes et concrètes, pas de superlatifs creux, **pas d'emoji**. Typographie française (espace avant « : ! ? % », guillemets « … », « 32 € »).
- Boutons : verbe, 1 à 4 mots (Commander, Découvrir la collection, Demander ce gâteau).
- Violet en accent seulement, jamais en grands aplats. Pas de noir pur ni de gris neutre. Dégradés rose → lilac uniquement, jamais sur les boutons ni le texte.
- Tout est arrondi : pilules pour les actions et les champs, 24px pour les cartes, 32px pour les blocs.
- Serif Cormorant Garamond pour les titres (accent émotionnel en *italique améthyste* dans le h1), Manrope pour le reste.
- Mobile-first : cibles tactiles ≥ 44px, « Commander » toujours accessible (header desktop, barre fixe mobile).
- Mouvements discrets (0.2–0.25s) : boutons lift -2px, cartes -6px, tuiles scale 1.03 ; respecter `motion-reduce`.

## Différences assumées avec la maquette

- Le nom affiché vient de `ShopSettings::business_name` et les contenus (adresse, zone de livraison) sont ceux du vrai site, pas les exemples du design system (Lyon, avis fictifs…).
- Pas de panier : le site fonctionne par demande puis acompte, donc pas d'IconButton panier dans le header.
- Messages d'erreur ou d'alerte de formulaire (`alert-error`, `alert-warning`, `field-error`) : absents du design system, en rouge/ambre Tailwind.

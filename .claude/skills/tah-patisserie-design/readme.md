# Tah'Pâtisserie — Design System

> Copie du `readme.md` du projet Claude Design https://claude.ai/design/p/4b946b13-312a-4c84-8495-e04b779d1b7c.
> Les chemins cités (`tokens/`, `components/`…) sont ceux du projet Claude Design ; leurs équivalents dans le site sont listés dans `SKILL.md`.

Système de design de **Tah'Pâtisserie**, pâtisserie artisanale (Lyon) qui vend ses créations en ligne — commande, retrait à l'atelier ou livraison. Cible : particuliers 25-55 ans, gourmands, sensibles au fait-maison et à l'esthétique (anniversaires, fêtes, cadeaux, plaisir du week-end). Objectif de chaque écran : donner envie en une seconde et mener au bouton **« Commander »**.

**Sources** : dossier local `Landing page Tah'Pâtisserie/` (design system préexistant, repris tel quel ici — tokens, composants, cartes, UI kit) ; sa landing page de référence `Tah Patisserie Landing.dc.html`, portée dans `templates/landing-page/` ; deux photos produit déposées par l'utilisateur (`assets/images/`). Aucun logo, Figma ni dépôt Git fournis.

## Index
- `styles.css` — point d'entrée CSS (imports uniquement) → `tokens/` : `fonts.css`, `colors.css`, `typography.css`, `spacing.css`, `effects.css`, `base.css`.
- `guidelines/` — cartes de fondations (Colors, Type, Spacing, Effects, Brand).
- `components/core/` — Button, IconButton, Icon, Badge, Chip, Eyebrow, Heading, SectionHeading, ImageFrame, Rating.
- `components/cards/` — ProductCard, CategoryTile, ReviewCard, TrustItem + TrustBar.
- `components/forms/` — EmailCapture, TextField.
- `components/navigation/` — Header, MobileOrderBar, Footer.
- `ui_kits/site/` — landing page recomposée (`index.html`, `LandingPage.jsx`).
- `templates/landing-page/LandingPage.dc.html` — template « Landing page » (3 variantes de hero via la prop `heroVariant`), point de départ pour les projets consommateurs.
- `thumbnail.html` — vignette du design system.
- `assets/images/` — photos produit fournies.
- `SKILL.md` — mode d'emploi pour agents.

**Ajouts intentionnels** (absents de la landing mais nécessaires à « tout le site ») : `TextField` (formulaires devis/contact/checkout), `Icon` (wrapper du petit jeu d'icônes), variante `close` du menu.

## Fondamentaux de contenu
- **Langue** : français, tutoiement jamais — on dit **« vous »**, et la marque dit **« on »/« notre »** (« on dessine ensemble », « notre atelier »). Chaleureux, gourmand, humain.
- **Phrases courtes**, concrètes, sensorielles : « Mousse légère, cœur framboise, biscuit amande. 6 parts. » Pas de superlatifs creux ; des faits (beurre AOP, fruits de saison, devis sous 48 h).
- **Casse** : phrase normale ; seules les *eyebrows*, badges et titres de colonnes du footer sont en capitales espacées. Noms de produits en capitales initiales (« Tarte Citron Meringuée »).
- **Typographie française** : espace avant « : », « ! », « ? », « % » ; guillemets « … » ; prix « 32 € » ; horaires « 9h – 19h ».
- **Boutons** : verbe à l'infinitif ou 1re personne, 1-4 mots — Commander, Ajouter, Découvrir la carte, Demander un devis, Je m'inscris.
- **Pas d'emoji.** Les étoiles ★ sont la seule « icône texte ».
- Contenu fictif (avis, chiffres) explicitement signalé « (témoignages fictifs — exemples de contenu) » tant qu'il n'est pas réel.

## Fondations visuelles
- **Ambiance** : lumineux, féminin-chic, premium et gourmand. Beaucoup de blanc, pastel en surfaces, violet soutenu seulement en accent (jamais en grands aplats — exception : le bloc newsletter prune).
- **Couleurs** : fond `#FCFAFE` (blanc lavande) ; surfaces lilas `#EDE4F7`/`#F4EDF9`/`#F9F4FC`, rose poudré `#F9E6EE` ; accent améthyste `#6B2D8B` (CTA, liens, eyebrows), prune `#4E1F68` (logo, prix, hover, bloc sombre) ; texte `#2E1A3A` (titres) / `#5A4664` (corps) / `#6E5A78` (discret) — **jamais de noir pur ni de gris neutre** ; or `#B8860B` pour les étoiles.
- **Dégradés** : uniquement rose → lilas, doux, 120–135°. Hero `--gradient-hero`, blocs `--gradient-block`, variante colorée `--gradient-hero-vivid`. Pas de dégradé sur les boutons ni le texte.
- **Typographie** : Cormorant Garamond (serif) pour titres, nom de marque (700, prune), citations (italique) ; accent émotionnel en *italique améthyste* dans le h1. Manrope pour tout le reste (400–700). Mono système uniquement pour les annotations/placeholders.
- **Échelle** : h1 clamp(44–76), h2 clamp(34–52), h3 cartes 24, lead 18, body 16, small 14, caption 13, eyebrow 12, badge 11. Interlignes 1.02–1.05 (display), 1.6–1.7 (texte).
- **Espacement** : base 4px ; container 1200 + 24 de gouttière ; sections 56/72/96 ; blocs `clamp(32px,5vw,64px)` ; gap cartes 24, tuiles 14.
- **Rayons** : tout est arrondi. Pilule (999) pour boutons, chips, badges, champs ; 24 cartes ; 32 blocs ; 40 image hero ; 14 carrés d'icône.
- **Ombres** : toujours teintées violet `rgba(107,45,139, 0.07–0.32)`, portée large et basse (`0 10px 30px` cartes, `0 30px 70px` hero). Pas d'ombre interne, pas de bordure + ombre forte en même temps.
- **Bordures** : 1px lilas `#E3D6EE` sur boutons secondaires, boutons ronds, séparateurs ; `#EDE4F7` pour le header/footer.
- **Cartes** : blanc, rayon 24, ombre carte, photo carrée pleine largeur en haut, contenu 20px.
- **Fonds** : plats ou dégradés doux ; pas de motif ni texture ; placeholders photo = hachures lilas/rose 14px à 135° avec étiquette mono.
- **Imagerie** : photos produit en vedette — grand format, cadrage serré, lumière naturelle, tons chauds/clairs ; coins arrondis, jamais de filtre N&B ni de grain.
- **Mouvement** : micro-interactions discrètes 0.2–0.25s ease — boutons lift -1/-2px + ombre renforcée ; cartes lift -6px ; tuiles scale 1.03 ; image hero flotte (`tp-floaty` 7s). Pas de rebond, pas d'entrées animées lourdes.
- **États** : hover secondaires = fond lilas très clair ; pressé = pas de shrink (lift seulement) ; focus champs = bordure améthyste + halo 4px à 12 %. Désactivé = opacité 0.5.
- **Transparence & flou** : réservés aux éléments fixes — header (85 % + blur 14) et barre mobile (92 %). Étiquettes sur image : blanc 92 %.
- **Layout** : mobile-first, grilles `auto-fit/auto-fill` ; sous 760px le header passe en burger et le CTA « Commander » vit dans une barre fixe en bas ; cibles tactiles ≥ 44px.
- **Contrastes vérifiés** : corps sur rose ≈ 7:1, discret sur lilas ≈ 5:1, blanc sur améthyste ≈ 8.6:1, lavande sur prune ≈ 9.5:1.

## Iconographie
- Pas d'icon font. Un **petit jeu d'icônes filaires maison** (trait 1.8, extrémités rondes, 24×24, currentColor) : cart, menu, close, handmade, quality, delivery, secure, arrow — exposé via le composant `Icon`. Posées dans un carré lilas 46px rayon 14 pour la réassurance, nues dans les boutons ronds.
- Fallback autorisé pour de nouveaux besoins : **Lucide** (même graisse 1.8–2, même style). Jamais d'icônes pleines ni d'emoji.
- Caractères Unicode comme icônes : seulement ★ (notes) et → (liens, tuiles).
- **Logo** : aucun fourni — le nom « Tah'Pâtisserie » en Cormorant Garamond 700 prune tient lieu de marque. Ne pas en dessiner un.
- Images : `assets/images/gateau-anniversaire-1.jpg`, `-2.jpg` (photos déposées par l'utilisateur).

## Polices
Chargées depuis Google Fonts (`tokens/fonts.css`) : Cormorant Garamond 500/600/700 + italique 500, Manrope 400–700. Aucun fichier de police local n'a été fourni — à remplacer par des fichiers auto-hébergés en production.

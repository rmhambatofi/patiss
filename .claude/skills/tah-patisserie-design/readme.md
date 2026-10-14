# Tah'Pâtisserie — Design system vitrine

> Fondations de la maquette `Tah Patisserie Vitrine Mobbin Couleurs Airbnb.dc.html` du projet Claude Design
> https://claude.ai/design/p/5ab6b005-c8df-4b0d-9a66-9e12bbac649d, qui combine la structure et la géométrie de Mobbin
> (`uploads/DESIGN-mobbin.md`) avec les couleurs d'Airbnb (`uploads/DESIGN-airbnb.md`). Les équivalents dans le site sont listés dans `SKILL.md`.

Site vitrine de **Tah'Pâtisserie**, pâtisserie artisanale : une page qui montre les créations, présente l'artisane et son atelier, et mène au contact (WhatsApp, e-mail, formulaire). **Pas de commande ni de paiement en première version.** Cible : particuliers qui préparent une occasion (anniversaire, mariage, baptême, événement d'entreprise). Objectif de chaque écran : laisser les photos parler et mener à « Me contacter ».

## Fondamentaux de contenu
- **Langue** : français, vouvoiement. L'artisane parle à la première personne (« Je m'appelle Tahina », « mon atelier », « écrivez-moi »). Chaleureux, direct, sans emphase.
- **Titres** : phrases déclaratives courtes terminées par un point (« La pâtisserie comme un art du quotidien. », « Une sélection de mes pièces. », « Du geste à l'assiette. »). Les titres de valeurs aussi (« Tout est fait main. »).
- **Phrases courtes**, concrètes, sensorielles : « Génoise vanille, crème légère, fleurs fraîches. » Des faits (beurre AOP, fruits du marché, réponse sous 48 h), pas de superlatifs.
- **Casse** : phrase normale partout. **Aucune capitale espacée**, aucun interlettrage : les libellés sont en 12px semi-gras gris.
- **Typographie française** : espace avant « : », « ! », « ? », « % » ; guillemets « … » ; « 48 h », « 100 % », « 9h – 18h », « Mar – Sam ».
- **Boutons** : verbe, 1 à 4 mots — Voir mes créations, Écrire sur WhatsApp, Me contacter, Envoyer, Envoyer un autre message.
- **Pas d'emoji.** Les seuls caractères-icônes : ↗ (lien sortant) et · (séparateur).
- Contenu fictif (témoignages, chiffres) signalé « Témoignages fictifs — exemples de contenu. » tant qu'il n'est pas réel ; photos d'illustration créditées dans le pied de page (« Photos d'illustration : Pexels »).

## Fondations visuelles
- **Ambiance** : galerie blanche. Le chrome s'efface derrière les photos : blanc, gris très clairs, encre presque noire, et une seule « tension » rouge pour les actions.
- **Couleurs** : fond `#FFFFFF` ; surfaces `#F7F7F7` (pilule de navigation, blocs, piste des filtres, cartes mises en avant) et `#F2F2F2` (champs) ; filets `#DDDDDD` (bordures de boutons, pied de page) et `#EBEBEB` (cartes blanc sur blanc) ; texte `#222222` (titres, corps, liens) / `#3F3F3F` / `#6A6A6A` (secondaire) / `#929292` (effacé) ; accent `#FF385C` (CTA, squircles de contact, coche de confirmation), pressé `#E00B41`, désactivé `#FFD1DA` ; erreur `#C13515`. **Jamais de noir pur, jamais de seconde couleur.**
- **Voiles** : étiquette sur photo `rgba(115,115,115,0.56)` + flou 6px, texte blanc ; fond de visionneuse `rgba(0,0,0,0.6)`.
- **Typographie** : une seule famille, **Hanken Grotesk** (substitut ouvert de Saans). Display et titres en 700 (la maquette dit 652) ; titres courts, boutons, libellés et liens en 600 ; chapôs en 300 ; texte en 400 (maquette 456). Aucun interlettrage.
- **Échelle** : h1 `clamp(44px, 7vw, 80px)` interligne 1 ; h2 `clamp(32px, 4vw, 44px)` 1,13 ; h3 32 / 1,13 ; h3 de carte 24 / 1,25 ; titre 20 / 1,3 ; chapô 20 / 1,38 en 300 ; corps 16 / 1,38 ; petit 14 / 1,43 ; mention 13 / 1,23 ; libellé 12 / 1,33 ; étiquette mobile 11. Chiffres clés 32 / 1,13.
- **Espacement** : base 4px ; conteneur 1200 + 24 de gouttière ; pilule de navigation 1040 × 60 ; sections 80 / 120 ; blocs `clamp(24px, 4vw, 48px)` ; cartes 24 ; grilles 16 ; chiffres clés 8.
- **Géométrie** : pilule (9999) pour toute action, filtre, étiquette, champ de navigation ; 16 pour les champs, tuiles d'étape, lignes de contact, chiffres clés, menu mobile ; 24 pour les cartes, blocs, photos, visionneuse ; squircle 30 % pour les carrés d'icône (44–48px).
- **Relief** : sans ombre. Le relief vient des aplats (`#F7F7F7` sur blanc) et des filets 1px. Exceptions : option active d'un segmented control `0 1px 2px rgba(0,0,0,0.08)` ; pilules flottantes mobile `0 4px 16px rgba(0,0,0,0.06–0.08)`.
- **Cartes** : blanc, filet `#EBEBEB`, rayon 24, photo carrée en haut (étiquette de catégorie en haut à gauche), contenu 20/24. Témoignages : rayon 16. Étapes : carte blanche à marge 8 avec photo 4/5 rayon 16.
- **Imagerie** : photos en vedette, format 4/5 (hero, artisane, étapes) ou carré (galerie), cover, coins 24, étiquette translucide. Portrait de l'artisane en **noir et blanc**. Pas de motif ni de dégradé.
- **Mouvement** : changement de fond 0,2 s (survol de carte → `#F7F7F7`, de ligne de contact → `#EBEBEB`, de bouton → couleur pressée) ; fondu 0,2 s (menu, visionneuse). Pas de lift, pas de scale, pas de rebond.
- **États** : focus des champs = anneau 2px encre (pas de bordure au repos) ; boutons désactivés à 50 % ; filtre actif = pilule blanche sur piste grise.
- **Layout** : mobile-first, grilles `auto-fit/auto-fill` (valeurs 260, pièces 270, artisan 320, étapes 220, avis 280). Sous 760px : burger dans la pilule, menu en lignes blanches, barre fixe WhatsApp / Écrire, hero en 2 colonnes (4 sur desktop). Cibles tactiles ≥ 44px.

## Iconographie
- Pas d'icon font. Un **petit jeu d'icônes filaires** (trait 1.8, extrémités rondes, 24×24, currentColor) : menu, close, check, arrow, handmade (flamme), quality (bouclier coché), whatsapp, instagram, facebook, mail, globe — exposé via `x-ds.icon`. Posées nues dans un squircle gris (valeurs) ou rouge (contact), dans un bouton rond (menu).
- Fallback autorisé pour de nouveaux besoins : **Lucide** (même graisse 1.8–2). Jamais d'icônes pleines ni d'emoji.
- **Logo** : aucun ; le nom en Hanken Grotesk 700 encre, 20px, tient lieu de marque.

## Polices
Hanken Grotesk 300 / 400 / 500 / 600 / 700, auto-hébergée via Bunny Fonts (`vite.config.js`). La maquette la charge depuis Google Fonts en variable.

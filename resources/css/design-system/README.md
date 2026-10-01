# Design system Tah'Pâtisserie — tokens

Copie conforme des fichiers `tokens/*.css` du design system Claude Design
(https://claude.ai/design/p/4b946b13-312a-4c84-8495-e04b779d1b7c).

**Ne pas modifier ces fichiers à la main.** Une évolution du design se fait dans
Claude Design, puis on recopie les fichiers ici. Les adaptations propres au site
(mapping Tailwind, classes de composants) vivent dans `resources/css/app.css`.

- `tokens/fonts.css` n'est pas repris : les polices sont auto-hébergées via
  `laravel-vite-plugin/fonts` (voir `vite.config.js`), pas chargées depuis Google Fonts.
- Attention : `--text-body` est défini deux fois dans le design system (couleur dans
  `colors.css`, taille dans `typography.css`) ; c'est la taille (16px) qui gagne.
  Pour la couleur du corps de texte, utiliser `--tp-plum-gray` (classe `text-ink-2`).

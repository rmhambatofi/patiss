<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>

# Design system

Toute page, vue Blade, composant ou style du site public suit le design system Tah'Pâtisserie (Claude Design : https://claude.ai/design/p/4b946b13-312a-4c84-8495-e04b779d1b7c).

- Lire `.claude/skills/tah-patisserie-design/SKILL.md` (et son `readme.md`) avant de créer ou modifier une page.
- Tokens : `resources/css/design-system/` (copie conforme, ne pas éditer à la main). Branchement Tailwind et classes : `resources/css/app.css`.
- Composants Blade `<x-ds.*>` dans `resources/views/components/ds/` : les utiliser avant d'écrire du balisage ; un besoin nouveau devient un composant `x-ds.*`.
- Pas de couleur, ombre, rayon ou taille en dur dans les vues (hex, `rgba()`, `text-[..px]`) : utiliser les utilitaires branchés sur les tokens.

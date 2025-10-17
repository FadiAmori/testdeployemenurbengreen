# UrbanGreen Laravel Application

UrbanGreen est une application Laravel qui expose l'interface du site vitrine et un back-office d'administration. Ce dépôt contient l'intégration Blade complète de la maquette ainsi que toutes les ressources frontales.

## Prérequis

- PHP 8.1+
- Composer
- Node.js 18+
- MySQL ou MariaDB (optionnel si vous n'utilisez pas la base de données)

## Installation

1. Cloner le dépôt et se placer dans le dossier `material-app`.
2. Installer les dépendances PHP :

   ```bash
   composer install
   ```

3. Créer votre fichier d'environnement et générer la clé d'application :

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configurer la base de données dans `.env`, puis lancer les migrations si nécessaire :

   ```bash
   php artisan migrate
   ```

5. Installer les dépendances Node (même si aucune compilation n'est requise) :

   ```bash
   npm install
   ```

## Lancer l'application

La commande de développement `npm run dev` lance directement le serveur Laravel. Une fois la commande exécutée, l'interface publique est accessible sur [http://localhost:5173](http://localhost:5173).

```bash
npm run dev
```

> Le serveur reste actif tant que la commande est en cours d'exécution. Utilisez `CTRL+C` pour l'arrêter.

Les ressources statiques sont désormais regroupées dans deux dossiers afin de séparer les fronts :

- `resources/front/client` contient les assets du site vitrine UrbanGreen, servis par Laravel via la route `/urbangreen/{path}` et utilisés par les vues Blade `resources/views/urbangreen`.
- `resources/front/admin` recense les assets du tableau de bord Material Dashboard. Ils sont exposés via `/assets/{path}` et alimentent les composants Blade situés dans `resources/views/dashboard`.

Les vues ont été regroupées par contexte :

- `resources/views/urbangreen/**/*` regroupe toutes les pages client (layout compris).
- `resources/views/dashboard/**/*` contient l'ensemble du front d'administration (pages, composants, écrans d'authentification).

Toutes les pages du site vitrine utilisent le layout `resources/views/layouts/urbangreen.blade.php`. Les pages de démonstration du back-office vivent quant à elles sous `/admin/*` (ex. `/admin/shop`).

## Générer une version optimisée

Pour optimiser l'application avant un déploiement, exécutez :

```bash
npm run build
```

Cette commande appelle `php artisan optimize` afin de mettre en cache la configuration et les routes.

## Tests

Vous pouvez exécuter la suite de tests Laravel avec :

```bash
php artisan test
```

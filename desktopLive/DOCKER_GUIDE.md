# Guide Docker - LiveShopping

## 🚀 Lancer le projet avec Docker

### Prérequis

- Docker et Docker Compose installés
- Make installé (optionnel mais recommandé)

### Démarrage rapide

```bash
# 1. Construire et démarrer tous les conteneurs
make up

# 2. Installer les dépendances et configurer le projet
make install

# 3. Accéder à l'application
# Ouvrez votre navigateur sur http://localhost:8000
```

### Commandes principales

#### Gestion des conteneurs

```bash
make up          # Démarrer tous les conteneurs
make down        # Arrêter tous les conteneurs
make logs        # Voir les logs des conteneurs
make shell       # Accéder au shell du conteneur PHP
make shell-db    # Accéder au shell du conteneur PostgreSQL
```

#### Installation et configuration

```bash
make install              # Installation complète du projet
make composer-install     # Installer les dépendances Composer
make npm-install          # Installer les dépendances npm
make cache-warmup         # Réchauffer le cache Symfony
make assets               # Installer les assets
```

#### Base de données

```bash
make migrate              # Appliquer les migrations
make migration-diff      # Créer une nouvelle migration
make schema_update       # Mettre à jour le schéma de base de données
make psql                # Accéder à PostgreSQL
```

#### Commandes Symfony

```bash
make sf cache:clear      # Vider le cache
make sf list             # Lister toutes les commandes Symfony
make sf <command>        # Exécuter une commande Symfony
```

### Architecture Docker

Le projet utilise 3 conteneurs :

1. **nginx** : Serveur web (port 8000)
2. **php** : PHP-FPM avec toutes les extensions nécessaires
3. **database** : PostgreSQL 16

### Configuration

#### Variables d'environnement

Créez un fichier `.env` à la racine du projet avec :

```env
POSTGRES_DB=app
POSTGRES_USER=app
POSTGRES_PASSWORD=!ChangeMe!
POSTGRES_VERSION=16
APP_ENV=dev
APP_SECRET=change-me-in-production
NGINX_PORT=8000
```

#### Ports

- **Nginx** : 8000 (modifiable via `NGINX_PORT`)
- **PostgreSQL** : 5432 (interne au réseau Docker)

### Dépannage

#### Les conteneurs ne démarrent pas

```bash
# Vérifier les logs
make logs

# Vérifier l'état des conteneurs
docker compose -p liveshopping -f compose.yaml ps

# Reconstruire les images
docker compose -p liveshopping -f compose.yaml build --no-cache
```

#### Problèmes de permissions

```bash
make fix-permissions
```

#### Réinitialiser complètement

```bash
make down
docker compose -p liveshopping -f compose.yaml down -v
make hard-reset
make up
make install
```

#### Accéder à la base de données

```bash
# Via make
make psql

# Directement
docker compose -p liveshopping -f compose.yaml exec database psql -U app -d app
```

### Structure des fichiers Docker

```
docker/
├── nginx/
│   ├── Dockerfile
│   └── default.conf
└── php/
    ├── Dockerfile
    └── php.ini
```

### Commandes utiles

```bash
# Voir toutes les commandes disponibles
make help

# Voir les logs d'un service spécifique
docker compose -p liveshopping -f compose.yaml logs -f php
docker compose -p liveshopping -f compose.yaml logs -f nginx
docker compose -p liveshopping -f compose.yaml logs -f database

# Redémarrer un service spécifique
docker compose -p liveshopping -f compose.yaml restart php

# Exécuter une commande dans le conteneur PHP
docker compose -p liveshopping -f compose.yaml exec php composer install
docker compose -p liveshopping -f compose.yaml exec php php bin/console cache:clear
```

### Production

Pour la production, assurez-vous de :

1. Changer tous les mots de passe par défaut
2. Modifier `APP_SECRET` avec une valeur sécurisée
3. Définir `APP_ENV=prod`
4. Configurer les volumes pour la persistance des données
5. Utiliser un reverse proxy (Traefik, Nginx, etc.)
6. Configurer SSL/TLS


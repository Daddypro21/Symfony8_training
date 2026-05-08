# Symfony 8 — Projet de formation & préparation à la certification

Ce projet est un environnement d'apprentissage complet pour préparer la **certification officielle Symfony 8**.
Il contient des exemples de code commentés, un cours théorique évolutif et une structure organisée notion par notion, en suivant le programme officiel de l'examen.

- Référence officielle de l'examen : https://certification.symfony.com/exams/symfony.html
- Cours théorique (mis à jour en continu) : [`cours-symfony-certification.md`](./cours-symfony-certification.md)
- Programme complet des notions : [`symfony.certification.md`](./symfony.certification.md)
- Exemples de code : `src/Learning/`

---

## Prérequis — Ce qu'il faut avoir sur sa machine

### Outils obligatoires

| Outil | Version minimale | Vérification |
|---|---|---|
| **PHP** | 8.4+ | `php -v` |
| **Composer** | 2.x | `composer -V` |
| **Symfony CLI** | dernière version | `symfony version` |
| **Docker Desktop** | 4.x | `docker -v` |
| **Docker Compose** | v2.x (intégré à Docker) | `docker compose version` |
| **Git** | 2.x | `git --version` |

### Outils recommandés

| Outil | Utilité |
|---|---|
| **PHPStorm** ou **VS Code** | IDE avec support Symfony/PHP |
| **Extension Symfony pour VS Code** | Autocomplétion, debug |
| **TablePlus** ou **DBeaver** | Interface graphique pour PostgreSQL |

---

## Installation & démarrage

### 1. Cloner le projet

```bash
git clone https://github.com/<votre-utilisateur>/training_project.git
cd training_project
```

### 2. Installer les dépendances PHP

```bash
composer install
```

> Cela installe les packages, vide le cache, installe les assets et l'importmap automatiquement.

### 3. Configurer les variables d'environnement

Créer un fichier `.env.local` à la racine pour surcharger les valeurs sans toucher à `.env` :

```bash
cp .env .env.local
```

Ouvrir `.env.local` et définir au minimum :

```dotenv
APP_SECRET=une_chaine_aleatoire_de_32_caracteres

# Base de données (garder PostgreSQL ou choisir SQLite pour simplifier)
# Option 1 : PostgreSQL via Docker (recommandé)
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"

# Option 2 : SQLite (aucune installation requise)
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_dev.db"
```

### 4. Démarrer les services Docker

```bash
docker compose up -d
```

Cela démarre :
- **PostgreSQL 16** — base de données principale (port `5432`)
- **Mailpit** — serveur SMTP de dev pour les emails (SMTP `1025`, interface web `8025`)

> Vérifier que les conteneurs tournent : `docker compose ps`

### 5. Créer la base de données et les tables

```bash
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
```

### 6. Démarrer le serveur de développement

```bash
symfony serve
```

L'application est accessible sur `https://localhost:8000`.

---

## Structure du projet

```
training_project/
├── src/
│   ├── Learning/           ← Exemples de code notions par notions
│   │   └── PHP/
│   │       ├── 01_ModernPHP.php
│   │       ├── 02_OOP.php
│   │       ├── 03_Attributes.php
│   │       └── ...
│   ├── Controller/         ← Contrôleurs Symfony (auto-découverte)
│   ├── Entity/             ← Entités Doctrine
│   └── Repository/         ← Repositories Doctrine
├── config/
│   ├── packages/           ← Configuration des bundles (YAML)
│   └── services.yaml       ← Déclaration des services
├── templates/              ← Templates Twig
├── assets/                 ← JS / CSS (Stimulus, Turbo — Asset Mapper)
├── migrations/             ← Migrations Doctrine
├── tests/                  ← Tests PHPUnit
├── cours-symfony-certification.md   ← Cours théorique complet
├── symfony.certification.md         ← Programme officiel de l'examen
└── compose.yaml            ← Services Docker (PostgreSQL, Mailpit)
```

---

## Commandes utiles au quotidien

### Développement

```bash
symfony serve                          # Démarrer le serveur dev
symfony serve -d                       # En arrière-plan
symfony server:stop                    # Arrêter le serveur
docker compose up -d                   # Démarrer Docker en arrière-plan
docker compose down                    # Arrêter Docker
```

### Base de données

```bash
symfony console doctrine:database:create              # Créer la base
symfony console doctrine:migrations:migrate           # Appliquer les migrations
symfony console doctrine:migrations:generate          # Générer une migration
symfony console doctrine:schema:validate              # Valider le mapping
symfony console doctrine:fixtures:load                # Charger les fixtures (si installées)
```

### Génération de code (MakerBundle)

```bash
symfony console make:controller NomControleur
symfony console make:entity NomEntite
symfony console make:migration
symfony console make:form NomType
symfony console make:command app:nom-commande
symfony console make:test NomTest
```

### Debug

```bash
symfony console debug:router                   # Lister toutes les routes
symfony console debug:autowiring               # Lister les services autowirables
symfony console debug:container                # Lister tous les services
symfony console debug:config framework         # Voir la config résolue d'un bundle
symfony console debug:twig                     # Lister les fonctions/filtres Twig
symfony console debug:asset-map                # Lister les assets mappés
```

### Tests

```bash
./bin/phpunit                                           # Tous les tests
./bin/phpunit tests/Unit/MonTest.php                    # Un seul fichier
./bin/phpunit --filter nomDuTest                        # Un seul test par nom
./bin/phpunit --coverage-html coverage/                 # Avec rapport de couverture
```

### Assets (Asset Mapper — pas de npm/webpack)

```bash
symfony console importmap:require package-name         # Ajouter un package JS
symfony console importmap:install                       # Réinstaller tous les packages
symfony console asset-map:compile                       # Compiler pour la prod
```

---

## Configuration de l'environnement

### Variables d'environnement principales

| Variable | Valeur par défaut | Description |
|---|---|---|
| `APP_ENV` | `dev` | Environnement (`dev`, `test`, `prod`) |
| `APP_SECRET` | *(vide)* | Clé secrète — **obligatoire**, à définir dans `.env.local` |
| `DATABASE_URL` | PostgreSQL local | URL de connexion à la base |
| `MESSENGER_TRANSPORT_DSN` | `doctrine://default` | Transport pour les messages async |
| `MAILER_DSN` | `null://null` | Transport email (Mailpit en dev) |

### Fichiers d'environnement

```
.env              ← Valeurs par défaut (committé)
.env.local        ← Surcharges locales (non committé, à créer)
.env.dev          ← Spécifique à APP_ENV=dev (committé)
.env.test         ← Spécifique aux tests (committé)
.env.prod.local   ← Secrets de production (non committé, sur le serveur)
```

### Bases de données alternatives

Si vous ne voulez pas utiliser Docker, vous pouvez utiliser **SQLite** (aucune installation requise) :

```dotenv
# Dans .env.local
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_dev.db"
```

---

## Stack technique complète

| Composant | Technologie | Version |
|---|---|---|
| Langage | PHP | 8.4+ |
| Framework | Symfony | 8.0.* |
| ORM | Doctrine ORM | 3.6 |
| Migrations | Doctrine Migrations | 4.0 |
| Templates | Twig | 3.x |
| Frontend JS | Stimulus + Turbo (Hotwired) | 3.x / 8.x |
| Assets | Symfony Asset Mapper | 8.0.* |
| Base de données | PostgreSQL | 16 |
| Tests | PHPUnit | 13.1 |
| Conteneurs | Docker + Docker Compose | v2 |
| Emails (dev) | Mailpit | dernière version |
| Serveur dev | Symfony CLI (FrankenPHP) | dernière version |

> **Pas de Node.js / npm / webpack** : le projet utilise l'Asset Mapper natif de Symfony, qui gère les modules ES directement via CDN et importmap.

---

## Programme de la certification

Les notions étudiées sont organisées dans [`symfony.certification.md`](./symfony.certification.md) et couvrent :

- **PHP** : API PHP 8.4, OOP, Attributes, Interfaces, Closures, Abstract, Exceptions, Traits, Enums
- **HTTP** : RFC 9110, méthodes, status codes, cookies, cache, négociation de contenu
- **Architecture Symfony** : HttpFoundation, Flex, Events, PSRs, Best Practices
- **Controllers, Routing, Twig, Forms, Validation**
- **Dependency Injection, Security, Messenger, Console**
- **Tests automatisés, Cache, Mailer, Serializer, et plus**

La progression est suivie dans [`cours-symfony-certification.md`](./cours-symfony-certification.md).

---

## Résolution des problèmes courants

**`symfony: command not found`**
→ Installer la Symfony CLI : https://symfony.com/download

**`composer install` échoue sur ext-ctype / ext-iconv**
→ Activer ces extensions dans `php.ini` (décommenter les lignes `extension=ctype`, `extension=iconv`)

**La base de données ne démarre pas**
→ Vérifier que Docker Desktop est lancé, puis : `docker compose down && docker compose up -d`

**Port 5432 déjà utilisé**
→ Une instance PostgreSQL locale tourne déjà. Modifier le port dans `compose.override.yaml` ou arrêter le service local.

**`APP_SECRET` manquant**
→ Créer `.env.local` et y définir `APP_SECRET=<32_caracteres_aleatoires>`
→ Générer une valeur : `openssl rand -hex 16` ou `symfony secret:generate`

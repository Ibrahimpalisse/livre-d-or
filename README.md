# Livre d'Or

Une application web de partage et de gestion de commentaires autour des lectures, mangas, manhwas et animés. Elle propose une interface conviviale permettant aux utilisateurs de laisser des messages, de consulter les avis des autres membres et de gérer leur propre compte.

![Livre d'Or Banner](public/images/logo.png)

## Table des matières

- [Présentation](#présentation)
- [Fonctionnalités](#fonctionnalités)
- [Technologies utilisées](#technologies-utilisées)
- [Installation](#installation)
  - [Prérequis](#prérequis)
  - [Installation avec Docker](#installation-avec-docker)
  - [Installation manuelle](#installation-manuelle)
- [Configuration](#configuration)
- [Structure du projet](#structure-du-projet)
- [Guide d'utilisation](#guide-dutilisation)
- [Sécurité](#sécurité)
- [Contribution](#contribution)
- [Préparation à la production](#préparation-à-la-production)

## Présentation

**Livre d'Or** est une application web permettant aux utilisateurs de partager leurs avis et commentaires sur différents types d'œuvres (romans, manhwas, animés). Le projet a été développé dans le cadre d'une formation Développeur Web et Web Mobile (DWWM), mettant en application des compétences en développement web, gestion de bases de données et conception d'interfaces utilisateur.

## Fonctionnalités

### Utilisateurs
- Inscription et connexion sécurisées
- Consultation des publications (filtrage par type et recherche)
- Ajout de commentaires sur les publications
- Évaluation des publications (validation/invalidation)
- Gestion du profil utilisateur

### Administrateurs
- Gestion des utilisateurs (modification des rôles, désactivation de comptes)
- Création, modification et suppression de publications
- Modération des commentaires
- Accès au tableau de bord d'administration

### Super Administrateurs
- Toutes les fonctionnalités des administrateurs
- Gestion complète des rôles utilisateurs
- Configuration avancée de l'application

## Technologies utilisées

### Back-end
- PHP 8.2 (framework MVC personnalisé)
- MongoDB (base de données NoSQL)
- JWT (JSON Web Tokens) pour l'authentification

### Front-end
- HTML5, CSS3, JavaScript
- Bootstrap 5 (framework CSS)
- AJAX pour les requêtes asynchrones

### Environnement
- Docker & docker-compose
- Apache
- Composer (gestion des dépendances PHP)

## Installation

### Prérequis
- [Docker](https://www.docker.com/products/docker-desktop) et [docker-compose](https://docs.docker.com/compose/install/)
- Ou XAMPP/WAMP/MAMP pour une installation locale
- [Composer](https://getcomposer.org/download/)
- [Git](https://git-scm.com/downloads) (optionnel)

### Installation avec Docker

1. Cloner le dépôt (ou télécharger l'archive)
   ```bash
   git clone https://github.com/votre-username/livre-d-or.git
   cd livre-d-or
   ```

2. Créer le fichier de configuration `.env` à partir de l'exemple
   ```bash
   cp .env-example .env
   ```

3. Modifier les valeurs dans le fichier `.env` selon votre environnement

4. Lancer les conteneurs Docker
   ```bash
   docker-compose up -d
   ```

5. Installer les dépendances PHP
   ```bash
   docker exec -it php-apache composer install
   ```

6. Accéder à l'application
   - Frontend: http://localhost:8080
   - Interface Mongo Express: http://localhost:8081 (username: ibra, password: voir docker-compose.yml)

### Installation manuelle

1. Cloner le dépôt dans votre répertoire web (www, htdocs, etc.)
   ```bash
   git clone https://github.com/votre-username/livre-d-or.git
   cd livre-d-or
   ```

2. Installer les dépendances via Composer
   ```bash
   composer install
   ```

3. Configurer la connexion MongoDB
   - Créer une base de données MongoDB "livre_d_or"
   - Copier `.env-example` vers `.env` et configurer les paramètres de connexion

4. Configurer le serveur web (Apache)
   - Assurer que `mod_rewrite` est activé
   - Configurer le VirtualHost pour pointer vers le répertoire du projet

5. Accéder à l'application via votre navigateur

## Configuration

Le fichier `.env` contient les paramètres de configuration essentiels :

```env
# Paramètres de l'application
APP_NAME="Livre d'or"
APP_DEBUG=false
APP_URL=http://localhost:8080

# Configuration MongoDB
MONGODB_HOST=mongo
MONGODB_PORT=27017
MONGODB_DATABASE=livre_d_or
MONGODB_USERNAME=dbuser
MONGODB_PASSWORD=securepwd123

# Configuration JWT pour l'authentification
JWT_SECRET=your_secret_key_at_least_32_chars_long
JWT_ALGORITHM=HS256
JWT_EXPIRATION=3600

# URI MongoDB direct (alternative)
MONGO_URI=mongodb://username:password@mongo:27017/livre_d_or?authSource=admin
```

## Structure du projet

```
livre-d-or/
├── public/                # Fichiers accessibles publiquement
│   ├── css/               # Feuilles de style CSS
│   ├── js/                # Scripts JavaScript
│   └── images/            # Images du site
├── src/
│   ├── controllers/       # Contrôleurs PHP (logique métier)
│   │   ├── AuthController.php
│   │   ├── CommentController.php
│   │   ├── PublicationController.php
│   │   └── SuperAdminController.php
│   ├── core/              # Classes de base
│   │   ├── Auth.php       # Gestion de l'authentification
│   │   ├── Config.php     # Accès à la configuration
│   │   ├── Database.php   # Connexion à MongoDB
│   │   ├── Env.php        # Gestion des variables d'environnement
│   │   ├── Router.php     # Routage des requêtes
│   │   └── Security.php   # Fonctions de sécurité (CSRF, etc.)
│   ├── models/            # Modèles pour MongoDB
│   │   ├── User.php
│   │   ├── Publication.php
│   │   └── Comment.php
│   ├── views/             # Vues PHP (templates)
│   │   ├── dashboard.php
│   │   ├── error.php
│   │   ├── home.php
│   │   ├── layout.php
│   │   ├── login.php
│   │   └── register.php
│   └── config/            # Configuration de l'application
│       └── config.php
├── vendor/                # Dépendances PHP (générées par Composer)
├── Dockerfile             # Configuration de l'image Docker
├── docker-compose.yml     # Configuration des services Docker
├── .env-example           # Exemple de configuration
├── .htaccess              # Configuration Apache
├── composer.json          # Dépendances du projet
├── index.php              # Point d'entrée de l'application
└── README.md              # Documentation
```

## Guide d'utilisation

### Utilisateurs

1. **Inscription/Connexion**
   - Visitez la page d'accueil et cliquez sur "S'inscrire" ou "Se connecter"
   - Suivez les instructions pour créer un compte ou vous connecter

2. **Consultation des publications**
   - Parcourez les publications sur la page d'accueil
   - Utilisez les filtres pour afficher uniquement certains types (romans, manhwas, animés)
   - Utilisez la barre de recherche pour trouver des publications spécifiques

3. **Interaction avec les publications**
   - Cliquez sur "Commentaires" pour lire ou ajouter un commentaire
   - Utilisez les boutons "Valider" ou "Ne pas valider" pour évaluer une publication
   - Cliquez sur "Liens" pour accéder aux sources de la publication

### Administrateurs

1. **Accès au tableau de bord**
   - Connectez-vous avec un compte administrateur
   - Vous serez automatiquement redirigé vers le tableau de bord

2. **Gestion des publications**
   - Créez de nouvelles publications via le formulaire
   - Modifiez ou supprimez des publications existantes
   - Gérez les commentaires des utilisateurs

3. **Gestion des utilisateurs** (Super Admin uniquement)
   - Visualisez tous les utilisateurs inscrits
   - Modifiez les rôles des utilisateurs
   - Désactivez les comptes problématiques

## Sécurité

L'application intègre plusieurs mesures de sécurité :

- **Authentification** : JWT (JSON Web Tokens) pour sécuriser les sessions
- **Protection CSRF** : Tokens générés pour toutes les actions sensibles
- **Hashage des mots de passe** : Utilisation des fonctions sécurisées de PHP
- **Validation des entrées** : Contrôle strict des données soumises par les utilisateurs
- **Cookies sécurisés** : Configuration HttpOnly, SameSite et Secure (en HTTPS)
- **Gestion des rôles** : Contrôle d'accès basé sur les rôles utilisateurs

## Nettoyage du code

Avant de déployer en production, assurez-vous de :

1. Supprimer tous les `console.log()` dans les fichiers JavaScript
2. Retirer les appels à `var_dump()` et `print_r()` dans le PHP
3. Désactiver le mode débogage dans le fichier `.env` (APP_DEBUG=false)
4. Vérifier que les informations sensibles ne sont pas exposées

## Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Forker le dépôt
2. Créer une branche pour votre fonctionnalité (`git checkout -b nouvelle-fonctionnalite`)
3. Commiter vos changements (`git commit -am 'Ajout d'une nouvelle fonctionnalité'`)
4. Pousser vers la branche (`git push origin nouvelle-fonctionnalite`)
5. Créer une Pull Request

## Préparation à la production

Pour préparer l'application au déploiement en production, les opérations suivantes ont été effectuées :

1. **Suppression des instructions de débogage** :
   - Suppression de tous les `console.log()` dans les fichiers JavaScript
   - Vérification de l'absence de `var_dump()` et `print_r()` dans les fichiers PHP
   - Conservation uniquement des messages d'erreur pertinents

2. **Amélioration des messages d'erreur** :
   - Remplacement des `alert()` par des fonctions de notification plus élégantes
   - Utilisation des logs serveur pour enregistrer les erreurs importantes
   - Affichage de messages d'erreur conviviaux pour l'utilisateur

3. **Optimisation du code** :
   - Regroupement des fonctionnalités similaires dans des fonctions dédiées
   - Suppression du code redondant
   - Amélioration de la lisibilité et de la maintenabilité

4. **Sécurité renforcée** :
   - Vérification et configuration correcte de toutes les variables d'environnement
   - Protection des formulaires avec des tokens CSRF
   - Validation stricte des entrées utilisateur

5. **Configuration de l'environnement** :
   - Désactivation du mode débogage (`APP_DEBUG=false` dans le fichier `.env`)
   - Configuration de la base de données pour la production
   - Documentation des étapes de déploiement


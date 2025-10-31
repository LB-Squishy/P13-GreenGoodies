[![forthebadge](https://forthebadge.com/images/badges/powered-by-coffee.svg)](https://forthebadge.com)

# Mettez en place un site de e-commerce avec Symfony

## Contenu:

Vous êtes développeur PHP/Symfony en freelance. GreenGoodies, une boutique lyonnaise spécialisée dans la vente de produits biologiques, éthiques et écologiques, souhaite élargir sa cible commerciale.
Vous êtes en contact avec Aurélie, la gérante de la boutique. Elle a déjà les maquettes de son futur site en sa possession et vous demande de développer le site en question.

Le site doit permettre :

-   la consultation des produits
-   la création de compte utilisateur
-   la gestion d'un panier et la validation de celui ci pour passage en commande
-   la consultation de l'historique de commandes
-   un accès API optionnel pour les utilisateurs authentifiés

Projet réalisé en se basant sur une maquette fourni et dans une démarche "green code".

## 📋 Prérequis

Avant d'installer le projet, assurez-vous d'avoir :

-   **PHP 8.3** ou supérieur
-   **Composer** (gestionnaire de dépendances PHP)
-   **MySQL** ou **MariaDB**
-   **Git**
-   **OpenSSL** (pour la génération des clés JWT)

## 🚀 Installation

### 1. Cloner le projet

```bash
git clone https://github.com/votre-username/EcoGardenApi.git
cd EcoGardenApi
```

### 2. Préparez le fichier `.env.local`

Créez un fichier .env.local et configurez vos variables d'environnement

```
DATABASE_URL="mysql://root:motdepasse@127.0.0.1:3306/greengoodies?serverVersion=9.1"
```

### 3. Installer les dépendances

```bash
composer install
```

### 4. Configuration de la base de données

Créez la base de données :

```bash
php bin/console doctrine:database:create --if-not-exists
```

Appliquez les migrations :

```bash
php bin/console doctrine:migrations:migrate
```

Alimentez la base de donnée:

```bash
php bin/console doctrine:fixtures:load
```

### 5. Configuration JWT (Authentification)

Créez le dossier pour les clés JWT :

```bash
mkdir config/jwt
```

Générez les clés privée et publique :

```bash
# Clé privée (vous devrez saisir une passphrase)
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096

# Clé publique
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

Ajoutez la passphrase dans votre fichier `.env.local` :

```env
JWT_PASSPHRASE=votre_passphrase_ici
```

### 6. Démarrer le serveur de développement

```bash
symfony server:start
```

### 7. Connectez-vous

-   Inscription possible directement sur le site.

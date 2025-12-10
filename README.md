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

### 2. Installer les dépendances

```bash
composer install
```

### 3. Générer les clés JWT

Créez un dossier `jwt` dans le dossier `config` puis générez les clés

Clé privée:

```bash
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
```

Clé publique:

```bash
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

### 4. Préparez le fichier `.env.local`

Créez un fichier .env.local et configurez vos variables d'environnement

```env
DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/GreenGoodies?serverVersion=9.1.0&charset=utf8mb4"
APP_SECRET=ta_clé_secret
JWT_PASSPHRASE=ta_passphrase
```

### 5. Configuration de la base de données

Créez la base de données et l'alimenter :

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### 6. Compiler les assets (CSS/JS)

    Si tu utilises AssetMapper (par défaut Symfony 6.3+) :

    ```bash
    php bin/console asset-map:compile
    ```

    > Le CSS sera généré dans `public/assets/` et utilisable en dev comme en prod.

### 7. Démarrer le serveur de développement

```bash
symfony server:start
# ou
php -S localhost:8000 -t public
```

### 8. Connectez-vous

-   Ouvre [http://localhost:8000](http://localhost:8000) dans ton navigateur.
-   Inscription possible directement sur le site.

## 🔄 Tester en production

### 1. Modifie la variable d’environnement dans `.env.local`

```env
APP_ENV=prod
```

### 2. Vide le cache et compile les assets en prod :

```bash
php bin/console cache:clear --env=prod
php bin/console asset-map:compile --env=prod
```

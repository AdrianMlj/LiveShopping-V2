# 🌩️ CLOUDINARY - Par où commencer ?

> **Bienvenue dans votre intégration Cloudinary !**  
> Ce guide vous indique exactement quoi faire, étape par étape.

---

## 🎯 Objectif

Remplacer le stockage local d'images (`/public/uploads`) par **Cloudinary**, un service cloud professionnel pour :
- ✅ Héberger vos images
- ✅ Optimiser automatiquement (compression, format)
- ✅ Livrer via CDN ultra-rapide
- ✅ Redimensionner à la volée
- ✅ Économiser de l'espace serveur

---

## ⚡ Démarrage ultra-rapide (15 minutes)

### Option 1 : Installation automatique (Recommandé)

```bash
# Windows PowerShell
.\install_cloudinary.ps1

# Linux / Mac
chmod +x install_cloudinary.sh
./install_cloudinary.sh
```

**Puis** : Suivez [INSTALLATION_RAPIDE.md](INSTALLATION_RAPIDE.md)

### Option 2 : Manuellement (si le script ne fonctionne pas)

**Étape 1** : Installer le package
```bash
composer require cloudinary/cloudinary_php
```

**Étape 2** : Configurer `.env.local`  
👉 Voir [CREATE_ENV_LOCAL.md](CREATE_ENV_LOCAL.md)

**Étape 3** : Tester  
👉 Voir [CLOUDINARY_QUICK_TEST.md](CLOUDINARY_QUICK_TEST.md)

---

## 📋 Étapes complètes (dans l'ordre)

### 1️⃣ Installation (5 min)

**Fichier** : [INSTALLATION_RAPIDE.md](INSTALLATION_RAPIDE.md)

- Installer le package Cloudinary
- Créer `.env.local`
- Vider le cache

**Commandes** :
```bash
composer require cloudinary/cloudinary_php
php bin/console cache:clear
```

---

### 2️⃣ Configuration (5 min)

**Fichier** : [CREATE_ENV_LOCAL.md](CREATE_ENV_LOCAL.md)

- Créer un compte Cloudinary gratuit
- Récupérer `CLOUDINARY_URL`
- Configurer `.env.local`

**Résultat** :
```bash
# .env.local
CLOUDINARY_URL=cloudinary://123456789:abcdefg@your-cloud-name
```

---

### 3️⃣ Test (2 min)

**Fichier** : [CLOUDINARY_QUICK_TEST.md](CLOUDINARY_QUICK_TEST.md)

- Démarrer le serveur
- Tester l'upload sur `/test/cloudinary/upload-form`
- Vérifier que l'image apparaît

**Commandes** :
```bash
symfony serve
# Ouvrir : http://localhost:8000/test/cloudinary/upload-form
```

**✅ Si ça marche, continuez !**

---

### 4️⃣ Migration Base de Données (10 min)

**Fichier** : [CLOUDINARY_DATABASE_MIGRATION.md](CLOUDINARY_DATABASE_MIGRATION.md)

- Sauvegarder la base de données
- Agrandir les colonnes `images` (VARCHAR(500))
- Vérifier les modifications

**Commandes** :
```bash
# Sauvegarde
mysqldump -u user -p base > backup.sql

# Migration
mysql -u user -p base < migrations/CloudinaryUrlsMigration.sql
```

**Pourquoi ?** Les URLs Cloudinary sont plus longues que les noms de fichiers locaux.

---

### 5️⃣ Intégration dans le code (30-60 min)

**Fichier** : [CLOUDINARY_SETUP_GUIDE.md](CLOUDINARY_SETUP_GUIDE.md)

**À modifier** :

#### A. Contrôleurs

Exemple : `src/Controller/ProfilAdminController.php`

```php
// Avant (local)
$imageFile->move($this->getParameter('uploads_directory'), $imageName);
$user->setImages($imageName);

// Après (Cloudinary)
$imageUrl = $cloudinaryService->uploadImageResized($imageFile, 'users/profiles', 500, 500);
$user->setImages($imageUrl);
```

**Voir l'exemple complet** : `src/Controller/ProfilAdminController_Cloudinary_Example.php`

#### B. Templates Twig

```twig
{# Avant (local uniquement) #}
<img src="/uploads/{{ user.images }}" alt="Profile">

{# Après (local + Cloudinary) #}
{% if user.images starts with 'http' %}
    <img src="{{ user.images }}" alt="Profile">
{% else %}
    <img src="/uploads/{{ user.images }}" alt="Profile">
{% endif %}
```

**Fichiers à modifier** :
- ✅ `ProfilAdminController.php`
- ✅ `ProfilClientController.php`
- ✅ Contrôleurs qui uploadent des images d'articles
- ✅ Templates qui affichent des images

---

## 📁 Fichiers créés pour vous

### 📚 Documentation

| Fichier | Quand l'utiliser ? |
|---------|-------------------|
| **[START_HERE.md](START_HERE.md)** | Ce fichier - Commencez ici |
| **[INSTALLATION_RAPIDE.md](INSTALLATION_RAPIDE.md)** | Installation automatique |
| **[CREATE_ENV_LOCAL.md](CREATE_ENV_LOCAL.md)** | Configurer Cloudinary |
| **[CLOUDINARY_QUICK_TEST.md](CLOUDINARY_QUICK_TEST.md)** | Tester en 5 min |
| **[CLOUDINARY_DATABASE_MIGRATION.md](CLOUDINARY_DATABASE_MIGRATION.md)** | Migrer la base |
| **[CLOUDINARY_SETUP_GUIDE.md](CLOUDINARY_SETUP_GUIDE.md)** | Guide complet |
| **[CLOUDINARY_README.md](CLOUDINARY_README.md)** | Vue d'ensemble |
| **[CLOUDINARY_FILES_SUMMARY.md](CLOUDINARY_FILES_SUMMARY.md)** | Liste complète |

### 🔧 Code

| Fichier | Description |
|---------|-------------|
| `src/Service/CloudinaryService.php` | Service Cloudinary (prêt à l'emploi) |
| `src/Controller/CloudinaryTestController.php` | Contrôleur de test |
| `src/Controller/ProfilAdminController_Cloudinary_Example.php` | Exemple d'intégration |
| `templates/test/cloudinary_test.html.twig` | Interface de test |
| `migrations/CloudinaryUrlsMigration.sql` | Migration SQL |
| `config/services.yaml` | Configuration (déjà modifié) |

### 🧪 Tests

| Fichier | Description |
|---------|-------------|
| `Cloudinary_Tests.postman_collection.json` | Collection Postman |

### 🚀 Scripts

| Fichier | Description |
|---------|-------------|
| `install_cloudinary.sh` | Installation automatique (Linux/Mac) |
| `install_cloudinary.ps1` | Installation automatique (Windows) |

---

## 🧭 Parcours recommandé

### Pour les pressés (30 min)

```
1. ⚡ INSTALLATION_RAPIDE.md
2. 🧪 CLOUDINARY_QUICK_TEST.md
3. 🗄️ CLOUDINARY_DATABASE_MIGRATION.md
4. ✅ Tester en production
```

### Pour une intégration complète (2h)

```
1. 📖 Lire CLOUDINARY_README.md (vue d'ensemble)
2. ⚡ INSTALLATION_RAPIDE.md (installation)
3. 🧪 CLOUDINARY_QUICK_TEST.md (test)
4. 🗄️ CLOUDINARY_DATABASE_MIGRATION.md (BDD)
5. 📚 CLOUDINARY_SETUP_GUIDE.md (intégration complète)
6. 🧹 Nettoyer les fichiers de test
```

---

## 🧪 Test rapide (2 minutes)

```bash
# 1. Démarrer le serveur
symfony serve

# 2. Ouvrir dans le navigateur
http://localhost:8000/test/cloudinary/upload-form

# 3. Uploader une image

# 4. ✅ Si vous voyez l'image avec une URL Cloudinary, c'est bon !
```

---

## 💡 Ce que vous devez savoir

### Format des URLs

**Local (ancien)** :
```
tshirt_noir.jpg
```

**Cloudinary (nouveau)** :
```
https://res.cloudinary.com/your-cloud/image/upload/v1234567890/items/tshirt_noir.jpg
```

### Compatibilité

Votre code peut gérer **les deux formats** :

```php
if (str_starts_with($image, 'http')) {
    // C'est une URL Cloudinary
} else {
    // C'est un ancien fichier local
}
```

Comme ça, vous pouvez migrer progressivement !

---

## 🎁 Fonctionnalités du service

```php
use App\Service\CloudinaryService;

// Upload simple
$url = $cloudinaryService->uploadImage($file, 'folder_name');

// Upload avec redimensionnement
$url = $cloudinaryService->uploadImageResized($file, 'items', 500, 500);

// Supprimer une image
$cloudinaryService->deleteImage($imageUrl);

// Vérifier si c'est une URL Cloudinary
if ($cloudinaryService->isCloudinaryUrl($url)) {
    // ...
}
```

---

## ✅ Checklist finale

### Installation
- [ ] Package `cloudinary/cloudinary_php` installé
- [ ] Fichier `.env.local` créé
- [ ] `CLOUDINARY_URL` configuré
- [ ] Cache Symfony vidé

### Test
- [ ] Interface de test accessible
- [ ] Upload d'image réussi
- [ ] Image visible avec URL Cloudinary

### Base de données
- [ ] Sauvegarde effectuée
- [ ] Migration SQL exécutée
- [ ] Colonnes agrandies (VARCHAR(500))

### Intégration
- [ ] ProfilAdminController modifié
- [ ] Autres contrôleurs modifiés
- [ ] Templates Twig mis à jour
- [ ] Tests manuels réussis

### Production
- [ ] Configuration serveur de production
- [ ] Tests en production réussis
- [ ] Monitoring des quotas Cloudinary

---

## 🆘 Besoin d'aide ?

### Problème d'installation
👉 [INSTALLATION_RAPIDE.md](INSTALLATION_RAPIDE.md) - Section "Problèmes"

### Problème de configuration
👉 [CREATE_ENV_LOCAL.md](CREATE_ENV_LOCAL.md) - Section "Problèmes courants"

### Problème de test
👉 [CLOUDINARY_QUICK_TEST.md](CLOUDINARY_QUICK_TEST.md) - Section "Problèmes courants"

### Problème d'intégration
👉 [CLOUDINARY_SETUP_GUIDE.md](CLOUDINARY_SETUP_GUIDE.md) - Section "Dépannage"

---

## 🎯 Action suivante

**👉 Commencez maintenant : [INSTALLATION_RAPIDE.md](INSTALLATION_RAPIDE.md)**

```bash
# Windows
.\install_cloudinary.ps1

# Linux / Mac
./install_cloudinary.sh
```

---

## 📞 Support

- 📖 [Documentation Cloudinary](https://cloudinary.com/documentation/php_integration)
- 🌐 [Dashboard Cloudinary](https://console.cloudinary.com/console/)
- 💬 [Support Cloudinary](https://support.cloudinary.com/)

---

**🎉 Bonne intégration ! Cloudinary va transformer votre gestion d'images !**



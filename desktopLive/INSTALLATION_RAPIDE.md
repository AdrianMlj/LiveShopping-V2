# ⚡ Installation Rapide Cloudinary

## 🚀 Installation en 1 commande

### Windows (PowerShell)

```powershell
# Exécuter le script d'installation
.\install_cloudinary.ps1
```

Si vous avez une erreur de politique d'exécution :
```powershell
# Autoriser l'exécution temporairement
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
.\install_cloudinary.ps1
```

### Linux / Mac

```bash
# Rendre le script exécutable
chmod +x install_cloudinary.sh

# Exécuter l'installation
./install_cloudinary.sh
```

---

## ✅ Ce que fait le script

1. ✅ Installe `cloudinary/cloudinary_php` via Composer
2. ✅ Crée le fichier `.env.local` (template)
3. ✅ Vide le cache Symfony
4. ✅ Vérifie que tous les fichiers sont présents
5. ✅ Affiche les prochaines étapes

**Durée** : ~2 minutes

---

## 📝 Configuration après installation

### 1. Configurer Cloudinary (5 min)

**Voir** : [CREATE_ENV_LOCAL.md](CREATE_ENV_LOCAL.md)

1. Créez un compte sur [cloudinary.com](https://cloudinary.com/)
2. Copiez votre `CLOUDINARY_URL` depuis le [Dashboard](https://console.cloudinary.com/console/)
3. Modifiez `.env.local` :

```bash
# Remplacez avec vos vraies valeurs
CLOUDINARY_URL=cloudinary://123456789:abcdefg@your-cloud-name
```

4. Videz le cache :
```bash
php bin/console cache:clear
```

### 2. Tester (2 min)

**Voir** : [CLOUDINARY_QUICK_TEST.md](CLOUDINARY_QUICK_TEST.md)

```bash
# Démarrer le serveur
symfony serve

# OU
php -S localhost:8000 -t public
```

Ouvrez : **http://localhost:8000/test/cloudinary/upload-form**

Upload une image de test ✅

### 3. Migrer la base de données (5 min)

**Voir** : [CLOUDINARY_DATABASE_MIGRATION.md](CLOUDINARY_DATABASE_MIGRATION.md)

```bash
# Sauvegarder la base
mysqldump -u user -p base > backup.sql

# Appliquer la migration
mysql -u user -p base < migrations/CloudinaryUrlsMigration.sql
```

### 4. Intégrer dans votre code (30 min)

**Voir** : [CLOUDINARY_SETUP_GUIDE.md](CLOUDINARY_SETUP_GUIDE.md)

- Modifier `ProfilAdminController.php`
- Modifier les autres contrôleurs avec uploads
- Mettre à jour les templates Twig

---

## 🐛 Problèmes ?

### Script ne s'exécute pas (Windows)

```powershell
# Vérifier la politique d'exécution
Get-ExecutionPolicy

# Si "Restricted", autoriser temporairement
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
```

### Script ne s'exécute pas (Linux/Mac)

```bash
# Vérifier les permissions
ls -la install_cloudinary.sh

# Rendre exécutable
chmod +x install_cloudinary.sh
```

### Package Cloudinary ne s'installe pas

```bash
# Vérifier Composer
composer --version

# Mettre à jour Composer
composer self-update

# Réessayer
composer require cloudinary/cloudinary_php
```

---

## 📚 Documentation complète

| Fichier | Description |
|---------|-------------|
| [CLOUDINARY_README.md](CLOUDINARY_README.md) | Vue d'ensemble |
| [CREATE_ENV_LOCAL.md](CREATE_ENV_LOCAL.md) | Configuration |
| [CLOUDINARY_QUICK_TEST.md](CLOUDINARY_QUICK_TEST.md) | Test rapide |
| [CLOUDINARY_DATABASE_MIGRATION.md](CLOUDINARY_DATABASE_MIGRATION.md) | Migration BDD |
| [CLOUDINARY_SETUP_GUIDE.md](CLOUDINARY_SETUP_GUIDE.md) | Guide complet |
| [CLOUDINARY_FILES_SUMMARY.md](CLOUDINARY_FILES_SUMMARY.md) | Liste des fichiers |

---

## ⏱️ Timeline complète

| Étape | Durée | Fichier de référence |
|-------|-------|---------------------|
| Installation script | 2 min | Ce fichier |
| Configuration .env.local | 5 min | CREATE_ENV_LOCAL.md |
| Test | 2 min | CLOUDINARY_QUICK_TEST.md |
| Migration BDD | 5 min | CLOUDINARY_DATABASE_MIGRATION.md |
| Intégration code | 30-60 min | CLOUDINARY_SETUP_GUIDE.md |
| **TOTAL** | **45-75 min** | |

---

## 🎯 Installation manuelle (sans script)

Si vous préférez installer manuellement :

```bash
# 1. Installer le package
composer require cloudinary/cloudinary_php

# 2. Créer .env.local
notepad .env.local  # Windows
nano .env.local     # Linux/Mac

# Ajouter :
# CLOUDINARY_URL=cloudinary://KEY:SECRET@CLOUD_NAME

# 3. Vider le cache
php bin/console cache:clear

# 4. Tester
symfony serve
# Ouvrir http://localhost:8000/test/cloudinary/upload-form
```

---

## ✅ Checklist

- [ ] Script d'installation exécuté
- [ ] Package `cloudinary/cloudinary_php` installé
- [ ] Fichier `.env.local` créé
- [ ] `CLOUDINARY_URL` configuré avec vraies valeurs
- [ ] Cache Symfony vidé
- [ ] Test réussi sur `/test/cloudinary/upload-form`
- [ ] Migration BDD effectuée
- [ ] Intégration dans les contrôleurs commencée

---

**🎉 Prêt à commencer ? Lancez le script d'installation !**

```bash
# Windows
.\install_cloudinary.ps1

# Linux/Mac
./install_cloudinary.sh
```



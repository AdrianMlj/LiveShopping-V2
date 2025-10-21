#!/bin/bash

# ========================================
# Script d'installation automatique Cloudinary
# Pour le projet LiveShopping
# ========================================

set -e  # Arrêter en cas d'erreur

echo "🌩️  Installation de Cloudinary pour LiveShopping"
echo "================================================"
echo ""

# Couleurs pour l'output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction d'affichage
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Vérifier si on est dans le bon répertoire
if [ ! -f "composer.json" ]; then
    print_error "Erreur : composer.json non trouvé"
    print_info "Exécutez ce script depuis la racine du projet"
    exit 1
fi

print_success "Répertoire du projet détecté"
echo ""

# Étape 1 : Installer le package Cloudinary
echo "📦 Étape 1/5 : Installation du package Cloudinary"
echo "------------------------------------------------"

if composer show cloudinary/cloudinary_php &> /dev/null; then
    print_info "Le package cloudinary/cloudinary_php est déjà installé"
else
    print_info "Installation de cloudinary/cloudinary_php..."
    composer require cloudinary/cloudinary_php
    print_success "Package installé"
fi
echo ""

# Étape 2 : Créer .env.local
echo "⚙️  Étape 2/5 : Configuration de .env.local"
echo "------------------------------------------"

if [ -f ".env.local" ]; then
    print_warning ".env.local existe déjà"
    read -p "Voulez-vous le remplacer ? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_info "Conservation du .env.local existant"
    else
        rm .env.local
        print_info ".env.local supprimé"
    fi
fi

if [ ! -f ".env.local" ]; then
    print_info "Création de .env.local..."

    echo "###> Cloudinary Configuration ###" > .env.local
    echo "# Remplacez les valeurs ci-dessous par vos identifiants Cloudinary" >> .env.local
    echo "# Format: cloudinary://API_KEY:API_SECRET@CLOUD_NAME" >> .env.local
    echo "# Obtenez-les sur: https://console.cloudinary.com/console/" >> .env.local
    echo "CLOUDINARY_URL=cloudinary://YOUR_API_KEY:YOUR_API_SECRET@YOUR_CLOUD_NAME" >> .env.local
    echo "###< Cloudinary Configuration ###" >> .env.local

    print_success ".env.local créé"
    print_warning "N'oubliez pas de configurer CLOUDINARY_URL avec vos identifiants !"
    print_info "Voir CREATE_ENV_LOCAL.md pour les instructions"
else
    print_info ".env.local déjà présent"
fi
echo ""

# Étape 3 : Vider le cache
echo "🗑️  Étape 3/5 : Nettoyage du cache"
echo "----------------------------------"

if [ -d "var/cache" ]; then
    print_info "Suppression du cache..."
    php bin/console cache:clear
    print_success "Cache vidé"
else
    print_warning "Dossier var/cache introuvable"
fi
echo ""

# Étape 4 : Vérifier les fichiers
echo "📄 Étape 4/5 : Vérification des fichiers"
echo "----------------------------------------"

FILES=(
    "src/Service/CloudinaryService.php"
    "src/Controller/CloudinaryTestController.php"
    "templates/test/cloudinary_test.html.twig"
    "config/services.yaml"
    "CLOUDINARY_README.md"
    "CLOUDINARY_QUICK_TEST.md"
)

all_files_present=true

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        print_success "$file présent"
    else
        print_error "$file manquant"
        all_files_present=false
    fi
done
echo ""

# Étape 5 : Instructions finales
echo "📋 Étape 5/5 : Prochaines étapes"
echo "--------------------------------"

if [ "$all_files_present" = true ]; then
    print_success "Tous les fichiers sont en place"
    echo ""
    print_info "🎯 Pour continuer :"
    echo ""
    echo "1. Configurez votre CLOUDINARY_URL dans .env.local"
    echo "   👉 Voir CREATE_ENV_LOCAL.md"
    echo ""
    echo "2. Testez l'intégration :"
    echo "   $ symfony serve"
    echo "   $ open http://localhost:8000/test/cloudinary/upload-form"
    echo ""
    echo "3. Migrez votre base de données :"
    echo "   👉 Voir CLOUDINARY_DATABASE_MIGRATION.md"
    echo ""
    echo "4. Intégrez dans vos contrôleurs :"
    echo "   👉 Voir CLOUDINARY_SETUP_GUIDE.md"
    echo ""
    print_success "Installation terminée !"
else
    print_error "Certains fichiers sont manquants"
    print_info "Vérifiez que tous les fichiers Cloudinary ont été créés"
fi

echo ""
echo "================================================"
echo "📚 Documentation complète : CLOUDINARY_README.md"
echo "🚀 Test rapide : CLOUDINARY_QUICK_TEST.md"
echo "================================================"





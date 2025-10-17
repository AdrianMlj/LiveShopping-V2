# ========================================
# Script d'installation automatique Cloudinary (Windows PowerShell)
# Pour le projet LiveShopping
# ========================================

$ErrorActionPreference = "Stop"

Write-Host "🌩️  Installation de Cloudinary pour LiveShopping" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# Fonctions d'affichage
function Print-Success {
    param($Message)
    Write-Host "✅ $Message" -ForegroundColor Green
}

function Print-Error {
    param($Message)
    Write-Host "❌ $Message" -ForegroundColor Red
}

function Print-Info {
    param($Message)
    Write-Host "ℹ️  $Message" -ForegroundColor Blue
}

function Print-Warning {
    param($Message)
    Write-Host "⚠️  $Message" -ForegroundColor Yellow
}

# Vérifier si on est dans le bon répertoire
if (-not (Test-Path "composer.json")) {
    Print-Error "Erreur : composer.json non trouvé"
    Print-Info "Exécutez ce script depuis la racine du projet"
    exit 1
}

Print-Success "Répertoire du projet détecté"
Write-Host ""

# Étape 1 : Installer le package Cloudinary
Write-Host "📦 Étape 1/5 : Installation du package Cloudinary" -ForegroundColor Cyan
Write-Host "------------------------------------------------" -ForegroundColor Cyan

try {
    $composerShow = composer show cloudinary/cloudinary_php 2>&1
    if ($LASTEXITCODE -eq 0) {
        Print-Info "Le package cloudinary/cloudinary_php est déjà installé"
    }
} catch {
    Print-Info "Installation de cloudinary/cloudinary_php..."
    composer require cloudinary/cloudinary_php
    Print-Success "Package installé"
}
Write-Host ""

# Étape 2 : Créer .env.local
Write-Host "⚙️  Étape 2/5 : Configuration de .env.local" -ForegroundColor Cyan
Write-Host "------------------------------------------" -ForegroundColor Cyan

if (Test-Path ".env.local") {
    Print-Warning ".env.local existe déjà"
    $response = Read-Host "Voulez-vous le remplacer ? (y/N)"
    if ($response -ne "y" -and $response -ne "Y") {
        Print-Info "Conservation du .env.local existant"
    } else {
        Remove-Item ".env.local"
        Print-Info ".env.local supprimé"
    }
}

if (-not (Test-Path ".env.local")) {
    Print-Info "Création de .env.local..."

    $envContent = @"
###> Cloudinary Configuration ###
# Remplacez les valeurs ci-dessous par vos identifiants Cloudinary
# Format: cloudinary://API_KEY:API_SECRET@CLOUD_NAME
# Obtenez-les sur: https://console.cloudinary.com/console/
CLOUDINARY_URL=cloudinary://YOUR_API_KEY:YOUR_API_SECRET@YOUR_CLOUD_NAME
###< Cloudinary Configuration ###
"@

    $envContent | Out-File -FilePath ".env.local" -Encoding UTF8

    Print-Success ".env.local créé"
    Print-Warning "N'oubliez pas de configurer CLOUDINARY_URL avec vos identifiants !"
    Print-Info "Voir CREATE_ENV_LOCAL.md pour les instructions"
} else {
    Print-Info ".env.local déjà présent"
}
Write-Host ""

# Étape 3 : Vider le cache
Write-Host "🗑️  Étape 3/5 : Nettoyage du cache" -ForegroundColor Cyan
Write-Host "----------------------------------" -ForegroundColor Cyan

if (Test-Path "var\cache") {
    Print-Info "Suppression du cache..."
    php bin/console cache:clear
    Print-Success "Cache vidé"
} else {
    Print-Warning "Dossier var\cache introuvable"
}
Write-Host ""

# Étape 4 : Vérifier les fichiers
Write-Host "📄 Étape 4/5 : Vérification des fichiers" -ForegroundColor Cyan
Write-Host "----------------------------------------" -ForegroundColor Cyan

$files = @(
    "src\Service\CloudinaryService.php",
    "src\Controller\CloudinaryTestController.php",
    "templates\test\cloudinary_test.html.twig",
    "config\services.yaml",
    "CLOUDINARY_README.md",
    "CLOUDINARY_QUICK_TEST.md"
)

$allFilesPresent = $true

foreach ($file in $files) {
    if (Test-Path $file) {
        Print-Success "$file présent"
    } else {
        Print-Error "$file manquant"
        $allFilesPresent = $false
    }
}
Write-Host ""

# Étape 5 : Instructions finales
Write-Host "📋 Étape 5/5 : Prochaines étapes" -ForegroundColor Cyan
Write-Host "--------------------------------" -ForegroundColor Cyan

if ($allFilesPresent) {
    Print-Success "Tous les fichiers sont en place"
    Write-Host ""
    Print-Info "🎯 Pour continuer :"
    Write-Host ""
    Write-Host "1. Configurez votre CLOUDINARY_URL dans .env.local"
    Write-Host "   👉 Voir CREATE_ENV_LOCAL.md"
    Write-Host ""
    Write-Host "2. Testez l'intégration :"
    Write-Host "   PS> symfony serve"
    Write-Host "   PS> Start-Process http://localhost:8000/test/cloudinary/upload-form"
    Write-Host ""
    Write-Host "3. Migrez votre base de données :"
    Write-Host "   👉 Voir CLOUDINARY_DATABASE_MIGRATION.md"
    Write-Host ""
    Write-Host "4. Intégrez dans vos contrôleurs :"
    Write-Host "   👉 Voir CLOUDINARY_SETUP_GUIDE.md"
    Write-Host ""
    Print-Success "Installation terminée !"
} else {
    Print-Error "Certains fichiers sont manquants"
    Print-Info "Vérifiez que tous les fichiers Cloudinary ont été créés"
}

Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "📚 Documentation complète : CLOUDINARY_README.md" -ForegroundColor Cyan
Write-Host "🚀 Test rapide : CLOUDINARY_QUICK_TEST.md" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan

# Pause à la fin
Write-Host ""
Write-Host "Appuyez sur une touche pour continuer..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")



#!/bin/bash

# Script de test pour le Makefile
# Ce script teste les commandes principales sans utiliser make

set -e

echo "🧪 Test du Makefile"
echo "==================="
echo ""

# Variables du Makefile
DOCKER_COMPOSE_FILE="compose.yaml"
COMPOSE_PROJECT_NAME="liveshopping"
DOCKER_COMPOSE="docker compose -p ${COMPOSE_PROJECT_NAME} -f ${DOCKER_COMPOSE_FILE}"

echo "✅ Test 1: Vérification de la syntaxe Docker Compose"
echo "---------------------------------------------------"
if $DOCKER_COMPOSE config > /dev/null 2>&1; then
    echo "✅ La configuration Docker Compose est valide"
else
    echo "❌ Erreur dans la configuration Docker Compose"
    exit 1
fi
echo ""

echo "✅ Test 2: Vérification des fichiers nécessaires"
echo "------------------------------------------------"
if [ -f "compose.yaml" ]; then
    echo "✅ compose.yaml trouvé"
else
    echo "❌ compose.yaml manquant"
    exit 1
fi

if [ -f "composer.json" ]; then
    echo "✅ composer.json trouvé"
else
    echo "❌ composer.json manquant"
    exit 1
fi

if [ -f "package.json" ]; then
    echo "✅ package.json trouvé"
else
    echo "❌ package.json manquant"
    exit 1
fi
echo ""

echo "✅ Test 3: Vérification des commandes disponibles"
echo "-------------------------------------------------"
commands=("docker" "composer" "npm" "php")

for cmd in "${commands[@]}"; do
    if command -v $cmd > /dev/null 2>&1; then
        echo "✅ $cmd est installé ($(which $cmd))"
    else
        echo "⚠️  $cmd n'est pas installé ou pas dans le PATH"
    fi
done
echo ""

echo "✅ Test 4: Test de la commande Docker Compose"
echo "---------------------------------------------"
if $DOCKER_COMPOSE ps > /dev/null 2>&1; then
    echo "✅ La commande Docker Compose fonctionne"
    echo ""
    echo "Conteneurs actuels:"
    $DOCKER_COMPOSE ps
else
    echo "⚠️  Impossible d'exécuter docker compose ps (normal si les conteneurs ne sont pas démarrés)"
fi
echo ""

echo "✅ Test 5: Vérification de la structure du Makefile"
echo "---------------------------------------------------"
if grep -q "COMPOSE_PROJECT_NAME" Makefile; then
    echo "✅ Variable COMPOSE_PROJECT_NAME trouvée"
fi

if grep -q "DOCKER_COMPOSE_FILE" Makefile; then
    echo "✅ Variable DOCKER_COMPOSE_FILE trouvée"
fi

if grep -q "^install:" Makefile; then
    echo "✅ Cible 'install' trouvée"
fi

if grep -q "^up:" Makefile; then
    echo "✅ Cible 'up' trouvée"
fi

if grep -q "^help:" Makefile; then
    echo "✅ Cible 'help' trouvée"
fi
echo ""

echo "📋 Résumé"
echo "========="
echo "Pour utiliser le Makefile, vous devez installer make:"
echo "  sudo apt install make"
echo ""
echo "Ensuite, vous pouvez tester avec:"
echo "  make help          # Afficher toutes les commandes"
echo "  make up            # Démarrer les conteneurs Docker"
echo "  make install       # Installer le projet"
echo ""



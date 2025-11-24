#!/bin/bash

# Script de démarrage rapide pour LiveShopping avec Docker

set -e

echo "🚀 Démarrage de LiveShopping avec Docker"
echo "=========================================="
echo ""

# Vérifier que Docker est installé
if ! command -v docker &> /dev/null; then
    echo "❌ Docker n'est pas installé. Veuillez l'installer d'abord."
    exit 1
fi

# Vérifier que Docker Compose est disponible
if ! docker compose version &> /dev/null; then
    echo "❌ Docker Compose n'est pas disponible."
    exit 1
fi

echo "✅ Docker et Docker Compose sont disponibles"
echo ""

# Construire et démarrer les conteneurs
echo "📦 Construction et démarrage des conteneurs..."
docker compose -p liveshopping -f compose.yaml up -d --build

echo ""
echo "⏳ Attente du démarrage de PostgreSQL..."
sleep 5

# Vérifier que PostgreSQL est prêt
until docker compose -p liveshopping -f compose.yaml exec -T database pg_isready -U app > /dev/null 2>&1; do
    echo "   En attente..."
    sleep 2
done

echo "✅ PostgreSQL est prêt"
echo ""

# Installer les dépendances si nécessaire
if [ ! -d "vendor" ]; then
    echo "📥 Installation des dépendances Composer..."
    docker compose -p liveshopping -f compose.yaml exec -T php composer install --no-interaction
fi

if [ ! -d "node_modules" ]; then
    echo "📥 Installation des dépendances npm..."
    docker compose -p liveshopping -f compose.yaml exec -T php npm install
fi

echo ""
echo "✅ Installation terminée"
echo ""
echo "🌐 L'application est accessible sur: http://localhost:8000"
echo ""
echo "Commandes utiles:"
echo "  make logs        # Voir les logs"
echo "  make shell       # Accéder au conteneur PHP"
echo "  make down        # Arrêter les conteneurs"
echo "  make help        # Voir toutes les commandes"
echo ""


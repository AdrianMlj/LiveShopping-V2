📊 VOS ENTITÉS DISTINCTES :
1. Utilisateur → Table Users
2. Catégorie → Table Category
3. Article/Produit → Table Item
4. Taille → Table Size
5. Couleur → Table Color
6. Déclinaison Produit → Tables Item_size + Item_size_color
7. Stock → Table Items_stock
8. Prix → Table Price_items
9. Promotion → Table Promotion
10. Commande → Table Commande
11. Détail Commande → Table Commande_details
12. État Commande → Table State_commande
13. Vente → Table Sale
14. Session Live → Table Live
15. Détail Live → Table Live_details
16. Export Temporaire → Table Export_temp


Ilay traduction category sy Item 
Ilay panier moyenne dans le dabshoard
Ilay dashboard mila lalinina tsara ny explication any


Le chiffre d'affaires moyen ciblé et réalisé
Calcul :
Chiffre d'affaires moyen ciblé = (Total de tous les objectifs CA) ÷ (Nombre de mois)
Chiffre d'affaires moyen réalisé = (Total de tous les CA réalisés) ÷ (Nombre de mois)
Exemple concret :
Si vous avez 3 mois avec ces objectifs CA :
Janvier : Objectif 10 000€ → Réalisé 9 500€
Février : Objectif 12 000€ → Réalisé 11 000€
Mars : Objectif 15 000€ → Réalisé 16 000€
CA moyen ciblé = (10 000 + 12 000 + 15 000) ÷ 3 = 12 333€
CA moyen réalisé = (9 500 + 11 000 + 16 000) ÷ 3 = 12 167€


Cas B : Mois en cours 📈 → CALCUL CLÉ
Projection CA = (CA réalisé ÷ Jours passés) × Jours total du mois
Projection Ventes = (Ventes réalisées ÷ Jours passés) × Jours total du mois
Exemple concret :
Aujourd'hui : 15 mars (15 jours passés)
Mars a 31 jours
CA réalisé sur 15 jours : 4 500€
Ventes réalisées sur 15 jours : 45
Projection CA = (4 500 ÷ 15) × 31 = 300 × 31 = 9 300€
Projection Ventes = (45 ÷ 15) × 31 = 3 × 31 = 93 ventes
Logique : Si vous faites 300€/jour pendant 15 jours, vous ferez en moyenne 300€/jour sur tout le mois.

Objectif Mensuel Lissé
Le calcul des objectifs mensuels s’appuie désormais sur la médiane des moyennes journalières des 6 derniers mois disponibles.
Déroulement (exemple)
Pour chaque mois historique, on calcule CA réalisé / nombre de jours.
On conserve la fenêtre glissante des 3 derniers quotidiens moyens, on en extrait la médiane (valeur centrale).
L’objectif du mois courant devient médiane × jours du mois.
Exemple : historiques quotidiens = [90, 110, 130, 120, 105, 100] [90, 100, 105, 110, 120, 130] → médiane = 107,5; pour un mois de 30 jours, objectif ≈ 3 225 €.
Conseils
Rafraîchis l’écran “Objectif mensuel” pour constater le nouvel écart (ecart_ca/ecart_ventes).
Alimente plusieurs mois de ventes pour que la médiane se stabilise.

Votre application est un serveur de streaming temps réel utilisant principalement Node.js avec WebSockets pour la communication et implémentant un protocole de type WebRTC pour les flux vidéo. C'est une architecture moderne pour des applications de streaming en direct avec interaction en temps réel.


Utilite anle export ra efa misy ny stock actuel !!!


WebSocket est un protocole de communication qui établit une connexion bidirectionnelle et permanente entre un client (navigateur) et un serveur. Contrairement au HTTP qui nécessite de nouvelles requêtes pour chaque échange, WebSocket maintient une connexion ouverte permettant des échanges en temps réel.
La bibliothèque ws est une implémentation légère et efficace de WebSocket pour Node.js. Voici ses caractéristiques :
Fonctionnement :
Crée un "tunnel" persistant entre client et serveur
Permet des échanges instantanés sans délai de requête
Idéal pour applications temps réel (chat, live streaming, notifications)

Node.js était le choix idéal pour notre serveur WebSocket pour plusieurs raisons techniques décisives :
🚀 Performance optimale
Architecture non-bloquante : Parfait pour gérer des milliers de connexions simultanées de streamers et spectateurs
Traitement asynchrone : Permet de diffuser des flux vidéo en temps réel sans latence
⚡ Écosystème idéal pour le temps réel
Bibliothèque ws : Solution WebSocket légère et ultra-rapide
Event-driven : Architecture naturellement adaptée aux communications bidirectionnelles

WebRTC (Web Real-Time Communication) est une technologie fondamentale pour votre projet LiveShopping. Voici pourquoi :
🎯 Ce qu'est WebRTC
Standard ouvert permettant les communications audio/vidéo en temps réel directement entre navigateurs
Sans plugins : natif dans tous les navigateurs modernes
P2P (Peer-to-Peer) : connexion directe entre les appareils
Fonctionnement :
Signalisation via votre serveur Node.js/WebSocket
Négociation WebRTC entre streamer et spectateurs
Connexion P2P établie pour le flux vidéo
            WebSocket vs WebRTC
WebSocket	                    WebRTC
Données structurées	            Flux audio/vidéo
Client-Serveur	                P2P direct
Parfait pour la signalisation	Parfait pour le streaming
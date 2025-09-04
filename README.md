# LiveShopping
php -S localhost:8000 -t public


php bin/console make:migration
php bin/console doctrine:migrations:migrate

composer require symfony/security-bundle
composer require symfony/form symfony/validator
php bin/console make:form InscriptionForm 
php bin/console make:controller InscriptionController 
composer require symfony/http-
composer require symfony/asset
php bin/console make:
npm install chart.js
composer require symfony/mime
composer require symfony/firebase-notifier


npm install ws

symfony serve --allow-http --port=8000 --allow-all-ip
netsh advfirewall firewall add rule name="WebSocket" dir=in action=allow protocol=TCP localport=9090



Redis-cli 

composer require symfony/redis-messenger
composer require predis/predis

Modules fanampiny:

    E-Commerce :
        -Listes produits:
            -Recherche
            -Notification
            -Favoris
            -Rating :(facultatif commentaires)
            -panier
    Livraison : 
        -Geolocalisation:
            -suivi du colis
            -etat(-En attente,En cours de recuperation,En cours de livraison,Livré)
            -notication de l'etat
            -Historique de notification

    Programme de fidelite :
        -Cote client il y a des points de fidelite apres achat
        -Les points deviennent des remises
        -Dashboard pour voir les points de fidelite

    Systeme de rembourssement : 
        -Interface client pour demande de rembourssement par etat 
        -Notification cote vendeurs pour la demande de rembourssement

    Manampy Dashboard oany client
        -Depenses totale,categorie,frequence d'achat
    Ao am Admin mjery ny client:
        -Liste client msuivre anazy (Read ny dashboard anle client)
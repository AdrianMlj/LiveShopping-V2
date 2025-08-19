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


Cote mobile uniquement :
        -Livraison : 
            -Login pour livreur
                -Nom,prenom,tel,mot de passe
            -Inscription:
                -Nom,Prenom,Email,Mode de livraison,Tel,Cin,date de naissance,mot de passe
            -Accueil Livreur:
                -Dashboard :    
                    -Vue generales des livraison
                    -Indicateurs des livraisons effectues/en cours
                    -Statistique
                    -Listes des commandes a livrer
                -Gestion des courses:
                    -Listes des commandes
                        -Details de la commande(adresse,client,colis)
                        -Details du colis (Prix,Etat,Qr code)
                        -Validation des courses
                    -Mises a jours des statuts
                        -En attente,En cours de recuperation,En cours de livraison,Livré
            -Geolocalisation:
                -suivi du colis(map)
                -etat(En attente,En cours de recuperation,En cours de livraison,Livré)
                -Historique de trajets
            -Notification :
                -Nouvelle livraisons disponibles
                -Notification de paiements(Paiements recu)
            -Paiement:
                -Historique de paiements
            -Client et vendeurs :
                -suivi du colis
                -etat(En attente,En cours de recuperation,En cours de livraison,Livré)
                -notication de l'etat
            
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

composer require knplabs/knp-paginator-bundle
composer require symfony/translation

npm install ws
npm install jspdf html2canvas

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
            


1️⃣ Liste des ventes (page principale de l’historique)

👉 Colonnes à afficher :

N° de vente (id_sale)

Date de vente (sale_date)

Client (nom/prénom → via Users.id_user)

Vendeur (Users aussi)

État de la commande (via State_commande.name_state → ex. "En attente", "Payé", "Livré")

Montant total (somme des Commande_details.quantity * price)

Statut paiement (is_paid)

👉 Filtres utiles :

Par date (intervalle)

Par état de commande (payé, en attente, livré…)

Par statut paiement (payé / non payé)

2️⃣ Détails d’une vente (vue détaillée)

Quand on clique sur une ligne, afficher :

Infos commande (id_commande, date, état, vendeur, client)

Liste des articles vendus (depuis Commande_details) :

Produit (via Item_size → Item)

Taille (si applicable)

Quantité

Prix unitaire

Sous-total (quantité × prix)

Total général

3️⃣ Facture (vue imprimable / téléchargeable PDF)

Format classique de facture :

En-tête avec ton logo, date, numéro de facture (= id_sale ou généré)

Infos client (nom, adresse, contact → Users)

Détails commande (articles, quantités, prix)

Montant total

Mention paiement (Payé / Non payé)

Signature / cachet si nécessaire

4️⃣ Paiement (gestion ou affichage)

Si is_paid = false → bouton Marquer comme payé

Historique des paiements (si tu ajoutes une table Payment)
Exemple de structure :

CREATE TABLE Payment(
   id_payment SERIAL PRIMARY KEY,
   id_sale INTEGER NOT NULL,
   amount NUMERIC(10,2) NOT NULL,
   method VARCHAR(100),  -- ex: Cash, Carte, Virement
   payment_date TIMESTAMP DEFAULT NOW(),
   FOREIGN KEY(id_sale) REFERENCES Sale(id_sale)
);


Afficher la liste des paiements effectués + reste dû
-- Active: 1748293822835@@127.0.0.1@5432@liveshopping

CREATE DATABASE liveShopping;
\c liveShopping;

CREATE TABLE Users(
   id_user SERIAL,
   email VARCHAR(255)  NOT NULL,
   username VARCHAR(255)  NOT NULL,
   password VARCHAR(500)  NOT NULL,
   contact VARCHAR(10)  NOT NULL,
   address VARCHAR(255)  NOT NULL,
   country VARCHAR(500)  NOT NULL,
   images BIGINT,
   is_seller BOOLEAN,
   PRIMARY KEY(id_user)
);

CREATE TABLE Category(
   id_category SERIAL,
   name_category VARCHAR(255)  NOT NULL,
   Description TEXT,
   PRIMARY KEY(id_category)
);

CREATE TABLE Item(
   id_item SERIAL,
   images BIGINT,
   name_item VARCHAR(255)  NOT NULL,
   id_seller INTEGER NOT NULL,
   id_category INTEGER NOT NULL,
   PRIMARY KEY(id_item),
   FOREIGN KEY(id_seller) REFERENCES Users(id_user),
   FOREIGN KEY(id_category) REFERENCES Category(id_category)
);

CREATE TABLE Size(
   id_size SERIAL,
   name_size VARCHAR(255)  NOT NULL,
   PRIMARY KEY(id_size)
);

CREATE TABLE Item_size(
   id_item_size SERIAL,
   value_size VARCHAR(50) ,
   id_size INTEGER NOT NULL,
   id_item INTEGER NOT NULL,
   PRIMARY KEY(id_item_size),
   FOREIGN KEY(id_size) REFERENCES Size(id_size),
   FOREIGN KEY(id_item) REFERENCES Item(id_item)
);

CREATE TABLE Items_stock(
   id_item_stock SERIAL,
   out_item INTEGER,
   in_item INTEGER ,
   date_move TIMESTAMP NOT NULL,
   id_item_size INTEGER NOT NULL,
   PRIMARY KEY(id_item_stock),
   FOREIGN KEY(id_item_size) REFERENCES Item_size(id_item_size)
);

CREATE TABLE Promotion(
   id_promotion SERIAL,
   name_promotion VARCHAR(255)  NOT NULL,
   description TEXT,
   percentage NUMERIC(15,2)   NOT NULL,
   start_date DATE NOT NULL,
   end_date DATE,
   id_item INTEGER NOT NULL,
   PRIMARY KEY(id_promotion),
   FOREIGN KEY(id_item) REFERENCES Item(id_item)
);

CREATE TABLE Price_items(
   id_price SERIAL,
   price NUMERIC(15,2)   NOT NULL,
   date_price DATE NOT NULL,
   id_item INTEGER NOT NULL,
   PRIMARY KEY(id_price),
   FOREIGN KEY(id_item) REFERENCES Item(id_item)
);

CREATE TABLE Notification_type(
   id_type SERIAL,
   name_type VARCHAR(255)  NOT NULL,
   PRIMARY KEY(id_type)
);

CREATE TABLE Favorites(
   id_favorites SERIAL,
   create_at TIMESTAMP NOT NULL,
   id_client INTEGER NOT NULL,
   PRIMARY KEY(id_favorites),
   FOREIGN KEY(id_client) REFERENCES Users(id_user)
);

CREATE TABLE Favorite_details(
   id_favorite_detail SERIAL,
   id_item_size INTEGER NOT NULL,
   id_favorites INTEGER NOT NULL,
   PRIMARY KEY(id_favorite_detail),
   FOREIGN KEY(id_item_size) REFERENCES Item_size(id_item_size),
   FOREIGN KEY(id_favorites) REFERENCES Favorites(id_favorites)
);

CREATE TABLE Bag(
   id_bag SERIAL,
   create_at TIMESTAMP NOT NULL,
   is_commande BOOLEAN,
   id_client INTEGER NOT NULL,
   id_seller INTEGER NOT NULL,
   PRIMARY KEY(id_bag),
   FOREIGN KEY(id_client) REFERENCES Users(id_user),
   FOREIGN KEY(id_seller) REFERENCES Users(id_user)
);

CREATE TABLE Bag_details(
   id_bag_detail SERIAL,
   id_item_size INTEGER NOT NULL,
   quantity INTEGER NOT NULL DEFAULT 1,
   id_bag INTEGER NOT NULL,
   PRIMARY KEY(id_bag_detail),
   FOREIGN KEY(id_item_size) REFERENCES Item_size(id_item_size),
   FOREIGN KEY(id_bag) REFERENCES Bag(id_bag)
);

CREATE TABLE Commande(
   id_commande SERIAL PRIMARY KEY,
   id_state INTEGER NOT NULL,             -- ex: en attente, payé, livré
   id_client INTEGER NOT NULL,
   id_seller INTEGER NOT NULL,
   created_at TIMESTAMP NOT NULL DEFAULT NOW(),
   FOREIGN KEY(id_state) REFERENCES State_commande(id_state),
   FOREIGN KEY(id_client) REFERENCES Users(id_user),
   FOREIGN KEY(id_seller) REFERENCES Users(id_user)
);

CREATE TABLE Commande_details(
   id_commande_detail SERIAL PRIMARY KEY,
   id_commande INTEGER NOT NULL,
   id_item_size INTEGER NOT NULL,
   quantity INTEGER NOT NULL DEFAULT 1,
   price NUMERIC(10,2) NOT NULL,  -- prix figé au moment de la validation
   FOREIGN KEY(id_commande) REFERENCES Commande(id_commande),
   FOREIGN KEY(id_item_size) REFERENCES Item_size(id_item_size)
);


CREATE TABLE Follow_seller(
   id_follow SERIAL,
   date_following TIMESTAMP NOT NULL,
   id_client INTEGER NOT NULL,
   id_seller INTEGER NOT NULL,
   PRIMARY KEY(id_follow),
   FOREIGN KEY(id_client) REFERENCES Users(id_user),
   FOREIGN KEY(id_seller) REFERENCES Users(id_user)
);

CREATE TABLE Live(
   id_live SERIAL,
   start_live TIMESTAMP NOT NULL,
   end_live TIMESTAMP,
   nbr_like INTEGER,
   id_seller INTEGER NOT NULL,
   PRIMARY KEY(id_live),
   FOREIGN KEY(id_seller) REFERENCES Users(id_user)
);

CREATE TABLE Live_details(
   id_live_detail SERIAL,
   id_item INTEGER NOT NULL,
   id_live INTEGER NOT NULL,
   PRIMARY KEY(id_live_detail),
   FOREIGN KEY(id_item) REFERENCES Item(id_item),
   FOREIGN KEY(id_live) REFERENCES Live(id_live)
);

CREATE TABLE State_commande(
   id_state SERIAL,
   name_state VARCHAR(255)  NOT NULL,
   PRIMARY KEY(id_state)
);

CREATE TABLE Notification(
   id_notification SERIAL,
   title VARCHAR(500)  NOT NULL,
   content TEXT NOT NULL,
   is_read BOOLEAN,
   date_creation TIMESTAMP NOT NULL,
   id_type INTEGER NOT NULL,
   id_user INTEGER NOT NULL,
   PRIMARY KEY(id_notification),
   FOREIGN KEY(id_type) REFERENCES Notification_type(id_type),
   FOREIGN KEY(id_user) REFERENCES Users(id_user)
);

CREATE TABLE Liaison_notification(
   id_liaison SERIAL,
   name_table VARCHAR(50)  NOT NULL,
   id_table INTEGER NOT NULL,
   id_notification INTEGER NOT NULL,
   PRIMARY KEY(id_liaison),
   FOREIGN KEY(id_notification) REFERENCES Notification(id_notification)
);

CREATE TABLE Sale(
   id_sale SERIAL,
   sale_date TIMESTAMP NOT NULL,
   is_paid BOOLEAN,
   id_commande INTEGER NOT NULL,
   PRIMARY KEY(id_sale),
   FOREIGN KEY(id_commande) REFERENCES Commande(id_commande)
);

CREATE TABLE Goals(
   id_goal SERIAL,
   id_seller INTEGER NOT NULL,
   target_ca NUMERIC(15,2) NOT NULL,   
   target_ventes INTEGER NOT NULL,
   PRIMARY KEY(id_goal),
   FOREIGN KEY(id_seller) REFERENCES Users(id_user)
);

SELECT
    EXTRACT(YEAR FROM s.sale_date) AS annee,
    EXTRACT(MONTH FROM s.sale_date) AS mois,
    g.target_ca,
    g.target_ventes,
    COALESCE(SUM(cd.price * cd.quantity), 0) AS ca_realise,
    COUNT(DISTINCT s.id_sale) AS ventes_realisees,
    COALESCE(SUM(cd.price * cd.quantity), 0) - g.target_ca AS ecart_ca,
    COUNT(DISTINCT s.id_sale) - g.target_ventes AS ecart_ventes
FROM Goals g
LEFT JOIN Commande c ON c.id_seller = g.id_seller
LEFT JOIN Sale s ON s.id_commande = c.id_commande AND s.is_paid = TRUE
LEFT JOIN Commande_details cd ON cd.id_commande = c.id_commande
WHERE g.id_seller = :sellerId
AND s.sale_date BETWEEN :dateDebut AND :dateFin
GROUP BY annee, mois, g.target_ca, g.target_ventes
ORDER BY annee, mois;

$jours_passes = (new \DateTime())->format('d'); // jours écoulés du mois
$jours_total = date('t'); // nombre total de jours dans le mois

$projection_ca = $ca_realise / $jours_passes * $jours_total;
$projection_ventes = $ventes_realisees / $jours_passes * $jours_total;

https://dbdiagram.io/d/67dc43c975d75cc844dcaee2
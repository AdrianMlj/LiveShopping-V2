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
   name_item VARCHAR(255)  NOT NULL,
   id_seller INTEGER NOT NULL,
   id_category INTEGER NOT NULL,
   images VARCHAR(255),
   Description TEXT,
   PRIMARY KEY(id_item),
   FOREIGN KEY(id_seller) REFERENCES Users(id_user),
   FOREIGN KEY(id_category) REFERENCES Category(id_category)
);

CREATE TABLE Size(
   id_size SERIAL,
   name_size VARCHAR(255)  NOT NULL,
   PRIMARY KEY(id_size)
);

CREATE TABLE Color (
   id_color SERIAL PRIMARY KEY,
   name_color VARCHAR(100) NOT NULL
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

CREATE TABLE Item_size_color(
   id_item_size_color SERIAL,
   id_item_size INTEGER NOT NULL,
   id_color INTEGER NOT NULL,
   images VARCHAR(255),
   FOREIGN KEY(id_color) REFERENCES Color(id_color)
);

CREATE TABLE Export_temp(
   id_export_temp SERIAL,
   id_item_size_color INTEGER NOT NULL,
   quantity INTEGER NOT NULL,
   PRIMARY KEY(id_export_temp),
   FOREIGN KEY(id_item_size_color) REFERENCES Item_size_color(id_item_size_color)
);

CREATE TABLE Items_stock(
   id_item_stock SERIAL,
   out_item INTEGER,
   in_item INTEGER ,
   date_move TIMESTAMP NOT NULL,
   id_item_size_color INTEGER NOT NULL,
   PRIMARY KEY(id_item_stock),
   FOREIGN KEY(id_item_size_color) REFERENCES Item_size_color(id_item_size_color)
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

CREATE TABLE Sale(
   id_sale SERIAL,
   sale_date TIMESTAMP NOT NULL,
   is_paid BOOLEAN,
   id_commande INTEGER NOT NULL,
   PRIMARY KEY(id_sale),
   FOREIGN KEY(id_commande) REFERENCES Commande(id_commande)
);


--MOUVEMENT DE STOCK 
CREATE VIEW v_stock_movement_details AS
SELECT
    s.id_item_stock,
    s.id_item_size_color,
    s.out_item,
    s.in_item,
    s.date_move,
    isc.id_color,
    isc.id_item_size,
    i.id_item,
    i.id_seller,
    i.name_item,
    isz.value_size,
    c.name_color
FROM items_stock AS s
INNER JOIN item_size_color AS isc ON s.id_item_size_color = isc.id_item_size_color
INNER JOIN color AS c ON isc.id_color = c.id_color
INNER JOIN item_size AS isz ON isc.id_item_size = isz.id_item_size
INNER JOIN item AS i ON isz.id_item = i.id_item;


-- STOCK ACTUEL
DROP VIEW IF EXISTS v_current_stock_per_variant;

CREATE VIEW v_current_stock_per_variant AS
SELECT
    i.id_item               AS itemid,
    i.name_item             AS itemname,
    i.id_seller             AS sellerid,
    isz.id_item_size        AS itemsizeid,
    isz.value_size          AS valuesize,
    c.id_color              AS colorid,
    c.name_color            AS colorname,
    isc.id_item_size_color  AS itemsizecolorid,
    COALESCE(SUM(s.in_item), 0)  AS totalin,
    COALESCE(SUM(s.out_item), 0) AS totalout,
    COALESCE(SUM(s.in_item), 0) - COALESCE(SUM(s.out_item), 0) AS currentstock
FROM items_stock s
INNER JOIN item_size_color isc ON s.id_item_size_color = isc.id_item_size_color
INNER JOIN color c              ON isc.id_color = c.id_color
INNER JOIN item_size isz        ON isc.id_item_size = isz.id_item_size
INNER JOIN item i               ON isz.id_item = i.id_item
GROUP BY
    i.id_item, i.name_item, i.id_seller,
    isz.id_item_size, isz.value_size,
    c.id_color, c.name_color,
    isc.id_item_size_color;


-- TOP ARTICLE
DROP VIEW IF EXISTS v_paid_sales_details;

CREATE VIEW v_paid_sales_details AS
SELECT
    s.id_commande                   AS commande_id,
    s.sale_date                     AS sale_date,
    seller.id_user                  AS seller_id,
    i.id_item                       AS item_id,
    i.name_item                     AS item_name,
    i.images                        AS image_id,
    cat.name_category               AS category_name,
    cd.quantity                     AS quantity,
    cd.price                        AS unit_price
FROM sale s
INNER JOIN commande             c   ON s.id_commande = c.id_commande
INNER JOIN commande_details     cd  ON cd.id_commande = c.id_commande
INNER JOIN item_size            isz ON cd.id_item_size = isz.id_item_size
INNER JOIN item                 i   ON isz.id_item = i.id_item
INNER JOIN category             cat ON i.id_category = cat.id_category
INNER JOIN users                seller ON i.id_seller = seller.id_user
WHERE s.is_paid = true;


-- TOP CLIENTS
DROP VIEW IF EXISTS v_paid_sales_per_client;

CREATE VIEW v_paid_sales_per_client AS
SELECT
    c.id_seller                 AS seller_id,
    s.id_commande               AS commande_id,
    u.id_user                   AS client_id,
    u.username                  AS client_name,
    u.email                     AS email,
    u.contact                   AS contact,
    u.address                   AS address,
    cd.id_commande_detail       AS detail_id,
    cd.quantity                 AS quantity,
    cd.price                    AS unit_price,
    cd.quantity * cd.price      AS line_total
FROM commande c
INNER JOIN sale s              ON s.id_commande = c.id_commande
INNER JOIN commande_details cd ON cd.id_commande = c.id_commande
INNER JOIN users u             ON c.id_client = u.id_user
WHERE s.is_paid = true;


-- CAPACITE DE STOCK
DROP VIEW IF EXISTS v_item_stock_with_latest_price;

CREATE VIEW v_item_stock_with_latest_price AS
SELECT
    i.id_item        AS item_id,
    i.id_seller      AS seller_id,
    COALESCE(SUM(s.in_item), 0) - COALESCE(SUM(s.out_item), 0) AS stock_qty,
    lp.latest_price  AS latest_price
FROM items_stock s
INNER JOIN item_size_color isc ON s.id_item_size_color = isc.id_item_size_color
INNER JOIN item_size       isz ON isc.id_item_size = isz.id_item_size
INNER JOIN item             i  ON isz.id_item = i.id_item
LEFT JOIN LATERAL (
    SELECT p.price AS latest_price
    FROM price_items p
    WHERE p.id_item = i.id_item
    ORDER BY p.date_price DESC
    LIMIT 1
) lp ON TRUE
GROUP BY i.id_item, i.id_seller, lp.latest_price;


-- HISTORIQUE DES VENTES
DROP VIEW IF EXISTS v_sales_per_seller;

CREATE VIEW v_sales_per_seller AS
SELECT
    s.id_sale             AS sale_id,
    s.sale_date           AS sale_date,
    s.is_paid             AS is_paid,
    c.id_commande         AS commande_id,
    c.id_seller           AS seller_id,
    c.id_client           AS client_id,
    c.created_at          AS commande_created_at,
    st.id_state           AS state_id,
    st.name_state         AS state_label,
    cl.id_user            AS client_user_id,
    cl.username           AS client_username,
    cl.email              AS client_email,
    cl.contact            AS client_contact,
    cl.address            AS client_address,
    se.id_user            AS seller_user_id,
    se.username           AS seller_username,
    COALESCE(SUM(cd.price * cd.quantity), 0) AS total_amount
FROM sale s
INNER JOIN commande        c  ON s.id_commande = c.id_commande
INNER JOIN state_commande st  ON c.id_state = st.id_state
INNER JOIN users          cl  ON c.id_client = cl.id_user
INNER JOIN users          se  ON c.id_seller = se.id_user
LEFT JOIN  commande_details cd ON cd.id_commande = c.id_commande
GROUP BY
    s.id_sale, s.sale_date, s.is_paid,
    c.id_commande, c.id_seller, c.id_client, c.created_at,
    st.id_state, st.name_state,
    cl.id_user, cl.username, cl.email, cl.contact, cl.address,
    se.id_user, se.username;



DO $$
DECLARE
    -- Parametres ajustables
    v_seller_id CONSTANT integer := 1;
    v_date_debut CONSTANT date := DATE '2025-06-01';
    v_date_fin   CONSTANT date := CURRENT_DATE;
    v_max_commandes_par_jour CONSTANT integer := 2;
    v_stock_min CONSTANT integer := 200;
    v_stock_cible CONSTANT integer := 400;

    -- Variables
    v_current_day date;
    v_nb_commandes integer;
    v_nb_lignes integer;
    v_commande_id integer;
    v_quantite integer;
    v_order_ts timestamp;
    v_unit_price numeric(10,2);
    v_paid boolean;

    v_variant record;
    v_client record;
    v_state record;

    -- Statistiques
    v_commandes_generees integer := 0;
    v_details_generees integer := 0;
    v_ventes_generees integer := 0;
    v_articles_vendus integer := 0;
    v_ca_total numeric(14,2) := 0;
BEGIN
    IF v_date_fin < v_date_debut THEN
        RAISE EXCEPTION 'La date de fin (%) doit etre >= a la date de debut (%)', v_date_fin, v_date_debut;
    END IF;

    -- 1) Charger tous les etats disponibles
    CREATE TEMP TABLE tmp_states ON COMMIT DROP AS
    SELECT id_state, name_state
    FROM state_commande;

    IF NOT EXISTS (SELECT 1 FROM tmp_states) THEN
        RAISE EXCEPTION 'Aucun etat de commande defini. Remplissez state_commande avant le seed.';
    END IF;

    -- 2) Preparer les variantes du vendeur et leur stock courant
    CREATE TEMP TABLE tmp_variants ON COMMIT DROP AS
    SELECT
        isc.id_item_size_color,
        isz.id_item_size,
        i.id_item,
        COALESCE(SUM(s.in_item) - SUM(s.out_item), 0) AS stock_disponible
    FROM item_size_color isc
    JOIN item_size isz ON isz.id_item_size = isc.id_item_size
    JOIN item i ON i.id_item = isz.id_item
    LEFT JOIN items_stock s ON s.id_item_size_color = isc.id_item_size_color
    WHERE i.id_seller = v_seller_id
    GROUP BY 1,2,3;

    IF NOT EXISTS (SELECT 1 FROM tmp_variants) THEN
        RAISE EXCEPTION 'Aucune variante disponible pour le vendeur %', v_seller_id;
    END IF;

    -- 3) Reassort sous seuil
    FOR v_variant IN SELECT * FROM tmp_variants LOOP
        IF v_variant.stock_disponible < v_stock_min THEN
            INSERT INTO items_stock(id_item_size_color, in_item, out_item, date_move)
            VALUES (
                v_variant.id_item_size_color,
                v_stock_cible - v_variant.stock_disponible,
                0,
                (v_date_debut::timestamp - INTERVAL '1 day')
            );

            UPDATE tmp_variants
            SET stock_disponible = v_stock_cible
            WHERE id_item_size_color = v_variant.id_item_size_color;
        END IF;
    END LOOP;

    -- 4) Preparer la liste de clients (non vendeurs)
    CREATE TEMP TABLE tmp_clients ON COMMIT DROP AS
    SELECT id_user
    FROM users
    WHERE is_seller = false;

    IF NOT EXISTS (SELECT 1 FROM tmp_clients) THEN
        RAISE EXCEPTION 'Aucun client (utilisateur non vendeur) trouve.';
    END IF;

    -- 5) Generation jour par jour
    FOR v_current_day IN
        SELECT generate_series(v_date_debut, v_date_fin, INTERVAL '1 day')::date
    LOOP
        v_nb_commandes := FLOOR(random() * (v_max_commandes_par_jour + 1))::int;

        IF v_nb_commandes = 0 THEN
            CONTINUE;
        END IF;

        FOR i IN 1..v_nb_commandes LOOP
            -- Client aleatoire
            SELECT id_user INTO v_client
            FROM tmp_clients
            ORDER BY random()
            LIMIT 1;

            -- Horodatage de commande
            v_order_ts :=
                v_current_day::timestamp
                + (8 + FLOOR(random() * 12))::int * INTERVAL '1 hour'
                + FLOOR(random() * 60)::int * INTERVAL '1 minute'
                + FLOOR(random() * 60)::int * INTERVAL '1 second';

            -- Etat de commande aleatoire
            SELECT id_state, name_state INTO v_state
            FROM tmp_states
            ORDER BY random()
            LIMIT 1;

            INSERT INTO commande(id_state, id_client, id_seller, created_at)
            VALUES (v_state.id_state, v_client.id_user, v_seller_id, v_order_ts)
            RETURNING id_commande INTO v_commande_id;

            v_commandes_generees := v_commandes_generees + 1;

            -- Nombre de lignes
            v_nb_lignes := GREATEST(1, (FLOOR(random() * 3) + 1)::int);

            <<detail_loop>>
            FOR j IN 1..v_nb_lignes LOOP
                SELECT
                    v.*,
                    price_info.price AS latest_price
                INTO v_variant
                FROM tmp_variants v
                LEFT JOIN LATERAL (
                    SELECT price
                    FROM price_items
                    WHERE id_item = v.id_item
                      AND date_price <= v_current_day
                    ORDER BY date_price DESC
                    LIMIT 1
                ) AS price_info ON true
                WHERE v.stock_disponible > 0
                ORDER BY random()
                LIMIT 1;

                IF NOT FOUND THEN
                    EXIT detail_loop;
                END IF;

                v_quantite := LEAST(
                    v_variant.stock_disponible,
                    GREATEST(1, CEIL(random() * 5)::int)
                );

                IF v_variant.latest_price IS NOT NULL THEN
                    v_unit_price := v_variant.latest_price::numeric(10,2);
                ELSE
                    SELECT price
                    INTO v_unit_price
                    FROM price_items
                    WHERE id_item = v_variant.id_item
                    ORDER BY date_price DESC
                    LIMIT 1;

                    IF v_unit_price IS NULL THEN
                        v_unit_price := ROUND((15 + random() * 35)::numeric, 2);
                    END IF;
                END IF;

                INSERT INTO commande_details(id_commande, id_item_size, quantity, price)
                VALUES (v_commande_id, v_variant.id_item_size, v_quantite, v_unit_price);

                v_details_generees := v_details_generees + 1;
                v_articles_vendus := v_articles_vendus + v_quantite;
                v_ca_total := v_ca_total + (v_unit_price * v_quantite);

                INSERT INTO items_stock(id_item_size_color, in_item, out_item, date_move)
                VALUES (v_variant.id_item_size_color, 0, v_quantite, v_order_ts);

                UPDATE tmp_variants
                SET stock_disponible = stock_disponible - v_quantite
                WHERE id_item_size_color = v_variant.id_item_size_color;
            END LOOP;

            -- Paiement correle (etats Expediee/Confirmee plus susceptibles d etre payes)
            v_paid := CASE
                WHEN lower(v_state.name_state) IN ('expediee', 'confirmee') THEN random() < 0.9
                WHEN lower(v_state.name_state) IN ('en attente', 'en preparation') THEN random() < 0.5
                WHEN lower(v_state.name_state) IN ('annulee') THEN false
                ELSE random() < 0.7
            END;

            INSERT INTO sale(id_commande, sale_date, is_paid)
            VALUES (v_commande_id, v_current_day, v_paid);

            v_ventes_generees := v_ventes_generees + 1;
        END LOOP;
    END LOOP;

    RAISE NOTICE 'Generation terminee : % commandes, % lignes, % ventes, % articles, CA %.2f EUR',
        v_commandes_generees,
        v_details_generees,
        v_ventes_generees,
        v_articles_vendus,
        v_ca_total;
END;
$$;

https://dbdiagram.io/d/67dc43c975d75cc844dcaee2
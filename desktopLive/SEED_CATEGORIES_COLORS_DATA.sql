INSERT INTO category (name_category, description) VALUES
  ('Chaussures', 'Tous types de chaussures : baskets, escarpins, sandales, bottes et sneakers.'),
  ('Vetements', 'Habillement pour homme et femme : chemises, chemisiers, jeans, pantalons, robes.'),
  ('Accessoires', 'Articles complémentaires : sacs à main, portefeuilles, ceintures, montres et autres accessoires.'),
  ('Bijoux', 'Bijouterie et fantaisie : colliers, bracelets, boucles d oreilles et bagues.'),
  ('Beaute', 'Produits de beauté et cosmétiques : parfums, maquillage et soins.');


INSERT INTO color (name_color) VALUES
  ('Rouge'),
  ('Bleu'),
  ('Jaune'),
  ('Vert'),
  ('Orange'),
  ('Violet');

INSERT INTO color (name_color)
SELECT 'Noir'
WHERE NOT EXISTS (SELECT 1 FROM color WHERE LOWER(name_color) = 'noir');

INSERT INTO color (name_color)
SELECT 'Blanc'
WHERE NOT EXISTS (SELECT 1 FROM color WHERE LOWER(name_color) = 'blanc');

INSERT INTO color (name_color)
SELECT 'Marron'
WHERE NOT EXISTS (SELECT 1 FROM color WHERE LOWER(name_color) = 'marron');

INSERT INTO color (name_color)
SELECT 'Corail'
WHERE NOT EXISTS (SELECT 1 FROM color WHERE LOWER(name_color) = 'corail');

INSERT INTO color (name_color)
SELECT 'Beige'
WHERE NOT EXISTS (SELECT 1 FROM color WHERE LOWER(name_color) = 'beige');

INSERT INTO color (name_color)
SELECT 'Gris'
WHERE NOT EXISTS (SELECT 1 FROM color WHERE LOWER(name_color) = 'gris');


-- =============================
-- Chaussures
-- =============================
INSERT INTO item (name_item, id_seller, id_category, images, Description)
SELECT 'Baskets unisexe', 1, id_category, null, 'Baskets unisexe, confortables pour un usage quotidien.'
FROM category WHERE name_category = 'Chaussures';

INSERT INTO item (name_item, id_seller, id_category, images, Description)
SELECT 'Escarpins', 1, id_category, null, 'Escarpins élégants pour tenues habillées.'
FROM category WHERE name_category = 'Chaussures';

-- =============================
-- Vêtements
-- =============================
INSERT INTO item (name_item, id_seller, id_category, images, Description)
SELECT 'Chemise homme coton', 1, id_category, null, 'Chemise en coton respirant, coupe classique.'
FROM category WHERE name_category = 'Vetements';

INSERT INTO item (name_item, id_seller, id_category, images, Description)
SELECT 'Chemisier en soie', 1, id_category, null, 'Chemisier élégant en soie, toucher doux.'
FROM category WHERE name_category = 'Vetements';

INSERT INTO item (name_item, id_seller, id_category, images, Description)
SELECT 'Jean skinny', 1, id_category, null, 'Jean skinny stretch, coupe près du corps.'
FROM category WHERE name_category = 'Vetements';

INSERT INTO item (name_item, id_seller, id_category, images, Description)
SELECT 'Pantalon chino', 1, id_category, null, 'Chino polyvalent, confortable au quotidien.'
FROM category WHERE name_category = 'Vetements';

INSERT INTO item (name_item, id_seller, id_category, images, Description)
SELECT 'Robe malagasy traditionnelle', 1, id_category, null, 'Robe traditionnelle malagasy, motifs authentiques.'
FROM category WHERE name_category = 'Vetements';

-- =============================
-- Accessoires
-- =============================
INSERT INTO item (name_item, id_seller, id_category, images, Description)
SELECT 'Sac à main cuir', 1, id_category, null, 'Sac à main en cuir véritable, finitions soignées.'
FROM category WHERE name_category = 'Accessoires';

INSERT INTO item (name_item, id_seller, id_category, images, Description)
SELECT 'Portefeuille', 1, id_category, null, 'Portefeuille compact, multiple rangements.'
FROM category WHERE name_category = 'Accessoires';

INSERT INTO item (name_item, id_seller, id_category, images, Description)
SELECT 'Montre', 1, id_category, null, 'Montre intemporelle, bracelet confortable.'
FROM category WHERE name_category = 'Accessoires';

-- =============================
-- Bijoux
-- =============================
INSERT INTO item (name_item, id_seller, id_category, images, Description)
SELECT 'Collier', 1, id_category, null, 'Collier raffiné, idéal pour sublimer une tenue.'
FROM category WHERE name_category = 'Bijoux';

-- =============================
-- Beauté
-- =============================
INSERT INTO item (name_item, id_seller, id_category, images, Description)
SELECT 'Parfum', 1, id_category, null, 'Parfum aux notes subtiles et élégantes.'
FROM category WHERE name_category = 'Beaute';

INSERT INTO item (name_item, id_seller, id_category, images, Description)
SELECT 'Rouge à lèvres', 1, id_category, null, 'Rouge à lèvres longue tenue, couleur intense.'
FROM category WHERE name_category = 'Beaute';



-- Données uniquement pour l'entité Size (table `size`, colonne `name_size`)
-- Tailles de vêtements, pointures, et tailles génériques

-- =============================
-- Tailles de vêtements (alphanum)
-- =============================
INSERT INTO size (name_size)
SELECT 'XS'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE UPPER(name_size) = 'XS');

INSERT INTO size (name_size)
SELECT 'S'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE UPPER(name_size) = 'S');

INSERT INTO size (name_size)
SELECT 'M'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE UPPER(name_size) = 'M');

INSERT INTO size (name_size)
SELECT 'L'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE UPPER(name_size) = 'L');

INSERT INTO size (name_size)
SELECT 'XL'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE UPPER(name_size) = 'XL');

INSERT INTO size (name_size)
SELECT 'XXL'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE UPPER(name_size) = 'XXL');

-- Optionnelles (numériques vêtement EU)
INSERT INTO size (name_size)
SELECT '34'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '34');

INSERT INTO size (name_size)
SELECT '36'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '36');

INSERT INTO size (name_size)
SELECT '38'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '38');

INSERT INTO size (name_size)
SELECT '40'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '40');

INSERT INTO size (name_size)
SELECT '42'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '42');

INSERT INTO size (name_size)
SELECT '44'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '44');

-- =============================
-- Pointures (UE)
-- =============================
INSERT INTO size (name_size)
SELECT '35'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '35');

INSERT INTO size (name_size)
SELECT '36'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '36');

INSERT INTO size (name_size)
SELECT '37'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '37');

INSERT INTO size (name_size)
SELECT '38'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '38');

INSERT INTO size (name_size)
SELECT '39'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '39');

INSERT INTO size (name_size)
SELECT '40'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '40');

INSERT INTO size (name_size)
SELECT '41'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '41');

INSERT INTO size (name_size)
SELECT '42'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '42');

INSERT INTO size (name_size)
SELECT '43'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '43');

INSERT INTO size (name_size)
SELECT '44'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '44');

INSERT INTO size (name_size)
SELECT '45'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '45');

INSERT INTO size (name_size)
SELECT '46'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE name_size = '46');

-- =============================
-- Tailles génériques
-- =============================
INSERT INTO size (name_size)
SELECT 'Taille unique'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE LOWER(name_size) = 'taille unique');

INSERT INTO size (name_size)
SELECT 'Standard'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE LOWER(name_size) = 'standard');


-- =============================
-- Vetements → XS, S, M, L, XL, XXL
-- =============================
INSERT INTO item_size (id_size, id_item, value_size)
SELECT s.id_size, i.id_item, s.name_size
FROM item i
JOIN category c ON c.id_category = i.id_category
JOIN size s ON s.name_size IN ('XS','S','M','L','XL','XXL')
WHERE c.name_category = 'Vetements'
  AND NOT EXISTS (
    SELECT 1 FROM item_size x
    WHERE x.id_item = i.id_item AND x.id_size = s.id_size
  );

-- =============================
-- Chaussures → 35 à 46
-- =============================
INSERT INTO item_size (id_size, id_item, value_size)
SELECT s.id_size, i.id_item, s.name_size
FROM item i
JOIN category c ON c.id_category = i.id_category
JOIN size s ON s.name_size IN ('35','36','37','38','39','40','41','42','43','44','45','46')
WHERE c.name_category = 'Chaussures'
  AND NOT EXISTS (
    SELECT 1 FROM item_size x
    WHERE x.id_item = i.id_item AND x.id_size = s.id_size
  );

-- =============================
-- Accessoires / Bijoux / Beaute → Taille unique (fallback: Standard)
-- =============================
-- Taille unique
INSERT INTO item_size (id_size, id_item, value_size)
SELECT s.id_size, i.id_item, s.name_size
FROM item i
JOIN category c ON c.id_category = i.id_category
JOIN size s ON LOWER(s.name_size) = 'taille unique'
WHERE c.name_category IN ('Accessoires','Bijoux','Beaute')
  AND NOT EXISTS (
    SELECT 1 FROM item_size x
    WHERE x.id_item = i.id_item AND x.id_size = s.id_size
  );

-- Données pour `item_size_color` basées sur les couleurs présentes dans public/Images
-- Table: item_size_color (id_item_size_color, id_item_size, id_color, images)
-- Règles:
--  - On crée une variation par combinaison (item_size × couleur détectée)
--  - On renseigne `images` par fichier correspondant
--  - On évite les doublons (NOT EXISTS)

-- =============================
-- Baskets unisexe → Noir, Blanc, Beige
-- =============================
INSERT INTO item_size_color (id_item_size, id_color, images)
SELECT isz.id_item_size,
       c.id_color,
       CASE LOWER(c.name_color)
         WHEN 'noir'  THEN null
         WHEN 'blanc' THEN null
         WHEN 'beige' THEN null
       END AS images
FROM item i
JOIN category cat ON cat.id_category = i.id_category AND cat.name_category = 'Chaussures'
JOIN item_size isz ON isz.id_item = i.id_item
JOIN color c ON LOWER(c.name_color) IN ('noir','blanc','beige')
WHERE i.name_item = 'Baskets unisexe'
  AND NOT EXISTS (
    SELECT 1 FROM item_size_color x
    WHERE x.id_item_size = isz.id_item_size AND x.id_color = c.id_color
  );

-- =============================
-- Escarpins → Noir, Blanc, Rouge
-- =============================
INSERT INTO item_size_color (id_item_size, id_color, images)
SELECT isz.id_item_size,
       c.id_color,
       CASE LOWER(c.name_color)
         WHEN 'noir'  THEN null
         WHEN 'blanc' THEN null
         WHEN 'rouge' THEN null
       END AS images
FROM item i
JOIN category cat ON cat.id_category = i.id_category AND cat.name_category = 'Chaussures'
JOIN item_size isz ON isz.id_item = i.id_item
JOIN color c ON LOWER(c.name_color) IN ('noir','blanc','rouge')
WHERE i.name_item = 'Escarpins'
  AND NOT EXISTS (
    SELECT 1 FROM item_size_color x
    WHERE x.id_item_size = isz.id_item_size AND x.id_color = c.id_color
  );

-- =============================
-- Chemise homme coton → Bleu, Gris, Noir (fichiers distincts)
-- =============================
INSERT INTO item_size_color (id_item_size, id_color, images)
SELECT isz.id_item_size,
       c.id_color,
       CASE LOWER(c.name_color)
         WHEN 'bleu' THEN null
         WHEN 'gris' THEN null
         WHEN 'noir' THEN null
       END AS images
FROM item i
JOIN category cat ON cat.id_category = i.id_category AND cat.name_category = 'Vetements'
JOIN item_size isz ON isz.id_item = i.id_item
JOIN color c ON LOWER(c.name_color) IN ('bleu','gris','noir')
WHERE i.name_item = 'Chemise homme coton'
  AND NOT EXISTS (
    SELECT 1 FROM item_size_color x
    WHERE x.id_item_size = isz.id_item_size AND x.id_color = c.id_color
  );

-- =============================
-- Chemisier en soie → Blanc, Bleu, Rouge
-- =============================
INSERT INTO item_size_color (id_item_size, id_color, images)
SELECT isz.id_item_size,
       c.id_color,
       CASE LOWER(c.name_color)
         WHEN 'blanc' THEN null
         WHEN 'bleu'  THEN null
         WHEN 'rouge' THEN null
       END AS images
FROM item i
JOIN category cat ON cat.id_category = i.id_category AND cat.name_category = 'Vetements'
JOIN item_size isz ON isz.id_item = i.id_item
JOIN color c ON LOWER(c.name_color) IN ('blanc','bleu','rouge')
WHERE i.name_item = 'Chemisier en soie'
  AND NOT EXISTS (
    SELECT 1 FROM item_size_color x
    WHERE x.id_item_size = isz.id_item_size AND x.id_color = c.id_color
  );

-- =============================
-- Jean skinny → Blanc, Bleu, Noir
-- =============================
INSERT INTO item_size_color (id_item_size, id_color, images)
SELECT isz.id_item_size,
       c.id_color,
       CASE LOWER(c.name_color)
         WHEN 'blanc' THEN null
         WHEN 'bleu'  THEN null
         WHEN 'noir'  THEN null
       END AS images
FROM item i
JOIN category cat ON cat.id_category = i.id_category AND cat.name_category = 'Vetements'
JOIN item_size isz ON isz.id_item = i.id_item
JOIN color c ON LOWER(c.name_color) IN ('blanc','bleu','noir')
WHERE i.name_item = 'Jean skinny'
  AND NOT EXISTS (
    SELECT 1 FROM item_size_color x
    WHERE x.id_item_size = isz.id_item_size AND x.id_color = c.id_color
  );

-- =============================
-- Pantalon chino → Bleu
-- =============================
INSERT INTO item_size_color (id_item_size, id_color, images)
SELECT isz.id_item_size,
       c.id_color,
       null AS images
FROM item i
JOIN category cat ON cat.id_category = i.id_category AND cat.name_category = 'Vetements'
JOIN item_size isz ON isz.id_item = i.id_item
JOIN color c ON LOWER(c.name_color) = 'bleu'
WHERE i.name_item = 'Pantalon chino'
  AND NOT EXISTS (
    SELECT 1 FROM item_size_color x
    WHERE x.id_item_size = isz.id_item_size AND x.id_color = c.id_color
  );

-- =============================
-- Robe malagasy traditionnelle → Rouge
-- =============================
INSERT INTO item_size_color (id_item_size, id_color, images)
SELECT isz.id_item_size,
       c.id_color,
       CASE LOWER(c.name_color)
         WHEN 'rouge' THEN null
         WHEN 'bleu'  THEN null
         WHEN 'vert'  THEN null
       END AS images
FROM item i
JOIN category cat ON cat.id_category = i.id_category AND cat.name_category = 'Vetements'
JOIN item_size isz ON isz.id_item = i.id_item
JOIN color c ON LOWER(c.name_color) IN ('rouge','bleu','vert')
WHERE i.name_item = 'Robe malagasy traditionnelle'
  AND NOT EXISTS (
    SELECT 1 FROM item_size_color x
    WHERE x.id_item_size = isz.id_item_size AND x.id_color = c.id_color
  );

-- =============================
-- Sac à main cuir → Corail, Marron, Rouge
-- =============================
INSERT INTO item_size_color (id_item_size, id_color, images)
SELECT isz.id_item_size,
       c.id_color,
       CASE LOWER(c.name_color)
         WHEN 'marron' THEN null
         WHEN 'rouge'  THEN null
       END AS images
FROM item i
JOIN category cat ON cat.id_category = i.id_category AND cat.name_category = 'Accessoires'
JOIN item_size isz ON isz.id_item = i.id_item
JOIN color c ON LOWER(c.name_color) IN ('marron','rouge')
WHERE i.name_item = 'Sac à main cuir'
  AND NOT EXISTS (
    SELECT 1 FROM item_size_color x
    WHERE x.id_item_size = isz.id_item_size AND x.id_color = c.id_color
  );


-- 1) Prérequis: Taille unique et couleur Noir (créés si absents)
INSERT INTO size (name_size)
SELECT 'Taille unique'
WHERE NOT EXISTS (SELECT 1 FROM size WHERE LOWER(name_size) = 'taille unique');

INSERT INTO color (name_color)
SELECT 'Noir'
WHERE NOT EXISTS (SELECT 1 FROM color WHERE LOWER(name_color) = 'noir');

-- 2) item_size: lier chaque item à 'Taille unique'
-- Portefeuille
INSERT INTO item_size (id_size, id_item, value_size)
SELECT s.id_size, i.id_item, s.name_size
FROM item i
JOIN size s ON LOWER(s.name_size) = 'taille unique'
WHERE i.name_item = 'Portefeuille'
  AND NOT EXISTS (
    SELECT 1 FROM item_size x
    WHERE x.id_item = i.id_item AND x.id_size = s.id_size
  );

-- Montre
INSERT INTO item_size (id_size, id_item, value_size)
SELECT s.id_size, i.id_item, s.name_size
FROM item i
JOIN size s ON LOWER(s.name_size) = 'taille unique'
WHERE i.name_item = 'Montre'
  AND NOT EXISTS (
    SELECT 1 FROM item_size x
    WHERE x.id_item = i.id_item AND x.id_size = s.id_size
  );

-- Collier
INSERT INTO item_size (id_size, id_item, value_size)
SELECT s.id_size, i.id_item, s.name_size
FROM item i
JOIN size s ON LOWER(s.name_size) = 'taille unique'
WHERE i.name_item = 'Collier'
  AND NOT EXISTS (
    SELECT 1 FROM item_size x
    WHERE x.id_item = i.id_item AND x.id_size = s.id_size
  );

-- Parfum
INSERT INTO item_size (id_size, id_item, value_size)
SELECT s.id_size, i.id_item, s.name_size
FROM item i
JOIN size s ON LOWER(s.name_size) = 'taille unique'
WHERE i.name_item = 'Parfum'
  AND NOT EXISTS (
    SELECT 1 FROM item_size x
    WHERE x.id_item = i.id_item AND x.id_size = s.id_size
  );

-- Rouge à lèvres
INSERT INTO item_size (id_size, id_item, value_size)
SELECT s.id_size, i.id_item, s.name_size
FROM item i
JOIN size s ON LOWER(s.name_size) = 'taille unique'
WHERE i.name_item = 'Rouge à lèvres'
  AND NOT EXISTS (
    SELECT 1 FROM item_size x
    WHERE x.id_item = i.id_item AND x.id_size = s.id_size
  );

-- 3) item_size_color: variation avec couleur Noir + image associée
-- Portefeuille
INSERT INTO item_size_color (id_item_size, id_color, images)
SELECT isz.id_item_size, c.id_color, 'Images/portefeuille.jpg'
FROM item i
JOIN item_size isz ON isz.id_item = i.id_item
JOIN color c ON LOWER(c.name_color) = 'noir'
WHERE i.name_item = 'Portefeuille'
  AND NOT EXISTS (
    SELECT 1 FROM item_size_color x
    WHERE x.id_item_size = isz.id_item_size AND x.id_color = c.id_color
  );

-- Montre
INSERT INTO item_size_color (id_item_size, id_color, images)
SELECT isz.id_item_size, c.id_color, 'Images/montre.jpg'
FROM item i
JOIN item_size isz ON isz.id_item = i.id_item
JOIN color c ON LOWER(c.name_color) = 'noir'
WHERE i.name_item = 'Montre'
  AND NOT EXISTS (
    SELECT 1 FROM item_size_color x
    WHERE x.id_item_size = isz.id_item_size AND x.id_color = c.id_color
  );

-- Collier
INSERT INTO item_size_color (id_item_size, id_color, images)
SELECT isz.id_item_size, c.id_color, 'Images/collier.jpg'
FROM item i
JOIN item_size isz ON isz.id_item = i.id_item
JOIN color c ON LOWER(c.name_color) = 'noir'
WHERE i.name_item = 'Collier'
  AND NOT EXISTS (
    SELECT 1 FROM item_size_color x
    WHERE x.id_item_size = isz.id_item_size AND x.id_color = c.id_color
  );

-- Parfum
INSERT INTO item_size_color (id_item_size, id_color, images)
SELECT isz.id_item_size, c.id_color, 'Images/parfum.jpg'
FROM item i
JOIN item_size isz ON isz.id_item = i.id_item
JOIN color c ON LOWER(c.name_color) = 'noir'
WHERE i.name_item = 'Parfum'
  AND NOT EXISTS (
    SELECT 1 FROM item_size_color x
    WHERE x.id_item_size = isz.id_item_size AND x.id_color = c.id_color
  );

-- Rouge à lèvres
INSERT INTO item_size_color (id_item_size, id_color, images)
SELECT isz.id_item_size, c.id_color, 'Images/rougealevres.jpg'
FROM item i
JOIN item_size isz ON isz.id_item = i.id_item
JOIN color c ON LOWER(c.name_color) = 'noir'
WHERE i.name_item = 'Rouge à lèvres'
  AND NOT EXISTS (
    SELECT 1 FROM item_size_color x
    WHERE x.id_item_size = isz.id_item_size AND x.id_color = c.id_color
  );

-- 4) items_stock: créer une entrée de stock initiale (in_item=10)
INSERT INTO items_stock (id_item_size_color, out_item, in_item, date_move)
SELECT isc.id_item_size_color, 0, 10, NOW()
FROM item i
JOIN item_size isz ON isz.id_item = i.id_item
JOIN item_size_color isc ON isc.id_item_size = isz.id_item_size
WHERE i.name_item IN ('Portefeuille','Montre','Collier','Parfum','Rouge à lèvres')
  AND NOT EXISTS (
    SELECT 1 FROM items_stock s WHERE s.id_item_size_color = isc.id_item_size_color
  );


INSERT INTO items_stock (id_item_size_color, out_item, in_item, date_move)
SELECT isc.id_item_size_color,
       0 AS out_item,
       50 AS in_item,
       NOW() AS date_move
FROM item_size_color isc
WHERE NOT EXISTS (
  SELECT 1 FROM items_stock s
  WHERE s.id_item_size_color = isc.id_item_size_color
);

-- Prix initial pour chaque item (une seule entrée par item si aucune n'existe)
-- Table: price_items (id_price, id_item, price, date_price)

-- Chaussures
INSERT INTO price_items (id_item, price, date_price)
SELECT i.id_item, 100000, CURRENT_DATE
FROM item i
WHERE i.name_item = 'Baskets unisexe'
  AND NOT EXISTS (SELECT 1 FROM price_items p WHERE p.id_item = i.id_item);

INSERT INTO price_items (id_item, price, date_price)
SELECT i.id_item, 80000, CURRENT_DATE
FROM item i
WHERE i.name_item = 'Escarpins'
  AND NOT EXISTS (SELECT 1 FROM price_items p WHERE p.id_item = i.id_item);

-- Vêtements
INSERT INTO price_items (id_item, price, date_price)
SELECT i.id_item, 30000, CURRENT_DATE
FROM item i
WHERE i.name_item = 'Chemise homme coton'
  AND NOT EXISTS (SELECT 1 FROM price_items p WHERE p.id_item = i.id_item);

INSERT INTO price_items (id_item, price, date_price)
SELECT i.id_item, 35000, CURRENT_DATE
FROM item i
WHERE i.name_item = 'Chemisier en soie'
  AND NOT EXISTS (SELECT 1 FROM price_items p WHERE p.id_item = i.id_item);

INSERT INTO price_items (id_item, price, date_price)
SELECT i.id_item, 28000, CURRENT_DATE
FROM item i
WHERE i.name_item = 'Jean skinny'
  AND NOT EXISTS (SELECT 1 FROM price_items p WHERE p.id_item = i.id_item);

INSERT INTO price_items (id_item, price, date_price)
SELECT i.id_item, 45000, CURRENT_DATE
FROM item i
WHERE i.name_item = 'Pantalon chino'
  AND NOT EXISTS (SELECT 1 FROM price_items p WHERE p.id_item = i.id_item);

INSERT INTO price_items (id_item, price, date_price)
SELECT i.id_item, 60000, CURRENT_DATE
FROM item i
WHERE i.name_item = 'Robe malagasy traditionnelle'
  AND NOT EXISTS (SELECT 1 FROM price_items p WHERE p.id_item = i.id_item);

-- Accessoires
INSERT INTO price_items (id_item, price, date_price)
SELECT i.id_item, 120000, CURRENT_DATE
FROM item i
WHERE i.name_item = 'Sac à main cuir'
  AND NOT EXISTS (SELECT 1 FROM price_items p WHERE p.id_item = i.id_item);

INSERT INTO price_items (id_item, price, date_price)
SELECT i.id_item, 20000, CURRENT_DATE
FROM item i
WHERE i.name_item = 'Portefeuille'
  AND NOT EXISTS (SELECT 1 FROM price_items p WHERE p.id_item = i.id_item);

INSERT INTO price_items (id_item, price, date_price)
SELECT i.id_item, 70000, CURRENT_DATE
FROM item i
WHERE i.name_item = 'Montre'
  AND NOT EXISTS (SELECT 1 FROM price_items p WHERE p.id_item = i.id_item);

-- Bijoux
INSERT INTO price_items (id_item, price, date_price)
SELECT i.id_item, 32000, CURRENT_DATE
FROM item i
WHERE i.name_item = 'Collier'
  AND NOT EXISTS (SELECT 1 FROM price_items p WHERE p.id_item = i.id_item);

-- Beauté
INSERT INTO price_items (id_item, price, date_price)
SELECT i.id_item, 90000, CURRENT_DATE
FROM item i
WHERE i.name_item = 'Parfum'
  AND NOT EXISTS (SELECT 1 FROM price_items p WHERE p.id_item = i.id_item);

INSERT INTO price_items (id_item, price, date_price)
SELECT i.id_item, 36000, CURRENT_DATE
FROM item i
WHERE i.name_item = 'Rouge à lèvres'
  AND NOT EXISTS (SELECT 1 FROM price_items p WHERE p.id_item = i.id_item);




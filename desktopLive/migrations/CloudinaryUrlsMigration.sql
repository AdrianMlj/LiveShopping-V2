-- ========================================
-- Migration pour supporter les URLs Cloudinary
-- ========================================
--
-- Les URLs Cloudinary sont plus longues que les noms de fichiers locaux
-- Exemple: https://res.cloudinary.com/cloud-name/image/upload/v1234567890/folder/filename.jpg
--
-- Cette migration augmente la taille des colonnes 'images' pour stocker les URLs complètes
--
-- IMPORTANT:
-- - Sauvegardez votre base de données avant d'exécuter cette migration
-- - Cette migration est compatible avec les données existantes
-- ========================================

-- Afficher les tailles actuelles
SELECT
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_TYPE
FROM
    INFORMATION_SCHEMA.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE()
    AND COLUMN_NAME = 'images';

-- ========================================
-- 1. Table Users - Colonne images
-- ========================================
-- Avant: images BIGINT
-- Après: images VARCHAR(500)

ALTER TABLE Users
MODIFY COLUMN images VARCHAR(500) DEFAULT NULL
COMMENT 'URL locale ou Cloudinary de l''image de profil';

-- ========================================
-- 2. Table Item - Colonne images
-- ========================================
-- Avant: images VARCHAR(255)
-- Après: images VARCHAR(500)

ALTER TABLE Item
MODIFY COLUMN images VARCHAR(500) DEFAULT NULL
COMMENT 'URL locale ou Cloudinary de l''image de l''article';

-- ========================================
-- 3. Table Item_size_color - Colonne images
-- ========================================
-- Avant: images VARCHAR(255)
-- Après: images VARCHAR(500)

ALTER TABLE Item_size_color
MODIFY COLUMN images VARCHAR(500) DEFAULT NULL
COMMENT 'URL locale ou Cloudinary de l''image de la variante';

-- ========================================
-- Vérification finale
-- ========================================

SELECT
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_COMMENT
FROM
    INFORMATION_SCHEMA.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE()
    AND COLUMN_NAME = 'images';

-- ========================================
-- Notes:
-- ========================================
--
-- 1. Cette migration préserve toutes les données existantes
-- 2. Les anciens chemins locaux (ex: "tshirt_noir.jpg") continueront de fonctionner
-- 3. Les nouvelles URLs Cloudinary seront stockées avec leur URL complète
-- 4. Pour distinguer les deux formats dans votre code :
--    - URL Cloudinary : commence par "http"
--    - Chemin local : ne commence pas par "http"
--
-- Exemple PHP :
-- if (str_starts_with($user->getImages(), 'http')) {
--     // C'est une URL Cloudinary
--     $imageUrl = $user->getImages();
-- } else {
--     // C'est un ancien fichier local
--     $imageUrl = '/uploads/' . $user->getImages();
-- }
--
-- ========================================
-- Rollback (si nécessaire)
-- ========================================
--
-- Si vous devez revenir en arrière :
--
-- ALTER TABLE Users MODIFY COLUMN images BIGINT DEFAULT NULL;
-- ALTER TABLE Item MODIFY COLUMN images VARCHAR(255) DEFAULT NULL;
-- ALTER TABLE Item_size_color MODIFY COLUMN images VARCHAR(255) DEFAULT NULL;
--
-- ⚠️ ATTENTION: Le rollback peut causer une perte de données si des URLs
-- Cloudinary ont déjà été enregistrées (elles seront tronquées)
-- ========================================

-- Fin de la migration


